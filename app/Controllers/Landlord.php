<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Landlord extends BaseController
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
     * Landlord Dashboard
     */
    public function dashboard()
    {
        // Check landlord access
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();

        // Get landlord's properties
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);

        // Get recent payments for landlord's properties
        $recentPayments = $this->paymentModel->getPaymentsByLandlord($landlordId, 10);

        // Get maintenance requests for landlord's properties
        $maintenanceRequests = $this->maintenanceModel->getRequestsByLandlord($landlordId, 10);

        // Calculate dashboard statistics
        $stats = $this->getLandlordStats($landlordId);

        $data = [
            'title' => 'Landlord Dashboard',
            'properties' => $properties ?? [],
            'recent_payments' => $recentPayments ?? [],
            'maintenance_requests' => $maintenanceRequests ?? [],
            'stats' => $stats
        ];

        return view('landlord/dashboard', $data);
    }

    /**
     * View Properties
     */
    public function properties()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);

        $data = [
            'title' => 'My Properties',
            'properties' => $properties ?? []
        ];

        return view('landlord/properties', $data);
    }

    /**
     * View Tenants
     */
    public function tenants()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();

        // Get tenants from leases
        $leases = $this->leaseModel->getLeasesByLandlord($landlordId);
        $tenants = [];
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);

        // Convert leases to tenant format for the view
        foreach ($leases as $lease) {
            $tenant = $this->userModel->find($lease['tenant_id']);
            if ($tenant) {
                $tenant['property_name'] = $lease['property_name'] ?? '';
                $tenant['property_address'] = $lease['property_address'] ?? '';
                $tenant['lease_start'] = $lease['start_date'];
                $tenant['lease_end'] = $lease['end_date'];
                $tenant['rent_amount'] = $lease['rent_amount'];
                $tenant['ownership_percentage'] = $lease['ownership_percentage'] ?? 100;
                $tenant['lease_status'] = $lease['status'];
                $tenant['security_deposit'] = $lease['security_deposit'] ?? 0;
                $tenant['payment_status'] = 'current'; // You might want to calculate this
                $tenants[] = $tenant;
            }
        }

        $data = [
            'title' => 'My Tenants',
            'tenants' => $tenants,
            'properties' => $properties ?? []
        ];

        return view('landlord/tenants', $data);
    }

    /**
     * View Maintenance Requests
     */
    public function maintenance()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();
        $maintenance_requests = $this->maintenanceModel->getRequestsByLandlord($landlordId);
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);

        $data = [
            'title' => 'Maintenance Requests',
            'maintenance_requests' => $maintenance_requests ?? [],
            'properties' => $properties ?? []
        ];

        return view('landlord/maintenance', $data);
    }

    /**
     * View Payments
     */
    public function payments()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();
        $payments = $this->paymentModel->getPaymentsByLandlord($landlordId);
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);

        // Filter by month/year if requested
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year');
        $status = $this->request->getGet('status');
        $property_id = $this->request->getGet('property_id');

        if ($month || $year || $status || $property_id) {
            $payments = array_filter($payments, function ($payment) use ($month, $year, $status, $property_id) {
                $paymentDate = strtotime($payment['payment_date']);
                $matchMonth = !$month || date('m', $paymentDate) == $month;
                $matchYear = !$year || date('Y', $paymentDate) == $year;
                $matchStatus = !$status || $payment['status'] == $status;
                $matchProperty = !$property_id || $payment['property_id'] == $property_id;

                return $matchMonth && $matchYear && $matchStatus && $matchProperty;
            });
        }

        // Calculate payment statistics
        $payment_stats = $this->calculatePaymentStats($payments);

        $data = [
            'title' => 'Payment History',
            'payments' => array_values($payments),
            'properties' => $properties ?? [],
            'payment_stats' => $payment_stats,
            'chart_data' => $this->getPaymentChartData($payments)
        ];

        return view('landlord/payments', $data);
    }

    /**
     * View Reports
     */
    public function reports()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $landlordId = $this->getCurrentUserId();
        $year = $this->request->getGet('year') ?? date('Y');

        // Get data for reports
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        $payments = $this->paymentModel->getPaymentsByLandlord($landlordId);
        $maintenance = $this->maintenanceModel->getRequestsByLandlord($landlordId);

        // Prepare report data
        $report_data = $this->prepareReportData($payments, $maintenance, $properties);
        $financial_summary = $this->calculateFinancialSummary($payments);
        $chart_data = $this->getReportChartData($payments, $properties, $maintenance);

        $data = [
            'title' => 'Reports & Analytics',
            'properties' => $properties ?? [],
            'report_data' => $report_data,
            'financial_summary' => $financial_summary,
            'maintenance_summary' => $this->getMaintenanceSummary($maintenance),
            'chart_data' => $chart_data,
            'generated_reports' => [], // You can implement this later
            'scheduled_reports' => []  // You can implement this later
        ];

        return view('landlord/reports', $data);
    }

    /**
     * Profile Management - UPDATED VERSION
     */
    public function profile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);

        // Get user statistics
        $properties = $this->propertyModel->getPropertiesForLandlord($userId);
        $leases = $this->leaseModel->getLeasesByLandlord($userId);
        $payments = $this->paymentModel->getPaymentsByLandlord($userId);

        $stats = [
            'total_properties' => count($properties),
            'total_tenants' => count($leases),
            'total_income' => '$' . number_format(array_sum(array_column($payments, 'amount')), 0),
            'avg_occupancy' => $this->calculateOccupancyRate($properties)
        ];

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'stats' => $stats
        ];

        return view('landlord/profile', $data);
    }

    /**
     * Update Profile - FIXED VERSION
     */
    public function updateProfile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $userId = $this->getCurrentUserId();

        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
            }
            return redirect()->to('/auth/login');
        }

        // Validation rules based on actual database columns
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Prepare update data with only existing columns
        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $this->userModel->update($userId, $updateData);

            if ($result) {
                // Update session data
                session()->set([
                    'full_name' => $updateData['first_name'] . ' ' . $updateData['last_name']
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Profile updated successfully!'
                    ]);
                }

                $this->setSuccess('Profile updated successfully');
                return redirect()->to('/landlord/profile');
            } else {
                // Get database errors
                $db = \Config\Database::connect();
                $error = $db->error();

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Database error: ' . ($error['message'] ?? 'Unknown error')
                    ]);
                }

                $this->setError('Failed to update profile: ' . ($error['message'] ?? 'Unknown error'));
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }

            $this->setError('Failed to update profile: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * DEBUG: Detailed password change debugging
     * Replace your changePassword method with this temporarily
     */
    public function changePassword()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $userId = $this->getCurrentUserId();
        log_message('info', '=== PASSWORD CHANGE DEBUG START ===');
        log_message('info', 'User ID: ' . $userId);

        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
            }
            return redirect()->to('/auth/login');
        }

        // Log all POST data (careful - contains passwords)
        $postData = $this->request->getPost();
        log_message('info', 'POST fields received: ' . implode(', ', array_keys($postData)));

        // Get the passwords
        $currentPasswordInput = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        log_message('info', 'Current password input length: ' . strlen($currentPasswordInput ?? ''));
        log_message('info', 'New password length: ' . strlen($newPassword ?? ''));
        log_message('info', 'Current password (first 3 chars): ' . substr($currentPasswordInput ?? '', 0, 3));

        // Validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Validation failed: ' . json_encode($errors));

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
            }
            return redirect()->back()->with('validation', $this->validator);
        }

        try {
            // Get current user
            $user = $this->userModel->find($userId);
            log_message('info', 'User found: ' . ($user ? 'Yes' : 'No'));

            if (!$user) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'User not found'
                    ]);
                }
                return redirect()->back();
            }

            $storedPasswordHash = $user['password'];
            log_message('info', 'Stored hash: ' . $storedPasswordHash);
            log_message('info', 'Hash info: ' . json_encode(password_get_info($storedPasswordHash)));

            // Test the exact input against the hash
            log_message('info', 'Testing current password...');
            $passwordVerified = password_verify($currentPasswordInput, $storedPasswordHash);
            log_message('info', 'Password verification result: ' . ($passwordVerified ? 'SUCCESS' : 'FAILED'));

            // If failed, let's test with different variations
            if (!$passwordVerified) {
                log_message('info', 'Testing password variations...');

                // Test without whitespace
                $trimmedInput = trim($currentPasswordInput);
                $trimTest = password_verify($trimmedInput, $storedPasswordHash);
                log_message('info', 'Trimmed password test: ' . ($trimTest ? 'SUCCESS' : 'FAILED'));

                // Test common variations
                $variations = [
                    strtolower($currentPasswordInput),
                    strtoupper($currentPasswordInput),
                    ucfirst($currentPasswordInput)
                ];

                foreach ($variations as $i => $variation) {
                    $result = password_verify($variation, $storedPasswordHash);
                    log_message('info', 'Variation ' . ($i + 1) . ' test: ' . ($result ? 'SUCCESS' : 'FAILED'));
                }
            }

            if (!$passwordVerified) {
                log_message('error', 'All password verification attempts failed');
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Current password is incorrect. Please check your spelling and try again.'
                    ]);
                }
                $this->setError('Current password is incorrect');
                return redirect()->back();
            }

            // If we get here, password verification succeeded
            log_message('info', 'Password verification successful, proceeding with update...');

            // Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            if (!$newPasswordHash) {
                throw new \Exception('Failed to hash new password');
            }

            // Verify the new hash works
            if (!password_verify($newPassword, $newPasswordHash)) {
                throw new \Exception('Password hash verification failed');
            }

            // Update password
            $db = \Config\Database::connect();
            $result = $db->table('users')
                ->where('id', $userId)
                ->update([
                    'password' => $newPasswordHash,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            log_message('info', 'Database update result: ' . ($result ? 'SUCCESS' : 'FAILED'));

            if ($result) {
                // Final verification
                $updatedUser = $db->table('users')->where('id', $userId)->get()->getRowArray();
                $finalVerification = password_verify($newPassword, $updatedUser['password']);
                log_message('info', 'Final verification: ' . ($finalVerification ? 'SUCCESS' : 'FAILED'));

                if (!$finalVerification) {
                    throw new \Exception('Password update verification failed');
                }

                log_message('info', 'Password change completed successfully');

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Password changed successfully!'
                    ]);
                }

                $this->setSuccess('Password changed successfully!');
                return redirect()->to('/landlord/profile');
            } else {
                $error = $db->error();
                log_message('error', 'Database update failed: ' . json_encode($error));
                throw new \Exception('Database update failed');
            }

        } catch (\Exception $e) {
            log_message('error', 'Password change exception: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }

            $this->setError('Failed to change password: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Add Property Directly (No Admin Approval)
     */
    public function addProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'number_of_landlords' => 'required|integer|greater_than[0]|less_than_equal_to[100]',
            'property_address' => 'required|min_length[5]|max_length[1000]',
            'landlord_names' => 'required',
            'ownership_percentages' => 'required',
            'estimated_rent' => 'required|decimal|greater_than[0]',
            'expenses' => 'required|decimal|greater_than_equal_to[0]',
            'management_company' => 'required|max_length[100]',
            'management_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[50]'
        ];

        if (!$this->validate($rules)) {
            // Fixed: Use $this->validator->getErrors() instead of just getErrors()
            $errors = $this->validator->getErrors();
            $errorMessages = [];

            foreach ($errors as $field => $error) {
                $errorMessages[] = $error;
            }

            return $this->respondWithError('Please fill all required fields: ' . implode(', ', $errorMessages), 400);
        }

        // Validate ownership percentages total exactly 100%
        $ownershipPercentages = $this->request->getPost('ownership_percentages');
        $landlordNames = $this->request->getPost('landlord_names');

        // Ensure arrays have same length
        if (count($landlordNames) !== count($ownershipPercentages)) {
            return $this->respondWithError('Mismatch between number of landlord names and ownership percentages', 400);
        }

        $totalOwnership = array_sum(array_map('floatval', $ownershipPercentages));

        if (abs($totalOwnership - 100) >= 0.01) {
            return $this->respondWithError('Total ownership percentage must equal exactly 100%', 400);
        }

        $currentLandlordId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        // Start transaction
        $db->transStart();

        try {
            // Insert property
            $propertyData = [
                'property_name' => $this->request->getPost('property_name'),
                'address' => $this->request->getPost('property_address'),
                'base_rent' => $this->request->getPost('estimated_rent'),
                'expenses' => $this->request->getPost('expenses'),
                'management_company' => $this->request->getPost('management_company'),
                'management_percentage' => $this->request->getPost('management_percentage'),
                'number_of_landlords' => count($landlordNames),
                'status' => 'vacant', // Default status
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $db->table('properties')->insert($propertyData);

            if (!$result) {
                throw new \Exception('Failed to insert property');
            }

            $propertyId = $db->insertID();

            // Insert property ownership for each landlord
            for ($i = 0; $i < count($landlordNames); $i++) {
                $ownershipData = [
                    'property_id' => $propertyId,
                    'landlord_id' => $currentLandlordId, // All ownerships linked to current user for now
                    'landlord_name' => trim($landlordNames[$i]),
                    'ownership_percentage' => floatval($ownershipPercentages[$i]),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $ownershipResult = $db->table('property_ownership')->insert($ownershipData);

                if (!$ownershipResult) {
                    throw new \Exception('Failed to insert property ownership for ' . $landlordNames[$i]);
                }
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Database transaction failed');
            }

            return $this->respondWithSuccess([], 'Property "' . $this->request->getPost('property_name') . '" added successfully with ' . count($landlordNames) . ' landlord(s)!');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Add property error: ' . $e->getMessage());
            return $this->respondWithError('Failed to add property: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Request New Property (shows form to add property)
     */
    public function requestProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $data = [
            'title' => 'Add New Property'
        ];

        return view('landlord/request_property', $data);
    }

    /**
     * Send Admin Message
     */
    public function sendAdminMessage()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        try {
            // Get form data
            $subject = $this->request->getPost('subject');
            $message = $this->request->getPost('message');
            $priority = $this->request->getPost('priority') ?: 'normal';

            // Basic validation
            if (empty($subject)) {
                return $this->respondWithError('Subject is required');
            }

            if (empty($message)) {
                return $this->respondWithError('Message is required');
            }

            if (strlen($message) < 3) {
                return $this->respondWithError('Message must be at least 3 characters');
            }

            // Handle custom subject
            if ($subject === 'Other') {
                $customSubject = $this->request->getPost('custom_subject');
                if (empty($customSubject)) {
                    return $this->respondWithError('Custom subject is required when "Other" is selected');
                }
                $subject = $customSubject;
            }

            $landlordId = $this->getCurrentUserId();
            $landlord = $this->userModel->find($landlordId);

            if (!$landlord) {
                return $this->respondWithError('User not found');
            }

            // Insert message into database
            $db = \Config\Database::connect();

            $messageData = [
                'landlord_id' => $landlordId,
                'landlord_name' => $landlord['first_name'] . ' ' . $landlord['last_name'],
                'landlord_email' => $landlord['email'],
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority,
                'status' => 'unread',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $db->table('admin_messages')->insert($messageData);

            if ($result) {
                return $this->respondWithSuccess([], 'Your message has been sent to the administrator successfully!');
            } else {
                $error = $db->error();
                log_message('error', 'Database insert failed: ' . json_encode($error));
                return $this->respondWithError('Failed to send message: ' . $error['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin message error: ' . $e->getMessage());
            return $this->respondWithError('Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper Methods for Error Handling
     */
    protected function respondWithSuccess($data = [], $message = 'Success')
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }

        session()->setFlashdata('success', $message);
        return redirect()->back();
    }

    protected function respondWithError($message = 'Error occurred', $code = 500)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($code)->setJSON([
                'success' => false,
                'message' => $message
            ]);
        }

        session()->setFlashdata('error', $message);
        return redirect()->back();
    }

    protected function setSuccess($message)
    {
        session()->setFlashdata('success', $message);
    }

    protected function setError($message)
    {
        session()->setFlashdata('error', $message);
    }

    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    protected function requireLandlord()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/auth/login');
        }
        return null;
    }

    /**
     * Helper method to calculate payment statistics
     */
    private function calculatePaymentStats($payments)
    {
        $currentMonth = date('Y-m');
        $currentYear = date('Y');

        $stats = [
            'this_month_collected' => 0,
            'this_month_expected' => 0,
            'outstanding' => 0,
            'year_to_date' => 0,
            'total_collected' => 0,
            'average_payment' => 0,
            'methods' => []
        ];

        $paidPayments = array_filter($payments, function ($p) {
            return $p['status'] === 'paid';
        });
        $thisMonthPaid = array_filter($paidPayments, function ($p) use ($currentMonth) {
            return strpos($p['payment_date'], $currentMonth) === 0;
        });
        $thisYearPaid = array_filter($paidPayments, function ($p) use ($currentYear) {
            return strpos($p['payment_date'], $currentYear) === 0;
        });

        $stats['this_month_collected'] = array_sum(array_column($thisMonthPaid, 'amount'));
        $stats['year_to_date'] = array_sum(array_column($thisYearPaid, 'amount'));
        $stats['total_collected'] = array_sum(array_column($paidPayments, 'amount'));
        $stats['average_payment'] = count($paidPayments) > 0 ? $stats['total_collected'] / count($paidPayments) : 0;

        // Calculate outstanding
        $outstandingPayments = array_filter($payments, function ($p) {
            return $p['status'] !== 'paid';
        });
        $stats['outstanding'] = array_sum(array_column($outstandingPayments, 'amount'));

        return $stats;
    }

    /**
     * Helper method to get payment chart data
     */
    private function getPaymentChartData($payments)
    {
        $monthlyData = [];
        $paidPayments = array_filter($payments, function ($p) {
            return $p['status'] === 'paid';
        });

        foreach ($paidPayments as $payment) {
            $month = date('M Y', strtotime($payment['payment_date']));
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = 0;
            }
            $monthlyData[$month] += $payment['amount'];
        }

        return [
            'labels' => array_keys($monthlyData),
            'data' => array_values($monthlyData)
        ];
    }

    /**
     * Helper methods for reports
     */
    private function prepareReportData($payments, $maintenance, $properties)
    {
        $totalIncome = array_sum(array_column(array_filter($payments, function ($p) {
            return $p['status'] === 'paid';
        }), 'amount'));
        $totalExpected = array_sum(array_column($properties, 'base_rent'));
        $occupiedProperties = count(array_filter($properties, function ($p) {
            return isset($p['lease_status']) && $p['lease_status'] === 'active';
        }));

        return [
            'expected_income' => $totalExpected,
            'collected_income' => $totalIncome,
            'occupancy_rate' => count($properties) > 0 ? ($occupiedProperties / count($properties)) * 100 : 0,
            'avg_maintenance_cost' => count($maintenance) > 0 ? array_sum(array_column($maintenance, 'estimated_cost')) / count($maintenance) : 0,
            'avg_lease_duration' => 1.0, // You can calculate this based on actual lease data
            'alerts' => []
        ];
    }

    private function calculateFinancialSummary($payments)
    {
        $currentMonth = date('Y-m');
        $thisMonthPayments = array_filter($payments, function ($p) use ($currentMonth) {
            return strpos($p['payment_date'], $currentMonth) === 0 && $p['status'] === 'paid';
        });

        $totalIncome = array_sum(array_column($thisMonthPayments, 'amount'));

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalIncome * 0.25, // Estimate 25% expenses
            'net_income' => $totalIncome * 0.75,
            'profit_margin' => 75.0,
            'income_growth' => 5.2, // You can calculate this based on historical data
            'expense_growth' => 3.1,
            'net_growth' => 6.8
        ];
    }

    private function getMaintenanceSummary($maintenance)
    {
        $categories = [];
        foreach ($maintenance as $request) {
            $category = $request['category'] ?? 'General';
            if (!isset($categories[$category])) {
                $categories[$category] = ['count' => 0, 'total_cost' => 0];
            }
            $categories[$category]['count']++;
            $categories[$category]['total_cost'] += $request['estimated_cost'] ?? 0;
        }

        $summary = [];
        foreach ($categories as $category => $data) {
            $summary[] = [
                'category' => ucfirst($category),
                'count' => $data['count'],
                'total_cost' => $data['total_cost'],
                'avg_cost' => $data['count'] > 0 ? $data['total_cost'] / $data['count'] : 0
            ];
        }

        return $summary;
    }

    private function getReportChartData($payments, $properties, $maintenance)
    {
        return [
            'income' => $this->getPaymentChartData($payments),
            'property' => [
                'labels' => ['Occupied', 'Vacant'],
                'data' => [
                    count(array_filter($properties, function ($p) {
                        return isset($p['lease_status']) && $p['lease_status'] === 'active';
                    })),
                    count(array_filter($properties, function ($p) {
                        return !isset($p['lease_status']) || $p['lease_status'] !== 'active';
                    }))
                ]
            ],
            'maintenance' => [
                'labels' => ['Plumbing', 'Electrical', 'HVAC', 'Other'],
                'data' => [150, 100, 200, 75] // You can calculate this from actual data
            ]
        ];
    }

    private function calculateOccupancyRate($properties)
    {
        if (count($properties) === 0)
            return 0;

        $occupiedCount = count(array_filter($properties, function ($p) {
            return isset($p['lease_status']) && $p['lease_status'] === 'active';
        }));

        return ($occupiedCount / count($properties)) * 100;
    }

    /**
     * Get Landlord Statistics
     */
    private function getLandlordStats($landlordId)
    {
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        $payments = $this->paymentModel->getPaymentsByLandlord($landlordId);
        $maintenance = $this->maintenanceModel->getRequestsByLandlord($landlordId);

        $stats = [
            'total_properties' => count($properties),
            'occupied_properties' => count(array_filter($properties, function ($p) {
                return isset($p['lease_status']) && $p['lease_status'] == 'active';
            })),
            'vacant_properties' => count(array_filter($properties, function ($p) {
                return !isset($p['lease_status']) || $p['lease_status'] != 'active';
            })),
            'pending_maintenance' => count(array_filter($maintenance, function ($m) {
                return $m['status'] == 'pending';
            })),
            'monthly_income' => 0,
            'collected_this_month' => 0
        ];

        // Calculate monthly income and collected amount
        $currentMonth = date('Y-m');
        foreach ($payments as $payment) {
            $income = ($payment['amount'] * ($payment['ownership_percentage'] ?? 100) / 100);

            if ($payment['status'] == 'paid' && strpos($payment['payment_date'], $currentMonth) === 0) {
                $stats['collected_this_month'] += $income;
            }

            if (isset($payment['due_date']) && strpos($payment['due_date'], $currentMonth) === 0) {
                $stats['monthly_income'] += $income;
            }
        }

        return $stats;
    }

    /**
     * Help & Support Page
     */
    public function help()
    {
        $redirect = $this->requireLandlord();
        if ($redirect)
            return $redirect;

        $data = [
            'title' => 'Help & Support'
        ];

        return view('landlord/help', $data);
    }


    /**
     * Verify Property Ownership
     */
    private function verifyPropertyOwnership($propertyId, $landlordId)
    {
        $db = \Config\Database::connect();
        $query = $db->table('property_ownership')
            ->where('property_id', $propertyId)
            ->where('landlord_id', $landlordId)
            ->get()
            ->getRowArray();

        return $query ? true : false;
    }

    /**
     * Verify Tenant Access
     */
    private function verifyTenantAccess($tenantId, $landlordId)
    {
        $db = \Config\Database::connect();
        $query = $db->table('leases l')
            ->join('property_ownership po', 'po.property_id = l.property_id')
            ->where('l.tenant_id', $tenantId)
            ->where('po.landlord_id', $landlordId)
            ->where('l.status', 'active')
            ->get()
            ->getRowArray();

        return $query;
    }

    /**
     * Verify Maintenance Access
     */
    private function verifyMaintenanceAccess($requestId, $landlordId)
    {
        $db = \Config\Database::connect();
        $query = $db->table('maintenance_requests mr')
            ->join('property_ownership po', 'po.property_id = mr.property_id')
            ->where('mr.id', $requestId)
            ->where('po.landlord_id', $landlordId)
            ->get()
            ->getRowArray();

        return $query;
    }
}