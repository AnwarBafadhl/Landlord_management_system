<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'username', 
        'email', 
        'password', 
        'role', 
        'first_name', 
        'last_name', 
        'phone', 
        'address', 
        'bank_account', 
        'bank_name', 
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,landlord,tenant,maintenance]',
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name'  => 'required|min_length[2]|max_length[50]'
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username already exists'
        ],
        'email' => [
            'is_unique' => 'Email already exists'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->where('is_active', 1)->findAll();
    }

    /**
     * Get landlords
     */
    public function getLandlords()
    {
        return $this->getUsersByRole('landlord');
    }

    /**
     * Get tenants
     */
    public function getTenants()
    {
        return $this->getUsersByRole('tenant');
    }

    /**
     * Get maintenance staff
     */
    public function getMaintenanceStaff()
    {
        return $this->getUsersByRole('maintenance');
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password)
    {
        $user = $this->where('username', $username)
                     ->orWhere('email', $username)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Remove password from returned data
            return $user;
        }

        return false;
    }

    /**
     * Get user with properties (for landlords)
     */
    public function getLandlordWithProperties($landlordId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*, p.id as property_id, p.property_name, p.address as property_address, 
                         p.base_rent, p.status, po.ownership_percentage');
        $builder->join('property_ownership po', 'po.landlord_id = u.id', 'left');
        $builder->join('properties p', 'p.id = po.property_id', 'left');
        $builder->where('u.id', $landlordId);
        $builder->where('u.role', 'landlord');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get tenant with lease info
     */
    public function getTenantWithLease($tenantId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*, l.*, p.property_name, p.address as property_address');
        $builder->join('leases l', 'l.tenant_id = u.id', 'left');
        $builder->join('properties p', 'p.id = l.property_id', 'left');
        $builder->where('u.id', $tenantId);
        $builder->where('u.role', 'tenant');
        $builder->where('l.status', 'active');
        
        return $builder->get()->getRowArray();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        // Remove password if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        return $this->update($userId, $data);
    }

    /**
     * Get users with filtering options
     */
    public function getUsers($role = null, $search = null)
    {
        $builder = $this->builder();
        
        if ($role) {
            $builder->where('role', $role);
        }
        
        if ($search) {
            $builder->groupStart()
                   ->like('first_name', $search)
                   ->orLike('last_name', $search)
                   ->orLike('email', $search)
                   ->orLike('username', $search)
                   ->groupEnd();
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        // Fix: Use get()->getResultArray() instead of findAll() on builder
        return $builder->get()->getResultArray();
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($userId)
    {
        $user = $this->find($userId);
        if ($user) {
            return $this->update($userId, ['is_active' => !$user['is_active']]);
        }
        return false;
    }
}