<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;

class Admin extends BaseController
{
    protected $userModel;
    protected $propertyModel;
    protected $paymentModel;
    protected $maintenanceModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->propertyModel = new PropertyModel();
        $this->paymentModel = new PaymentModel();
        $this->maintenanceModel = new MaintenanceRequestModel();
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $data = [
            'title' => 'Admin Dashboard',
            'stats' => $this->getDashboardStats(),
            'recent_entries' => $this->getRecentIncomeExpense(10),
            'recent_transfers' => $this->getRecentTransfers(5),
            'pending_maintenance' => $this->getPendingMaintenance(5), // <-- use helper
        ];

        return view('admin/dashboard', $data);
    }

    private function getPendingMaintenance(int $limit = 5): array
    {
        $db = \Config\Database::connect();
        return $db->table('maintenance_requests mr')
            ->select('mr.*, p.property_name')               // <-- bring property_name
            ->join('properties p', 'p.id = mr.property_id', 'left')
            ->where('mr.status', 'pending')
            ->orderBy('mr.requested_date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }


    /**
     * User Management
     */
    public function users()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $role = $this->request->getGet('role');
        $search = $this->request->getGet('search');

        $users = $this->userModel->getUsers($role, $search);

        $data = [
            'title' => 'User Management',
            'users' => $users,
            'current_role' => $role,
            'search_term' => $search
        ];

        return view('admin/users/index', $data);
    }

    /**
     * Create User Form
     */
    public function createUser()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $data = [
            'title' => 'Create User',
            'validation' => \Config\Services::validation()
        ];

        return view('admin/users/create', $data);
    }

    /**
     * Store New User
     */
    public function storeUser()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[admin,landlord,maintenance]',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => $this->request->getPost('role'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_name' => $this->request->getPost('bank_name'),
            'is_active' => 1
        ];

        if ($this->userModel->insert($userData)) {
            $this->setSuccess('User created successfully');
        } else {
            $this->setError('Failed to create user');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Edit User Form
     */
    public function editUser($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setError('User not found');
            return redirect()->to('/admin/users');
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/users/edit', $data);
    }

    /**
     * Update User
     */
    public function updateUser($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setError('User not found');
            return redirect()->to('/admin/users');
        }

        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role' => 'required|in_list[admin,landlord,maintenance]',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]'
        ];

        // Add password validation only if password is provided
        if (!empty($this->request->getPost('password'))) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_name' => $this->request->getPost('bank_name')
        ];

        // Add password only if provided
        if (!empty($this->request->getPost('password'))) {
            $userData['password'] = $this->request->getPost('password');
        }

        if ($this->userModel->update($id, $userData)) {
            $this->setSuccess('User updated successfully');
        } else {
            $this->setError('Failed to update user');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Delete User
     */
    public function deleteUser($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        // Don't allow deletion of current admin user
        if ($id == $this->getCurrentUserId()) {
            $this->setError('You cannot delete your own account');
            return redirect()->to('/admin/users');
        }

        if ($this->userModel->delete($id)) {
            $this->setSuccess('User deleted successfully');
        } else {
            $this->setError('Failed to delete user');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Toggle User Status
     */
    public function toggleUserStatus($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        if ($this->userModel->toggleStatus($id)) {
            $this->setSuccess('User status updated successfully');
        } else {
            $this->setError('Failed to update user status');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Property Management
     */
    public function properties()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $properties = $this->propertyModel->getPropertiesWithLandlords();

        $data = [
            'title' => 'Property Management',
            'properties' => $properties
        ];

        return view('admin/properties/index', $data);
    }

    /**
     * Financial Reports
     */
    public function financials()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $data = [
            'title' => 'Financial Management',
            'payments' => $this->paymentModel->getAllPayments(),
            'overdue_payments' => $this->paymentModel->getOverduePayments(),
            'monthly_stats' => $this->paymentModel->getMonthlyStats()
        ];

        return view('admin/financials/index', $data);
    }

    /**
     * System Settings
     */
    public function settings()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $db = \Config\Database::connect();
        $settings = $db->table('system_settings')->get()->getResultArray();

        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = $setting['setting_value'];
        }

        $data = [
            'title' => 'System Settings',
            'settings' => $settingsArray
        ];

        return view('admin/settings/index', $data);
    }

    /**
     * Update System Settings
     */
    public function updateSettings()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        $db = \Config\Database::connect();
        $postData = $this->request->getPost();

        foreach ($postData as $key => $value) {
            if ($key !== 'csrf_token_name') {
                $db->table('system_settings')
                    ->where('setting_key', $key)
                    ->update(['setting_value' => $value]);
            }
        }

        $this->setSuccess('Settings updated successfully');
        return redirect()->to('/admin/settings');
    }

    /**
     * Get Dashboard Statistics
     */
    private function getDashboardStats()
    {
        $db = \Config\Database::connect();

        $stats = [
            'total_users' => $this->userModel->countAllResults(),
            'total_landlords' => $this->userModel->where('role', 'landlord')->countAllResults(),
            'total_properties' => $this->propertyModel->countAllResults(),
            'occupied_properties' => 0,
            'vacant_properties' => 0,
            'pending_maintenance' => $db->table('maintenance_requests')->where('status', 'pending')->countAllResults(),
            // monthly financials
            'monthly_income' => 0.0,
            'monthly_expense' => 0.0,
            'net_monthly' => 0.0,
        ];

        // ----- Occupancy inference (optional; safe if you lack a properties.status) -----
        $tables = array_map('strtolower', $db->listTables());
        $hasUnits = in_array('property_units', $tables, true);
        $hasLeases = in_array('leases', $tables, true);

        if ($hasUnits && $hasLeases) {
            $rows = $db->query("
            SELECT p.id,
                   COUNT(u.id) AS total_units,
                   SUM(CASE WHEN l.id IS NULL THEN 0 ELSE 1 END) AS occupied_units
            FROM properties p
            LEFT JOIN property_units u ON u.property_id = p.id
            LEFT JOIN leases l
              ON l.unit_id = u.id
             AND (
                   (l.status = 'active') OR
                   (l.start_date <= CURDATE() AND (l.end_date IS NULL OR l.end_date >= CURDATE()))
                 )
            GROUP BY p.id
        ")->getResultArray();

            $occ = 0;
            $vac = 0;
            foreach ($rows as $r) {
                if ((int) $r['total_units'] === 0) {
                    $vac++;
                } else {
                    ((int) $r['occupied_units'] > 0) ? $occ++ : $vac++;
                }
            }
            $stats['occupied_properties'] = $occ;
            $stats['vacant_properties'] = $vac;
        }

        // ----- Monthly income / expense / net from income_expense_payments -----
        $ym = date('Y-m');
        $monthly = $db->query("
        SELECT
            SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income_sum,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense_sum
        FROM income_expense_payments
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
    ", [$ym])->getRowArray() ?: ['income_sum' => 0, 'expense_sum' => 0];

        $stats['monthly_income'] = (float) $monthly['income_sum'];
        $stats['monthly_expense'] = (float) $monthly['expense_sum'];
        $stats['net_monthly'] = $stats['monthly_income'] - $stats['monthly_expense'];

        return $stats;
    }

    private function getRecentIncomeExpense(int $limit = 10): array
    {
        $db = \Config\Database::connect();
        return $db->table('income_expense_payments iep')
            ->select('iep.*, p.property_name, pu.unit_name')
            ->join('properties p', 'p.id = iep.property_id', 'left')
            ->join('property_units pu', 'pu.id = iep.unit_id', 'left')
            ->orderBy('iep.date', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    private function getRecentTransfers(int $limit = 5): array
    {
        $db = \Config\Database::connect();
        return $db->table('transfer_receipts tr')
            ->select('tr.*, p.property_name')
            ->join('properties p', 'p.id = tr.property_id', 'left')
            ->orderBy('tr.transfer_date', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}