<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;

class Tenant extends BaseController
{
    protected $userModel;
    protected $propertyModel;
    protected $leaseModel;
    protected $paymentModel;
    protected $maintenanceModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->propertyModel = new PropertyModel();
        $this->leaseModel = new LeaseModel();
        $this->paymentModel = new PaymentModel();
        $this->maintenanceModel = new MaintenanceRequestModel();
    }

    /**
     * Tenant Dashboard - Fixed to remove property_type references
     */
    public function dashboard()
    {
        // Check tenant access
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get tenant's current lease with property info - REMOVED property_type
            $lease = $db->table('leases l')
                ->select('l.*, 
                         p.property_name, p.address as property_address, p.property_value,
                         pu.unit_number, pu.unit_name, pu.rent_amount')
                ->join('properties p', 'p.id = l.property_id')
                ->join('property_units pu', 'pu.id = l.unit_id', 'left')
                ->where('l.tenant_id', $tenantId)
                ->where('l.status', 'active')
                ->orderBy('l.created_at', 'DESC')
                ->get()
                ->getRowArray();

            // Get recent payments
            $recentPayments = $db->table('payments p')
                ->select('p.*, prop.property_name, pu.unit_number')
                ->join('property_units pu', 'pu.id = p.unit_id', 'left')
                ->join('properties prop', 'prop.id = pu.property_id', 'left')
                ->where('p.tenant_id', $tenantId)
                ->orderBy('p.payment_date', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Get recent maintenance requests
            $maintenanceRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_number')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->join('properties p', 'p.id = pu.property_id', 'left')
                ->where('mr.tenant_id', $tenantId)
                ->orderBy('mr.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Calculate next payment due
            $nextPayment = null;
            if ($lease) {
                $nextPaymentDate = date('Y-m-d', strtotime('+1 month', strtotime($lease['lease_start'] ?? 'now')));
                $nextPayment = [
                    'due_date' => $nextPaymentDate,
                    'amount' => $lease['rent_amount'] ?? 0,
                    'property_name' => $lease['property_name'] ?? 'N/A'
                ];
            }

            // Calculate dashboard statistics
            $stats = [
                'total_payments' => count($recentPayments),
                'pending_maintenance' => count(array_filter($maintenanceRequests, function($req) {
                    return $req['status'] === 'pending';
                })),
                'lease_days_remaining' => $lease ? max(0, ceil((strtotime($lease['lease_end']) - time()) / (60 * 60 * 24))) : 0,
                'total_paid_this_month' => array_sum(array_column(
                    array_filter($recentPayments, function($payment) {
                        return date('Y-m', strtotime($payment['payment_date'])) === date('Y-m');
                    }),
                    'amount'
                ))
            ];

            $data = [
                'title' => 'Tenant Dashboard',
                'lease' => $lease,
                'recent_payments' => $recentPayments,
                'maintenance_requests' => $maintenanceRequests,
                'next_payment' => $nextPayment,
                'stats' => $stats
            ];

            return view('tenant/dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Tenant dashboard error: ' . $e->getMessage());
            
            // Return with empty data if there's an error
            $data = [
                'title' => 'Tenant Dashboard',
                'lease' => null,
                'recent_payments' => [],
                'maintenance_requests' => [],
                'next_payment' => null,
                'stats' => [
                    'total_payments' => 0,
                    'pending_maintenance' => 0,
                    'lease_days_remaining' => 0,
                    'total_paid_this_month' => 0
                ]
            ];

            return view('tenant/dashboard', $data);
        }
    }

    /**
     * View Tenant Profile
     */
    public function profile()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();
        $user = $this->userModel->find($tenantId);

        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];

        return view('tenant/profile', $data);
    }

    /**
     * Update Tenant Profile
     */
    public function updateProfile()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address')
        ];

        if ($this->userModel->update($tenantId, $updateData)) {
            // Update session data
            session()->set('full_name', $updateData['first_name'] . ' ' . $updateData['last_name']);
            
            $this->setSuccess('Profile updated successfully!');
            return redirect()->to('/tenant/profile');
        } else {
            $this->setError('Failed to update profile. Please try again.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Change Password
     */
    public function changePassword()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('validation', $this->validator);
        }

        // Get current user
        $user = $this->userModel->find($tenantId);
        
        // Verify current password
        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            $this->setError('Current password is incorrect.');
            return redirect()->back();
        }

        // Update password
        $updateData = [
            'password' => $this->request->getPost('new_password')
        ];

        if ($this->userModel->update($tenantId, $updateData)) {
            $this->setSuccess('Password changed successfully!');
            return redirect()->to('/tenant/profile');
        } else {
            $this->setError('Failed to change password. Please try again.');
            return redirect()->back();
        }
    }

    /**
     * View Lease Information
     */
    public function lease()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get tenant's lease with property info - REMOVED property_type
            $lease = $db->table('leases l')
                ->select('l.*, 
                         p.property_name, p.address as property_address, p.property_value,
                         pu.unit_number, pu.unit_name, pu.rent_amount,
                         landlord.first_name as landlord_first_name, landlord.last_name as landlord_last_name, 
                         landlord.email as landlord_email, landlord.phone as landlord_phone')
                ->join('properties p', 'p.id = l.property_id')
                ->join('property_units pu', 'pu.id = l.unit_id', 'left')
                ->join('users landlord', 'landlord.id = l.landlord_id', 'left')
                ->where('l.tenant_id', $tenantId)
                ->where('l.status', 'active')
                ->get()
                ->getRowArray();

            $data = [
                'title' => 'My Lease',
                'lease' => $lease
            ];

            return view('tenant/lease', $data);

        } catch (\Exception $e) {
            log_message('error', 'Tenant lease error: ' . $e->getMessage());
            
            $data = [
                'title' => 'My Lease',
                'lease' => null
            ];

            return view('tenant/lease', $data);
        }
    }

    /**
     * View Payments
     */
    public function payments()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get all payments for tenant
            $payments = $db->table('payments p')
                ->select('p.*, prop.property_name, pu.unit_number')
                ->join('property_units pu', 'pu.id = p.unit_id', 'left')
                ->join('properties prop', 'prop.id = pu.property_id', 'left')
                ->where('p.tenant_id', $tenantId)
                ->orderBy('p.payment_date', 'DESC')
                ->get()
                ->getResultArray();

            $data = [
                'title' => 'Payment History',
                'payments' => $payments
            ];

            return view('tenant/payments', $data);

        } catch (\Exception $e) {
            log_message('error', 'Tenant payments error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Payment History',
                'payments' => []
            ];

            return view('tenant/payments', $data);
        }
    }

    /**
     * View Maintenance Requests
     */
    public function maintenance()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get maintenance requests for tenant
            $maintenanceRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_number')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->join('properties p', 'p.id = pu.property_id', 'left')
                ->where('mr.tenant_id', $tenantId)
                ->orderBy('mr.created_at', 'DESC')
                ->get()
                ->getResultArray();

            $data = [
                'title' => 'Maintenance Requests',
                'maintenance_requests' => $maintenanceRequests
            ];

            return view('tenant/maintenance', $data);

        } catch (\Exception $e) {
            log_message('error', 'Tenant maintenance error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Maintenance Requests',
                'maintenance_requests' => []
            ];

            return view('tenant/maintenance', $data);
        }
    }

    /**
     * Helper method to require tenant authentication
     */
    protected function requireTenant()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth/login')->with('error', 'Please log in to continue.');
        }

        if ($this->getCurrentUserRole() !== 'tenant') {
            return redirect()->to('/auth/login')->with('error', 'Access denied. Tenant privileges required.');
        }

        return null;
    }
}