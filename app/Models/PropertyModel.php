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
     * Get properties for a specific landlord with ownership details
     */
    public function getPropertiesForLandlord($landlordId)
    {
        return $this->db->table('properties p')
                       ->join('property_ownership po', 'po.property_id = p.id')
                       ->where('po.landlord_id', $landlordId)
                       ->select('p.*, po.ownership_percentage, po.landlord_name')
                       ->orderBy('p.created_at', 'DESC')
                       ->get()
                       ->getResultArray();
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
            $ownershipRatio = $property['ownership_percentage'] / 100;
            $summary['total_rent'] += ($property['base_rent'] * $ownershipRatio);
            $summary['total_expenses'] += ($property['expenses'] * $ownershipRatio);
        }
        
        $summary['net_income'] = $summary['total_rent'] - $summary['total_expenses'];
        
        return $summary;
    }
}