<?php

namespace App\Models;

use CodeIgniter\Model;

class PropertyModel extends Model
{
protected $table            = 'properties';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // ✅ include every column you save

protected $allowedFields = [
    'property_name',
    'address',
    'property_value',
    'total_shares',
    'share_value',
    'contribution_duration',
    'management_company',
    'management_percentage',
    'total_units',
    'status',
    'description',
    'created_at',
    'updated_at',
];

protected $useTimestamps = true;
protected $createdField  = 'created_at';
protected $updatedField  = 'updated_at';

// ensure default status 'vacant' at insert time (extra safety)
protected $beforeInsert = ['ensureDefaultStatus'];
protected function ensureDefaultStatus(array $data)
{
    if (empty($data['data']['status'])) {
        $data['data']['status'] = 'vacant';
    }
    return $data;
}

}