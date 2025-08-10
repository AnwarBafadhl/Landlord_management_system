<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaseModel extends Model
{
    protected $table = 'leases';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'tenant_id',
        'property_id',
        'rent_amount',
        'deposit_amount',
        'lease_start',
        'lease_end',
        'status',
        'lease_terms'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'tenant_id' => 'required|integer',
        'property_id' => 'required|integer',
        'rent_amount' => 'required|decimal|greater_than[0]',
        'deposit_amount' => 'decimal|greater_than_equal_to[0]',
        'lease_start' => 'required|valid_date',
        'lease_end' => 'required|valid_date',
        'status' => 'in_list[active,expired,terminated]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['updatePropertyStatus'];
    protected $beforeUpdate = ['updatePropertyStatus'];

    /**
     * Update property status when lease is created/updated
     */
    protected function updatePropertyStatus(array $data)
    {
        if (isset($data['data']['property_id']) && isset($data['data']['status'])) {
            $db = \Config\Database::connect();
            $propertyStatus = ($data['data']['status'] === 'active') ? 'occupied' : 'vacant';
            
            $db->table('properties')
               ->where('id', $data['data']['property_id'])
               ->update(['status' => $propertyStatus]);
        }
        
        return $data;
    }

    /**
     * Get all leases with tenant and property details
     */
    public function getAllLeases($status = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email, t.phone as tenant_phone,
                         p.property_name, p.address as property_address, p.property_type');
        $builder->join('users t', 't.id = l.tenant_id');
        $builder->join('properties p', 'p.id = l.property_id');
        
        if ($status) {
            $builder->where('l.status', $status);
        }
        
        $builder->orderBy('l.lease_start', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get active leases
     */
    public function getActiveLeases()
    {
        return $this->getAllLeases('active');
    }

    /**
     * Get expired leases
     */
    public function getExpiredLeases()
    {
        return $this->getAllLeases('expired');
    }

    /**
     * Get lease by tenant ID
     */
    public function getLeaseByTenant($tenantId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, p.property_name, p.address as property_address, p.property_type');
        $builder->join('properties p', 'p.id = l.property_id');
        $builder->where('l.tenant_id', $tenantId);
        $builder->where('l.status', 'active');
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get leases for specific property
     */
    public function getLeasesByProperty($propertyId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email, t.phone as tenant_phone');
        $builder->join('users t', 't.id = l.tenant_id');
        $builder->where('l.property_id', $propertyId);
        $builder->orderBy('l.lease_start', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get leases for landlord properties
     */
    public function getLeasesByLandlord($landlordId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email, t.phone as tenant_phone,
                         p.property_name, p.address as property_address,
                         po.ownership_percentage');
        $builder->join('users t', 't.id = l.tenant_id');
        $builder->join('properties p', 'p.id = l.property_id');
        $builder->join('property_ownership po', 'po.property_id = l.property_id');
        $builder->where('po.landlord_id', $landlordId);
        $builder->orderBy('l.lease_start', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Create new lease
     */
    public function createLease($data)
    {
        // Validate that property is available
        $db = \Config\Database::connect();
        $existingLease = $db->table('leases')
                           ->where('property_id', $data['property_id'])
                           ->where('status', 'active')
                           ->get()
                           ->getRowArray();
        
        if ($existingLease) {
            throw new \Exception('Property is already occupied');
        }

        // Validate tenant is not already in another active lease
        $tenantLease = $db->table('leases')
                         ->where('tenant_id', $data['tenant_id'])
                         ->where('status', 'active')
                         ->get()
                         ->getRowArray();
        
        if ($tenantLease) {
            throw new \Exception('Tenant is already in an active lease');
        }

        return $this->insert($data);
    }

    /**
     * Terminate lease
     */
    public function terminateLease($leaseId, $terminationDate = null)
    {
        $terminationDate = $terminationDate ?? date('Y-m-d');
        
        $updateData = [
            'status' => 'terminated',
            'lease_end' => $terminationDate
        ];
        
        if ($this->update($leaseId, $updateData)) {
            // Update property status to vacant
            $lease = $this->find($leaseId);
            if ($lease) {
                $db = \Config\Database::connect();
                $db->table('properties')
                   ->where('id', $lease['property_id'])
                   ->update(['status' => 'vacant']);
            }
            return true;
        }
        
        return false;
    }

    /**
     * Renew lease
     */
    public function renewLease($leaseId, $newEndDate, $newRentAmount = null)
    {
        $lease = $this->find($leaseId);
        if (!$lease) {
            return false;
        }

        $updateData = [
            'lease_end' => $newEndDate,
            'status' => 'active'
        ];
        
        if ($newRentAmount) {
            $updateData['rent_amount'] = $newRentAmount;
        }
        
        return $this->update($leaseId, $updateData);
    }

    /**
     * Get leases expiring soon
     */
    public function getExpiringSoon($days = 30)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email, t.phone as tenant_phone,
                         p.property_name, p.address as property_address');
        $builder->join('users t', 't.id = l.tenant_id');
        $builder->join('properties p', 'p.id = l.property_id');
        $builder->where('l.status', 'active');
        $builder->where('l.lease_end <=', $expiryDate);
        $builder->where('l.lease_end >=', date('Y-m-d'));
        $builder->orderBy('l.lease_end', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Mark expired leases
     */
    public function markExpiredLeases()
    {
        $today = date('Y-m-d');
        
        // Get leases that expired
        $expiredLeases = $this->where('status', 'active')
                             ->where('lease_end <', $today)
                             ->findAll();
        
        $updated = 0;
        
        foreach ($expiredLeases as $lease) {
            if ($this->update($lease['id'], ['status' => 'expired'])) {
                // Update property status to vacant
                $db = \Config\Database::connect();
                $db->table('properties')
                   ->where('id', $lease['property_id'])
                   ->update(['status' => 'vacant']);
                
                $updated++;
            }
        }
        
        return $updated;
    }

    /**
     * Get lease statistics
     */
    public function getLeaseStatistics()
    {
        $db = \Config\Database::connect();
        
        $stats = [];
        
        // Total leases
        $stats['total'] = $this->countAllResults();
        
        // Leases by status
        $statusQuery = $db->query("
            SELECT status, COUNT(*) as count 
            FROM leases 
            GROUP BY status
        ");
        $statusResults = $statusQuery->getResultArray();
        
        foreach ($statusResults as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }
        
        // Average rent
        $avgRentQuery = $db->query("
            SELECT AVG(rent_amount) as avg_rent 
            FROM leases 
            WHERE status = 'active'
        ");
        $avgRentResult = $avgRentQuery->getRowArray();
        $stats['average_rent'] = $avgRentResult['avg_rent'] ?? 0;
        
        // Total monthly rent from active leases
        $totalRentQuery = $db->query("
            SELECT SUM(rent_amount) as total_rent 
            FROM leases 
            WHERE status = 'active'
        ");
        $totalRentResult = $totalRentQuery->getRowArray();
        $stats['total_monthly_rent'] = $totalRentResult['total_rent'] ?? 0;
        
        // Leases expiring in next 30 days
        $expiringQuery = $db->query("
            SELECT COUNT(*) as expiring_count 
            FROM leases 
            WHERE status = 'active' 
            AND lease_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ");
        $expiringResult = $expiringQuery->getRowArray();
        $stats['expiring_soon'] = $expiringResult['expiring_count'] ?? 0;
        
        return $stats;
    }

    /**
     * Search leases
     */
    public function searchLeases($searchTerm = '', $status = '', $propertyId = '')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('leases l');
        $builder->select('l.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email,
                         p.property_name, p.address as property_address');
        $builder->join('users t', 't.id = l.tenant_id');
        $builder->join('properties p', 'p.id = l.property_id');
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('t.first_name', $searchTerm)
                   ->orLike('t.last_name', $searchTerm)
                   ->orLike('t.email', $searchTerm)
                   ->orLike('p.property_name', $searchTerm)
                   ->orLike('p.address', $searchTerm)
                   ->groupEnd();
        }
        
        if (!empty($status)) {
            $builder->where('l.status', $status);
        }
        
        if (!empty($propertyId)) {
            $builder->where('l.property_id', $propertyId);
        }
        
        $builder->orderBy('l.lease_start', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}