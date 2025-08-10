<?php

namespace App\Models;

use CodeIgniter\Model;

class PropertyModel extends Model
{
    protected $table = 'properties';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'property_name',
        'address',
        'property_type',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'base_rent',
        'deposit',
        'status',
        'description'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'property_name' => 'required|min_length[3]|max_length[100]',
        'address' => 'required|min_length[10]',
        'property_type' => 'required|in_list[apartment,house,condo,commercial]',
        'base_rent' => 'required|decimal|greater_than[0]',
        'status' => 'in_list[vacant,occupied,maintenance]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $beforeUpdate = [];

    /**
     * Get properties with landlord information
     */
    public function getPropertiesWithLandlords($propertyId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('properties p');
        $builder->select('p.*, u.first_name, u.last_name, u.email, u.phone, po.ownership_percentage,
                         l.tenant_id, tenant.first_name as tenant_first_name, tenant.last_name as tenant_last_name,
                         l.lease_start, l.lease_end, l.rent_amount, l.status as lease_status');
        $builder->join('property_ownership po', 'po.property_id = p.id', 'left');
        $builder->join('users u', 'u.id = po.landlord_id AND u.role = "landlord"', 'left');
        $builder->join('leases l', 'l.property_id = p.id AND l.status = "active"', 'left');
        $builder->join('users tenant', 'tenant.id = l.tenant_id AND tenant.role = "tenant"', 'left');
        
        if ($propertyId) {
            $builder->where('p.id', $propertyId);
        }
        
        $builder->orderBy('p.property_name', 'ASC');
        
        return $propertyId ? $builder->get()->getResultArray() : $builder->get()->getResultArray();
    }

    /**
     * Get properties for specific landlord
     */
    public function getPropertiesForLandlord($landlordId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('properties p');
        $builder->select('p.*, po.ownership_percentage, 
                         l.tenant_id, tenant.first_name as tenant_first_name, tenant.last_name as tenant_last_name,
                         l.lease_start, l.lease_end, l.rent_amount, l.status as lease_status');
        $builder->join('property_ownership po', 'po.property_id = p.id');
        $builder->join('leases l', 'l.property_id = p.id AND l.status = "active"', 'left');
        $builder->join('users tenant', 'tenant.id = l.tenant_id AND tenant.role = "tenant"', 'left');
        $builder->where('po.landlord_id', $landlordId);
        $builder->orderBy('p.property_name', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get vacant properties
     */
    public function getVacantProperties()
    {
        return $this->where('status', 'vacant')->findAll();
    }

    /**
     * Get occupied properties
     */
    public function getOccupiedProperties()
    {
        return $this->where('status', 'occupied')->findAll();
    }

    /**
     * Assign landlord to property
     */
    public function assignLandlord($propertyId, $landlordId, $ownershipPercentage = 100.00)
    {
        $db = \Config\Database::connect();
        
        // Check if ownership already exists
        $existing = $db->table('property_ownership')
                      ->where('property_id', $propertyId)
                      ->where('landlord_id', $landlordId)
                      ->get()
                      ->getRowArray();

        if ($existing) {
            // Update ownership percentage
            return $db->table('property_ownership')
                     ->where('property_id', $propertyId)
                     ->where('landlord_id', $landlordId)
                     ->update(['ownership_percentage' => $ownershipPercentage]);
        } else {
            // Insert new ownership
            return $db->table('property_ownership')
                     ->insert([
                         'property_id' => $propertyId,
                         'landlord_id' => $landlordId,
                         'ownership_percentage' => $ownershipPercentage
                     ]);
        }
    }

    /**
     * Remove landlord from property
     */
    public function removeLandlord($propertyId, $landlordId)
    {
        $db = \Config\Database::connect();
        return $db->table('property_ownership')
                 ->where('property_id', $propertyId)
                 ->where('landlord_id', $landlordId)
                 ->delete();
    }

    /**
     * Get property landlords
     */
    public function getPropertyLandlords($propertyId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('property_ownership po');
        $builder->select('po.*, u.first_name, u.last_name, u.email, u.phone');
        $builder->join('users u', 'u.id = po.landlord_id');
        $builder->where('po.property_id', $propertyId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Update property status
     */
    public function updateStatus($propertyId, $status)
    {
        return $this->update($propertyId, ['status' => $status]);
    }

    /**
     * Get property statistics
     */
    public function getPropertyStatistics()
    {
        $db = \Config\Database::connect();
        
        $stats = [];
        
        // Total properties
        $stats['total'] = $this->countAllResults();
        
        // Properties by status
        $statusQuery = $db->query("
            SELECT status, COUNT(*) as count 
            FROM properties 
            GROUP BY status
        ");
        $statusResults = $statusQuery->getResultArray();
        
        foreach ($statusResults as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }
        
        // Properties by type
        $typeQuery = $db->query("
            SELECT property_type, COUNT(*) as count 
            FROM properties 
            GROUP BY property_type
        ");
        $typeResults = $typeQuery->getResultArray();
        
        foreach ($typeResults as $row) {
            $stats['by_type'][$row['property_type']] = $row['count'];
        }
        
        return $stats;
    }

    /**
     * Search properties
     */
    public function searchProperties($searchTerm = '', $status = '', $type = '')
    {
        $builder = $this->builder();
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('property_name', $searchTerm)
                   ->orLike('address', $searchTerm)
                   ->groupEnd();
        }
        
        if (!empty($status)) {
            $builder->where('status', $status);
        }
        
        if (!empty($type)) {
            $builder->where('property_type', $type);
        }
        
        return $builder->get()->getResultArray();
    }
}