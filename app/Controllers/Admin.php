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
        // Check admin access
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        // Get dashboard statistics
        $data = [
            'title' => 'Admin Dashboard',
            'stats' => $this->getDashboardStats(),
            'recent_payments' => $this->paymentModel->getRecentPayments(10),
            'pending_maintenance' => $this->maintenanceModel->getPendingRequests(5),
            'overdue_payments' => $this->paymentModel->getOverduePayments(5)
        ];

        return view('admin/dashboard', $data);
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
            'occupied_properties' => $this->propertyModel->where('status', 'occupied')->countAllResults(),
            'vacant_properties' => $this->propertyModel->where('status', 'vacant')->countAllResults(),
            'pending_maintenance' => $this->maintenanceModel->where('status', 'pending')->countAllResults(),
            'overdue_payments' => $this->paymentModel->where('status', 'overdue')->countAllResults()
        ];

        // Calculate total monthly rent
        $rentQuery = $db->query("
            SELECT SUM(l.rent_amount) as total_rent
            FROM leases l 
            WHERE l.status = 'active'
        ");
        $rentResult = $rentQuery->getRowArray();
        $stats['total_monthly_rent'] = $rentResult['total_rent'] ?? 0;

        // Calculate this month's collected rent
        $collectedQuery = $db->query("
            SELECT SUM(p.amount) as collected_rent
            FROM payments p 
            WHERE p.status = 'paid' 
            AND MONTH(p.payment_date) = MONTH(CURDATE())
            AND YEAR(p.payment_date) = YEAR(CURDATE())
        ");
        $collectedResult = $collectedQuery->getRowArray();
        $stats['collected_rent'] = $collectedResult['collected_rent'] ?? 0;

        return $stats;
    }

    /**
     * Send Payment Reminder (AJAX)
     */
    public function sendPaymentReminder($paymentId)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        if (!$this->request->isAJAX()) {
            return $this->respondWithError('Invalid request method', 405);
        }

        // Get payment details
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return $this->respondWithError('Payment not found', 404);
        }

        // In a real application, you would send an actual email
        // For now, we'll just simulate the reminder
        $reminderSent = true; // Simulate email sending

        if ($reminderSent) {
            return $this->respondWithSuccess([], 'Payment reminder sent successfully');
        } else {
            return $this->respondWithError('Failed to send reminder');
        }
    }

    /**
     * Mark Payment as Paid (AJAX)
     */
    public function markPaymentPaid($paymentId)
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        if (!$this->request->isAJAX()) {
            return $this->respondWithError('Invalid request method', 405);
        }

        $updateData = [
            'status' => 'paid',
            'payment_date' => date('Y-m-d'),
            'payment_method' => 'manual',
            'transaction_id' => 'ADMIN_' . time()
        ];

        if ($this->paymentModel->update($paymentId, $updateData)) {
            return $this->respondWithSuccess([], 'Payment marked as paid successfully');
        } else {
            return $this->respondWithError('Failed to update payment status');
        }
    }

    /**
     * Generate Monthly Payments (AJAX)
     */
    public function generateMonthlyPayments()
    {
        $redirect = $this->requireAdmin();
        if ($redirect)
            return $redirect;

        if (!$this->request->isAJAX()) {
            return $this->respondWithError('Invalid request method', 405);
        }

        $month = $this->request->getPost('month') ?? date('m');
        $year = $this->request->getPost('year') ?? date('Y');

        $generatedCount = $this->paymentModel->generateMonthlyRentPayments($month, $year);

        if ($generatedCount > 0) {
            return $this->respondWithSuccess(
                ['generated_count' => $generatedCount],
                "Generated {$generatedCount} payment records successfully"
            );
        } else {
            return $this->respondWithSuccess(
                ['generated_count' => 0],
                'No new payments to generate (already exist for this month)'
            );
        }
    }
}