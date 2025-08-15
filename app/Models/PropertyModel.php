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
        'expenses',
        'management_company',
        'management_percentage',
        'number_of_landlords',
        'deposit',
        'status',
        'description'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get properties for a specific landlord with ownership details - FIXED VERSION
     */
    public function getPropertiesForLandlord($landlordId)
{
    try {
        log_message('info', 'PropertyModel: Getting properties for landlord ID: ' . $landlordId);
        
        $result = $this->db->table('properties p')
                       ->join('property_ownership po', 'po.property_id = p.id')
                       ->groupStart() // Start OR condition for both ownership columns
                           ->where('po.user_id', $landlordId)
                           ->orWhere('po.landlord_id', $landlordId)
                       ->groupEnd() // End OR condition
                       ->select('p.id, p.property_name, p.address, p.property_type, p.management_company, 
                               p.management_percentage, p.number_of_landlords, p.status, p.number_of_units, 
                               p.created_at, p.updated_at,
                               po.ownership_percentage, 
                               po.landlord_name, 
                               po.user_id, 
                               po.landlord_id')
                       ->orderBy('p.created_at', 'DESC')
                       ->get()
                       ->getResultArray();
        
        log_message('info', 'PropertyModel: Found ' . count($result) . ' properties');
        
        // Debug: log the first property to see what fields we're getting
        if (!empty($result)) {
            log_message('info', 'First property data: ' . json_encode($result[0]));
        }
        
        return $result;
        
    } catch (\Exception $e) {
        log_message('error', 'PropertyModel getPropertiesForLandlord error: ' . $e->getMessage());
        return [];
    }
}

    /**
     * Get property with all ownership details
     */
    public function getPropertyWithOwnership($propertyId)
    {
        $property = $this->find($propertyId);
        
        if ($property) {
            $ownership = $this->db->table('property_ownership')
                                 ->where('property_id', $propertyId)
                                 ->orderBy('ownership_percentage', 'DESC')
                                 ->get()
                                 ->getResultArray();
            
            $property['ownership'] = $ownership;
        }
        
        return $property;
    }

    /**
     * Get properties with landlord information - for admin use
     */
    public function getPropertiesWithLandlords($propertyId = null)
    {
        $builder = $this->db->table('properties p')
                           ->select('p.*, po.ownership_percentage, po.landlord_name, 
                                   u.first_name as landlord_first_name, u.last_name as landlord_last_name, 
                                   u.email as landlord_email')
                           ->join('property_ownership po', 'po.property_id = p.id', 'left')
                           ->join('users u', 'u.id = po.user_id OR u.id = po.landlord_id', 'left')
                           ->orderBy('p.created_at', 'DESC');

        if ($propertyId) {
            $builder->where('p.id', $propertyId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get properties summary for dashboard
     */
    public function getPropertiesSummary($landlordId)
    {
        $properties = $this->getPropertiesForLandlord($landlordId);
        
        $summary = [
            'total' => count($properties),
            'vacant' => 0,
            'occupied' => 0,
            'maintenance' => 0,
            'total_rent' => 0,
            'total_expenses' => 0,
            'net_income' => 0
        ];
        
        foreach ($properties as $property) {
            // Count by status
            switch ($property['status']) {
                case 'vacant':
                    $summary['vacant']++;
                    break;
                case 'occupied':
                    $summary['occupied']++;
                    break;
                case 'maintenance':
                    $summary['maintenance']++;
                    break;
            }
            
            // Calculate totals based on ownership percentage
            $ownershipRatio = ($property['ownership_percentage'] ?? 100) / 100;
            $summary['total_rent'] += (($property['base_rent'] ?? 0) * $ownershipRatio);
            $summary['total_expenses'] += (($property['expenses'] ?? 0) * $ownershipRatio);
        }
        
        $summary['net_income'] = $summary['total_rent'] - $summary['total_expenses'];
        
        return $summary;
    }

    /**
     * Assign landlord to property - for admin use
     */
    public function assignLandlord($propertyId, $landlordId, $ownershipPercentage = 100)
    {
        $data = [
            'property_id' => $propertyId,
            'user_id' => $landlordId,
            'landlord_id' => $landlordId, // Fill both columns
            'ownership_percentage' => $ownershipPercentage,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('property_ownership')->insert($data);
    }

    /**
     * Remove landlord from property - for admin use
     */
    public function removeLandlord($propertyId, $landlordId)
    {
        return $this->db->table('property_ownership')
                       ->where('property_id', $propertyId)
                       ->groupStart()
                           ->where('user_id', $landlordId)
                           ->orWhere('landlord_id', $landlordId)
                       ->groupEnd()
                       ->delete();
    }

    /**
     * Get property landlords - for admin use
     */
    public function getPropertyLandlords($propertyId)
    {
        return $this->db->table('property_ownership po')
                       ->select('po.*, u.first_name, u.last_name, u.email')
                       ->join('users u', 'u.id = po.user_id OR u.id = po.landlord_id', 'left')
                       ->where('po.property_id', $propertyId)
                       ->orderBy('po.ownership_percentage', 'DESC')
                       ->get()
                       ->getResultArray();
    }
}