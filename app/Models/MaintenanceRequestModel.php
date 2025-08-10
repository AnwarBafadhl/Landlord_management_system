<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceRequestModel extends Model
{
    protected $table = 'maintenance_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'tenant_id',
        'property_id',
        'assigned_staff_id',
        'title',
        'description',
        'priority',
        'status',
        'estimated_cost',
        'actual_cost',
        'materials_used',
        'work_notes',
        'requested_date',
        'assigned_date',
        'completed_date'
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
        'title' => 'required|min_length[5]|max_length[200]',
        'description' => 'required|min_length[10]',
        'priority' => 'in_list[low,normal,high,urgent]',
        'status' => 'in_list[pending,assigned,in_progress,completed,cancelled]',
        'estimated_cost' => 'decimal|greater_than_equal_to[0]',
        'actual_cost' => 'decimal|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setRequestedDate'];
    protected $beforeUpdate = ['updateStatusDates'];

    /**
     * Set requested date on insert
     */
    protected function setRequestedDate(array $data)
    {
        if (!isset($data['data']['requested_date'])) {
            $data['data']['requested_date'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * Update status-related dates
     */
    protected function updateStatusDates(array $data)
    {
        if (isset($data['data']['status'])) {
            switch ($data['data']['status']) {
                case 'assigned':
                    if (!isset($data['data']['assigned_date'])) {
                        $data['data']['assigned_date'] = date('Y-m-d H:i:s');
                    }
                    break;
                case 'completed':
                    if (!isset($data['data']['completed_date'])) {
                        $data['data']['completed_date'] = date('Y-m-d H:i:s');
                    }
                    break;
            }
        }
        return $data;
    }

    /**
     * Get all maintenance requests with related data
     */
    public function getAllRequests($status = null, $priority = null, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, 
                         t.email as tenant_email, t.phone as tenant_phone,
                         p.property_name, p.address as property_address,
                         s.first_name as staff_first_name, s.last_name as staff_last_name,
                         s.email as staff_email, s.phone as staff_phone');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');
        
        if ($status) {
            $builder->where('mr.status', $status);
        }
        
        if ($priority) {
            $builder->where('mr.priority', $priority);
        }
        
        $builder->orderBy('mr.priority', 'DESC');
        $builder->orderBy('mr.requested_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests($limit = null)
    {
        return $this->getAllRequests('pending', null, $limit);
    }

    /**
     * Get requests by tenant
     */
    public function getRequestsByTenant($tenantId, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         p.property_name, p.address as property_address,
                         s.first_name as staff_first_name, s.last_name as staff_last_name');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');
        $builder->where('mr.tenant_id', $tenantId);
        $builder->orderBy('mr.requested_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get requests by landlord properties
     */
    public function getRequestsByLandlord($landlordId, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         p.property_name, p.address as property_address,
                         s.first_name as staff_first_name, s.last_name as staff_last_name,
                         po.ownership_percentage');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->join('property_ownership po', 'po.property_id = mr.property_id');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');
        $builder->where('po.landlord_id', $landlordId);
        $builder->orderBy('mr.priority', 'DESC');
        $builder->orderBy('mr.requested_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get requests assigned to maintenance staff
     */
    public function getRequestsByStaff($staffId, $status = null, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         t.email as tenant_email, t.phone as tenant_phone,
                         p.property_name, p.address as property_address');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->where('mr.assigned_staff_id', $staffId);
        
        if ($status) {
            $builder->where('mr.status', $status);
        }
        
        $builder->orderBy('mr.priority', 'DESC');
        $builder->orderBy('mr.requested_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Assign staff to request
     */
    public function assignStaff($requestId, $staffId)
    {
        $data = [
            'assigned_staff_id' => $staffId,
            'status' => 'assigned',
            'assigned_date' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($requestId, $data);
    }

    /**
     * Update request status
     */
    public function updateStatus($requestId, $status, $notes = null)
    {
        $data = ['status' => $status];
        
        if ($notes) {
            $data['work_notes'] = $notes;
        }
        
        if ($status === 'completed') {
            $data['completed_date'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($requestId, $data);
    }

    /**
     * Complete maintenance request
     */
    public function completeRequest($requestId, $actualCost = null, $materialsUsed = null, $notes = null)
    {
        $data = [
            'status' => 'completed',
            'completed_date' => date('Y-m-d H:i:s')
        ];
        
        if ($actualCost !== null) {
            $data['actual_cost'] = $actualCost;
        }
        
        if ($materialsUsed) {
            $data['materials_used'] = $materialsUsed;
        }
        
        if ($notes) {
            $data['work_notes'] = $notes;
        }
        
        return $this->update($requestId, $data);
    }

    /**
     * Get urgent requests
     */
    public function getUrgentRequests($limit = null)
    {
        return $this->getAllRequests(null, 'urgent', $limit);
    }

    /**
     * Get high priority requests
     */
    public function getHighPriorityRequests($limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         p.property_name, p.address as property_address');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->whereIn('mr.priority', ['high', 'urgent']);
        $builder->whereIn('mr.status', ['pending', 'assigned', 'in_progress']);
        $builder->orderBy('mr.priority', 'DESC');
        $builder->orderBy('mr.requested_date', 'ASC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get maintenance statistics
     */
    public function getMaintenanceStatistics()
    {
        $db = \Config\Database::connect();
        
        $stats = [];
        
        // Total requests
        $stats['total'] = $this->countAllResults();
        
        // Requests by status
        $statusQuery = $db->query("
            SELECT status, COUNT(*) as count 
            FROM maintenance_requests 
            GROUP BY status
        ");
        $statusResults = $statusQuery->getResultArray();
        
        foreach ($statusResults as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }
        
        // Requests by priority
        $priorityQuery = $db->query("
            SELECT priority, COUNT(*) as count 
            FROM maintenance_requests 
            GROUP BY priority
        ");
        $priorityResults = $priorityQuery->getResultArray();
        
        foreach ($priorityResults as $row) {
            $stats['by_priority'][$row['priority']] = $row['count'];
        }
        
        // Average completion time (in days)
        $avgTimeQuery = $db->query("
            SELECT AVG(DATEDIFF(completed_date, requested_date)) as avg_completion_days
            FROM maintenance_requests 
            WHERE status = 'completed' 
            AND completed_date IS NOT NULL
        ");
        $avgTimeResult = $avgTimeQuery->getRowArray();
        $stats['avg_completion_days'] = round($avgTimeResult['avg_completion_days'] ?? 0, 1);
        
        // Total maintenance costs this month
        $costQuery = $db->query("
            SELECT SUM(actual_cost) as total_cost
            FROM maintenance_requests 
            WHERE status = 'completed' 
            AND MONTH(completed_date) = MONTH(CURDATE())
            AND YEAR(completed_date) = YEAR(CURDATE())
        ");
        $costResult = $costQuery->getRowArray();
        $stats['monthly_cost'] = $costResult['total_cost'] ?? 0;
        
        return $stats;
    }

    /**
     * Get overdue requests (pending for more than specified days)
     */
    public function getOverdueRequests($days = 7)
    {
        $overdueDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         p.property_name, p.address as property_address');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->whereIn('mr.status', ['pending', 'assigned']);
        $builder->where('mr.requested_date <', $overdueDate);
        $builder->orderBy('mr.requested_date', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Search maintenance requests
     */
    public function searchRequests($searchTerm = '', $status = '', $priority = '', $propertyId = '')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         p.property_name, p.address as property_address,
                         s.first_name as staff_first_name, s.last_name as staff_last_name');
        $builder->join('users t', 't.id = mr.tenant_id');
        $builder->join('properties p', 'p.id = mr.property_id');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('mr.title', $searchTerm)
                   ->orLike('mr.description', $searchTerm)
                   ->orLike('t.first_name', $searchTerm)
                   ->orLike('t.last_name', $searchTerm)
                   ->orLike('p.property_name', $searchTerm)
                   ->orLike('p.address', $searchTerm)
                   ->groupEnd();
        }
        
        if (!empty($status)) {
            $builder->where('mr.status', $status);
        }
        
        if (!empty($priority)) {
            $builder->where('mr.priority', $priority);
        }
        
        if (!empty($propertyId)) {
            $builder->where('mr.property_id', $propertyId);
        }
        
        $builder->orderBy('mr.priority', 'DESC');
        $builder->orderBy('mr.requested_date', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get monthly maintenance report
     */
    public function getMonthlyReport($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        
        $db = \Config\Database::connect();
        
        // Requests submitted this month
        $submittedQuery = $db->query("
            SELECT COUNT(*) as count, priority
            FROM maintenance_requests 
            WHERE YEAR(requested_date) = ? 
            AND MONTH(requested_date) = ?
            GROUP BY priority
        ", [$year, $month]);
        $submitted = $submittedQuery->getResultArray();
        
        // Requests completed this month
        $completedQuery = $db->query("
            SELECT COUNT(*) as count, SUM(actual_cost) as total_cost
            FROM maintenance_requests 
            WHERE status = 'completed'
            AND YEAR(completed_date) = ? 
            AND MONTH(completed_date) = ?
        ", [$year, $month]);
        $completed = $completedQuery->getRowArray();
        
        // Average completion time this month
        $avgTimeQuery = $db->query("
            SELECT AVG(DATEDIFF(completed_date, requested_date)) as avg_days
            FROM maintenance_requests 
            WHERE status = 'completed'
            AND YEAR(completed_date) = ? 
            AND MONTH(completed_date) = ?
        ", [$year, $month]);
        $avgTime = $avgTimeQuery->getRowArray();
        
        return [
            'submitted_by_priority' => $submitted,
            'completed_count' => $completed['count'] ?? 0,
            'total_cost' => $completed['total_cost'] ?? 0,
            'avg_completion_days' => round($avgTime['avg_days'] ?? 0, 1)
        ];
    }
}
        