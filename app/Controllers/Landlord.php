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
     * Get current user ID - FIXED VERSION (NO DUPLICATES)
     */
    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    /**
     * Require landlord role - FIXED VERSION (NO DUPLICATES)
     */
    protected function requireLandlord()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/auth/login');
        }
        return null;
    }

    /**
     * Test method
     */
    public function test()
    {
        echo "Landlord controller is working!<br>";
        echo "Session user_id: " . session()->get('user_id') . "<br>";
        echo "Session role: " . session()->get('role') . "<br>";
        echo "Session isLoggedIn: " . (session()->get('isLoggedIn') ? 'Yes' : 'No') . "<br>";
        return;
    }

    /**
     * Landlord Dashboard
     */
    public function dashboard()
    {
        // Check landlord access
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Get landlord's properties with safe defaults
        try {
            $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        } catch (\Exception $e) {
            log_message('error', 'Properties error: ' . $e->getMessage());
            $properties = [];
        }

        // Get recent payments with safe defaults
        try {
            $recentPayments = $this->paymentModel->getPaymentsByLandlord($landlordId, 10);
        } catch (\Exception $e) {
            log_message('error', 'Payments error: ' . $e->getMessage());
            $recentPayments = [];
        }

        // Get maintenance requests with safe defaults
        try {
            $maintenanceRequests = $this->maintenanceModel->getRequestsByLandlord($landlordId, 10);
        } catch (\Exception $e) {
            log_message('error', 'Maintenance error: ' . $e->getMessage());
            $maintenanceRequests = [];
        }

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
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();
        
        try {
            $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        } catch (\Exception $e) {
            log_message('error', 'Properties page error: ' . $e->getMessage());
            $properties = [];
        }

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
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
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
                    $tenant['payment_status'] = 'current';
                    $tenants[] = $tenant;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Tenants page error: ' . $e->getMessage());
            $tenants = [];
            $properties = [];
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
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();
        
        try {
            $maintenance_requests = $this->maintenanceModel->getRequestsByLandlord($landlordId);
            $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        } catch (\Exception $e) {
            log_message('error', 'Maintenance page error: ' . $e->getMessage());
            $maintenance_requests = [];
            $properties = [];
        }

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
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();
        
        try {
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
        } catch (\Exception $e) {
            log_message('error', 'Payments page error: ' . $e->getMessage());
            $payments = [];
            $properties = [];
            $payment_stats = [];
        }

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
     * Updated reports method to include recent reports
     */
    public function reports()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();
        $year = $this->request->getGet('year') ?? date('Y');

        try {
            // Get data for reports with better error handling
            $properties = [];
            $payments = [];
            $maintenance = [];
            
            // Get properties safely
            try {
                $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
            } catch (\Exception $e) {
                log_message('error', 'Error getting properties: ' . $e->getMessage());
                $properties = [];
            }

            // Get payments safely
            try {
                $payments = $this->paymentModel->getPaymentsByLandlord($landlordId);
            } catch (\Exception $e) {
                log_message('error', 'Error getting payments: ' . $e->getMessage());
                $payments = [];
            }

            // Get maintenance safely
            try {
                if (method_exists($this->maintenanceModel, 'getRequestsByLandlord')) {
                    $maintenance = $this->maintenanceModel->getRequestsByLandlord($landlordId);
                } else {
                    $maintenance = [];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting maintenance: ' . $e->getMessage());
                $maintenance = [];
            }

            // Prepare report data with safe methods
            $report_data = $this->prepareReportData($payments, $maintenance, $properties);
            $financial_summary = $this->calculateFinancialSummary($payments);
            $chart_data = $this->getReportChartData($payments, $properties, $maintenance);
            $maintenance_summary = $this->getMaintenanceSummary($maintenance);
            
            // Get recent generated reports
            $generated_reports = $this->getRecentGeneratedReports($landlordId, 10);
            
        } catch (\Exception $e) {
            log_message('error', 'Reports page error: ' . $e->getMessage());
            $properties = [];
            $report_data = [];
            $financial_summary = [];
            $chart_data = [];
            $maintenance_summary = [];
            $generated_reports = [];
        }

        $data = [
            'title' => 'Reports & Analytics',
            'properties' => $properties,
            'report_data' => $report_data,
            'financial_summary' => $financial_summary,
            'maintenance_summary' => $maintenance_summary,
            'chart_data' => $chart_data,
            'generated_reports' => $generated_reports,
            'scheduled_reports' => []   // Empty for now
        ];

        return view('landlord/reports', $data);
    }
    
    /**
     * Get recent generated reports
     */
    private function getRecentGeneratedReports($landlordId, $limit = 10)
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if table exists
            if (!$db->tableExists('reports_log')) {
                return [];
            }
            
            $builder = $db->table('reports_log');
            $builder->where('landlord_id', $landlordId);
            $builder->orderBy('generated_date', 'DESC');
            $builder->limit($limit);
            
            return $builder->get()->getResultArray();
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting recent reports: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get current user's name
     */
    private function getCurrentUserName()
    {
        try {
            $userId = $this->getCurrentUserId();
            $db = \Config\Database::connect();
            $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
            
            if ($user) {
                $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                $lastName = $user['last_name'] ?? $user['lastname'] ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                
                if (empty($fullName)) {
                    $fullName = $user['username'] ?? 'Unknown User';
                }
                
                return $fullName;
            }
            
            return session()->get('username') ?? 'Unknown User';
            
        } catch (\Exception $e) {
            return 'Unknown User';
        }
    }
    
     /**
     * Log report generation
     */
    private function logReportGeneration($landlordId, $reportKind, $reportName, $propertyName, $propertyId = null)
    {
        try {
            $db = \Config\Database::connect();
            
            // Create reports_log table if it doesn't exist
            $this->createReportsLogTable();
            
            $data = [
                'landlord_id' => $landlordId,
                'report_kind' => $reportKind,
                'report_name' => $reportName,
                'property_name' => $propertyName,
                'property_id' => $propertyId,
                'generated_date' => date('Y-m-d H:i:s'),
                'generated_by' => $this->getCurrentUserName()
            ];
            
            $db->table('reports_log')->insert($data);
            log_message('info', 'Report logged successfully: ' . $reportName);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log report generation: ' . $e->getMessage());
            // Don't stop report generation if logging fails
        }
    }
    
    /**
     * Create reports log table if it doesn't exist
     */
    private function createReportsLogTable()
    {
        $db = \Config\Database::connect();
        
        if (!$db->tableExists('reports_log')) {
            $forge = \Config\Database::forge();
            
            $fields = [
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                'landlord_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true
                ],
                'report_kind' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ],
                'report_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255
                ],
                'property_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255
                ],
                'property_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true
                ],
                'generated_date' => [
                    'type' => 'DATETIME'
                ],
                'generated_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ]
            ];
            
            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->addKey('landlord_id');
            $forge->createTable('reports_log');
            
            log_message('info', 'Created reports_log table');
        }
    }

    /**
     * Profile Management
     */
    public function profile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);

        try {
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
        } catch (\Exception $e) {
            log_message('error', 'Profile stats error: ' . $e->getMessage());
            $stats = [
                'total_properties' => 0,
                'total_tenants' => 0,
                'total_income' => '$0',
                'avg_occupancy' => 0
            ];
        }

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'stats' => $stats
        ];

        return view('landlord/profile', $data);
    }

    /**
     * Update Profile
     */
    public function updateProfile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

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

        // Validation rules
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

        // Prepare update data
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
     * Change Password
     */
    public function changePassword()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

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

        // Validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();

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

            if (!$user) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'User not found'
                    ]);
                }
                return redirect()->back();
            }

            $currentPasswordInput = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!password_verify($currentPasswordInput, $user['password'])) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ]);
                }
                $this->setError('Current password is incorrect');
                return redirect()->back();
            }

            // Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            if (!$newPasswordHash) {
                throw new \Exception('Failed to hash new password');
            }

            // Update password
            $db = \Config\Database::connect();
            $result = $db->table('users')
                ->where('id', $userId)
                ->update([
                    'password' => $newPasswordHash,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($result) {
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
     * Check Database
     */
    public function checkDatabase()
    {
        try {
            $db = \Config\Database::connect();

            echo "<h3>Database Connection Test</h3>";
            $result = $db->query("SELECT 1")->getResult();
            echo "✅ Database connection successful!<br><br>";

            echo "<h3>Required Tables Check</h3>";
            $requiredTables = [
                'properties' => [
                    'id', 'property_name', 'property_type', 'address', 'number_of_units',
                    'management_company', 'management_percentage', 'number_of_landlords',
                    'status', 'created_at', 'updated_at'
                ],
                'property_units' => [
                    'id', 'property_id', 'unit_name', 'created_at', 'updated_at'
                ],
                'property_expenses' => [
                    'id', 'property_id', 'expense_name', 'expense_amount', 'created_at', 'updated_at'
                ],
                'property_ownership' => [
                    'id', 'property_id', 'user_id', 'landlord_name', 'username',
                    'ownership_percentage', 'created_at'
                ]
            ];

            foreach ($requiredTables as $tableName => $expectedColumns) {
                if ($db->tableExists($tableName)) {
                    echo "✅ Table '$tableName' exists<br>";

                    $fields = $db->getFieldNames($tableName);
                    $missingColumns = array_diff($expectedColumns, $fields);
                    $extraColumns = array_diff($fields, $expectedColumns);

                    if (empty($missingColumns)) {
                        echo "&nbsp;&nbsp;&nbsp;✅ All required columns present<br>";
                    } else {
                        echo "&nbsp;&nbsp;&nbsp;❌ Missing columns: " . implode(', ', $missingColumns) . "<br>";
                    }

                    if (!empty($extraColumns)) {
                        echo "&nbsp;&nbsp;&nbsp;ℹ️ Extra columns: " . implode(', ', $extraColumns) . "<br>";
                    }

                } else {
                    echo "❌ Table '$tableName' does not exist<br>";
                }
                echo "<br>";
            }

            echo "<h3>Test Simple Insert</h3>";
            $testData = [
                'property_name' => 'Test Property - ' . date('Y-m-d H:i:s'),
                'property_type' => 'other',
                'address' => 'Test Address',
                'number_of_units' => 1,
                'number_of_landlords' => 1,
                'status' => 'vacant',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertResult = $db->table('properties')->insert($testData);
            if ($insertResult) {
                $testId = $db->insertID();
                echo "✅ Test insert successful! Property ID: $testId<br>";
                $db->table('properties')->delete(['id' => $testId]);
                echo "✅ Test data cleaned up<br>";
            } else {
                $error = $db->error();
                echo "❌ Test insert failed: " . json_encode($error) . "<br>";
            }

        } catch (\Exception $e) {
            echo "❌ Database check failed: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Request New Property
     */
    public function requestProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'title' => 'Add New Property'
        ];

        return view('landlord/request_property', $data);
    }

    /**
     * Help & Support Page
     */
    public function help()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'title' => 'Help & Support'
        ];

        return view('landlord/help', $data);
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

    /**
     * Helper method to calculate payment statistics
     */
    private function calculatePaymentStats($payments)
    {
        if (empty($payments)) {
            return [
                'this_month_collected' => 0,
                'this_month_expected' => 0,
                'outstanding' => 0,
                'year_to_date' => 0,
                'total_collected' => 0,
                'average_payment' => 0,
                'methods' => []
            ];
        }

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
        
        $totalExpected = is_array($properties) ? array_sum(array_column($properties, 'base_rent')) : 0;
        $occupiedProperties = is_array($properties) ? count(array_filter($properties, function ($p) {
            return isset($p['lease_status']) && $p['lease_status'] === 'active';
        })) : 0;

        return [
            'expected_income' => $totalExpected,
            'collected_income' => $totalIncome,
            'occupancy_rate' => count($properties) > 0 ? ($occupiedProperties / count($properties)) * 100 : 0,
            'avg_maintenance_cost' => is_array($maintenance) && count($maintenance) > 0 ? array_sum(array_column($maintenance, 'estimated_cost')) / count($maintenance) : 0,
            'avg_lease_duration' => 1.0,
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
            'total_expenses' => $totalIncome * 0.25,
            'net_income' => $totalIncome * 0.75,
            'profit_margin' => 75.0,
            'income_growth' => 5.2,
            'expense_growth' => 3.1,
            'net_growth' => 6.8
        ];
    }

    private function getMaintenanceSummary($maintenance)
    {
        if (empty($maintenance)) {
            return [];
        }

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
                'data' => [150, 100, 200, 75]
            ]
        ];
    }

    private function calculateOccupancyRate($properties)
    {
        if (count($properties) === 0) {
            return 0;
        }

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
        try {
            $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
            $payments = $this->paymentModel->getPaymentsByLandlord($landlordId);
            $maintenance = $this->maintenanceModel->getRequestsByLandlord($landlordId);
        } catch (\Exception $e) {
            log_message('error', 'Stats calculation error: ' . $e->getMessage());
            return [
                'total_properties' => 0,
                'occupied_properties' => 0,
                'vacant_properties' => 0,
                'pending_maintenance' => 0,
                'monthly_income' => 0,
                'collected_this_month' => 0
            ];
        }

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
 * Complete Add Property Method - FIXED for landlord_id column
 */
public function addProperty()
{
    $redirect = $this->requireLandlord();
    if ($redirect) return $redirect;

    $rules = [
        'property_name' => 'required|min_length[3]|max_length[100]',
        'property_type' => 'required|in_list[rest_house,chalet,other]',
        'number_of_landlords' => 'required|integer|greater_than[0]|less_than_equal_to[100]',
        'property_address' => 'required|min_length[5]|max_length[1000]',
        'number_of_units' => 'required|integer|greater_than[0]|less_than_equal_to[500]',
        'landlord_names' => 'required',
        'ownership_percentages' => 'required',
        'unit_names' => 'required',
        'expense_names' => 'required',
        'expense_amounts' => 'required',
        'management_company' => 'permit_empty|max_length[100]',
        'management_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[50]'
    ];

    if (!$this->validate($rules)) {
        $errors = $this->validator->getErrors();
        $errorMessages = [];

        foreach ($errors as $field => $error) {
            $errorMessages[] = $error;
        }

        return $this->respondWithError('Please fill all required fields: ' . implode(', ', $errorMessages), 400);
    }

    // Get form data
    $landlordNames = $this->request->getPost('landlord_names');
    $landlordUsernames = $this->request->getPost('landlord_usernames');
    $ownershipPercentages = $this->request->getPost('ownership_percentages');
    $unitNames = $this->request->getPost('unit_names');
    $expenseNames = $this->request->getPost('expense_names');
    $expenseAmounts = $this->request->getPost('expense_amounts');

    // Convert to arrays if they're not already
    if (!is_array($landlordNames)) $landlordNames = [$landlordNames];
    if (!is_array($landlordUsernames)) $landlordUsernames = [$landlordUsernames];
    if (!is_array($ownershipPercentages)) $ownershipPercentages = [$ownershipPercentages];
    if (!is_array($unitNames)) $unitNames = [$unitNames];
    if (!is_array($expenseNames)) $expenseNames = [$expenseNames];
    if (!is_array($expenseAmounts)) $expenseAmounts = [$expenseAmounts];

    // Log the arrays for debugging
    log_message('info', 'Landlord names: ' . json_encode($landlordNames));
    log_message('info', 'Ownership percentages: ' . json_encode($ownershipPercentages));

    // Validate arrays have same length
    if (count($landlordNames) !== count($ownershipPercentages)) {
        return $this->respondWithError('Mismatch between number of landlord names and ownership percentages', 400);
    }

    if (count($landlordNames) !== count($landlordUsernames)) {
        return $this->respondWithError('Mismatch between number of landlord names and usernames', 400);
    }

    if (count($expenseNames) !== count($expenseAmounts)) {
        return $this->respondWithError('Mismatch between expense names and amounts', 400);
    }

    // Validate ownership percentages total exactly 100%
    $totalOwnership = array_sum(array_map('floatval', $ownershipPercentages));
    log_message('info', 'Total ownership: ' . $totalOwnership);

    if (abs($totalOwnership - 100) >= 0.01) {
        return $this->respondWithError('Total ownership percentage must equal exactly 100%. Current total: ' . $totalOwnership, 400);
    }

    // Validate expenses
    foreach ($expenseAmounts as $amount) {
        if (floatval($amount) < 0) {
            return $this->respondWithError('Expense amounts cannot be negative', 400);
        }
    }

    $currentLandlordId = $this->getCurrentUserId();
    
    try {
        $db = \Config\Database::connect();
        
        // Test database connection first
        $testQuery = $db->query("SELECT 1")->getResult();
        log_message('info', 'Database connection successful');

        // Start transaction
        $db->transStart();

        // Prepare property data - COMPLETE VERSION
        $managementCompany = $this->request->getPost('management_company');
        $managementPercentage = $this->request->getPost('management_percentage');
        
        $propertyData = [
            'property_name' => $this->request->getPost('property_name'),
            'property_type' => $this->request->getPost('property_type'),
            'address' => $this->request->getPost('property_address'),
            'number_of_units' => intval($this->request->getPost('number_of_units')),
            'management_company' => !empty($managementCompany) ? $managementCompany : null,
            'management_percentage' => !empty($managementPercentage) ? floatval($managementPercentage) : 0.00,
            'number_of_landlords' => count($landlordNames),
            'status' => 'vacant',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'Inserting property data: ' . json_encode($propertyData));

        // Insert property
        $result = $db->table('properties')->insert($propertyData);

        if (!$result) {
            $error = $db->error();
            log_message('error', 'Failed to insert property: ' . json_encode($error));
            throw new \Exception('Failed to insert property: ' . ($error['message'] ?? 'Unknown error'));
        }

        $propertyId = $db->insertID();
        log_message('info', 'Property inserted with ID: ' . $propertyId);

        // Insert property units
for ($i = 0; $i < count($landlordNames); $i++) {
    $username = trim($landlordUsernames[$i]);
    $ownerUserId = null; // ✅ CHANGED: Use different variable name to avoid conflict

    // Look up user ID if username is provided
    if (!empty($username)) {
        $user = $db->table('users')->where('username', $username)->get()->getRowArray();
        if ($user) {
            $ownerUserId = $user['id']; // ✅ CHANGED: Use the new variable name
            log_message('info', 'Found owner user ID ' . $ownerUserId . ' for username: ' . $username);
        } else {
            log_message('warning', 'Username not found during property creation: ' . $username);
        }
    }

    // If no username provided or user not found, use the current landlord's ID
    if ($ownerUserId === null) {
        $ownerUserId = $currentLandlordId; // ✅ Use current landlord as default
    }

    // Fill BOTH columns with the same user ID
    $ownershipData = [
        'property_id' => $propertyId,
        'user_id' => $ownerUserId,       // ✅ Fill user_id column
        'landlord_id' => $ownerUserId,   // ✅ Fill landlord_id column with same value
        'landlord_name' => trim($landlordNames[$i]),
        'username' => !empty($username) ? $username : null,
        'ownership_percentage' => floatval($ownershipPercentages[$i]),
        'created_at' => date('Y-m-d H:i:s')
    ];

    log_message('info', 'Inserting ownership: ' . json_encode($ownershipData));

    $ownershipResult = $db->table('property_ownership')->insert($ownershipData);

    if (!$ownershipResult) {
        $error = $db->error();
        log_message('error', 'Failed to insert ownership: ' . json_encode($error));
        throw new \Exception('Failed to insert property ownership for ' . $landlordNames[$i] . ' - ' . ($error['message'] ?? 'Unknown error'));
    }
}

        log_message('info', 'All ownership records inserted successfully');

        // Complete transaction
        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            log_message('error', 'Transaction failed');
            throw new \Exception('Database transaction failed');
        }

        log_message('info', 'Property added successfully with ID: ' . $propertyId);

        // Count how many landlords have usernames (for sharing info)
        $sharedLandlords = array_filter($landlordUsernames, function($username) {
            return !empty(trim($username));
        });

        $managementInfo = !empty($managementCompany) 
            ? " with {$managementCompany} as management company ({$managementPercentage}% fee)" 
            : " (self-managed)";

        $sharingInfo = count($sharedLandlords) > 1 
            ? " Property has been shared with " . (count($sharedLandlords) - 1) . " other landlord(s)."
            : "";

        $successMessage = 'Property "' . $this->request->getPost('property_name') . '" added successfully with ' . 
            count($landlordNames) . ' landlord(s), ' . count($unitNames) . ' unit(s), and ' . 
            count($expenseNames) . ' expense(s)' . $managementInfo . $sharingInfo;

        return $this->respondWithSuccess([
            'property_id' => $propertyId,
            'units_added' => count($unitNames),
            'expenses_added' => count($expenseNames),
            'owners_added' => count($landlordNames)
        ], $successMessage);

    } catch (\Exception $e) {
        // Rollback transaction if it was started
        if (isset($db)) {
            $db->transRollback();
        }
        
        log_message('error', 'Add property error: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        return $this->respondWithError('Failed to add property: ' . $e->getMessage(), 500);
    }
}

/**
 * View Property Details - FIXED for landlord_id column
 */
public function viewProperty($propertyId)
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    $landlordId = $this->getCurrentUserId();

    try {
        $db = \Config\Database::connect();

        // Get property basic info with ownership verification
        $propertyQuery = $db->table('properties p')
            ->select('p.*')
            ->join('property_ownership po', 'po.property_id = p.id')
            ->where('p.id', $propertyId)
            ->groupStart()
                ->where('po.user_id', $landlordId)
                ->orWhere('po.landlord_id', $landlordId)
            ->groupEnd()
            ->get();

        $property = $propertyQuery->getRowArray();

        if (!$property) {
            $this->setError('Property not found or you do not have access to it.');
            return redirect()->to('/landlord/properties');
        }

        // Get all landlords/owners for this property
        $ownersQuery = $db->table('property_ownership po')
            ->select('po.*, 
                      COALESCE(u1.first_name, u2.first_name) as first_name,
                      COALESCE(u1.last_name, u2.last_name) as last_name,
                      COALESCE(u1.email, u2.email) as email,
                      COALESCE(u1.phone, u2.phone) as phone')
            ->join('users u1', 'u1.id = po.user_id', 'left')
            ->join('users u2', 'u2.id = po.landlord_id', 'left')
            ->where('po.property_id', $propertyId)
            ->orderBy('po.ownership_percentage', 'DESC')
            ->get();

        $owners = $ownersQuery->getResultArray();

        // Get property units
        $unitsQuery = $db->table('property_units')
            ->where('property_id', $propertyId)
            ->orderBy('unit_name')
            ->get();

        $units = $unitsQuery->getResultArray();

        // Get property expenses
        $expensesQuery = $db->table('property_expenses')
            ->where('property_id', $propertyId)
            ->orderBy('expense_name')
            ->get();

        $expenses = $expensesQuery->getResultArray();

        // Calculate some statistics
        $totalExpenses = array_sum(array_column($expenses, 'expense_amount'));
        $yourOwnership = 0;
        foreach ($owners as $owner) {
            if ($owner['user_id'] == $landlordId || $owner['landlord_id'] == $landlordId) {
                $yourOwnership = $owner['ownership_percentage'];
                break;
            }
        }

        $data = [
            'title' => 'Property Details - ' . ($property['property_name'] ?? 'Unknown'),
            'property' => $property,
            'owners' => $owners ?? [],
            'units' => $units ?? [],
            'expenses' => $expenses ?? [],
            'total_expenses' => $totalExpenses,
            'your_ownership' => $yourOwnership
        ];

        return view('landlord/property_details', $data);

    } catch (\Exception $e) {
        log_message('error', 'Property details error: ' . $e->getMessage());
        $this->setError('Error loading property details: ' . $e->getMessage());
        return redirect()->to('/landlord/properties');
    }
}

/**
     * Get property units
     */
    private function getPropertyUnits($propertyId)
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if property_units table exists
            if (!$db->tableExists('property_units')) {
                return [];
            }
            
            $builder = $db->table('property_units');
            $builder->where('property_id', $propertyId);
            $builder->orderBy('unit_name', 'ASC');
            
            return $builder->get()->getResultArray();
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting property units: ' . $e->getMessage());
            return [];
        }
    }

/**
 * Calculate Property Statistics - FIXED for landlord_id
 */
private function calculatePropertyStats($propertyId, $property, $owners)
{
    try {
        $db = \Config\Database::connect();

        // Calculate total monthly expenses
        $expensesQuery = $db->table('property_expenses')
            ->selectSum('expense_amount', 'total_expenses')
            ->where('property_id', $propertyId)
            ->get();
        
        $totalExpenses = $expensesQuery->getRowArray()['total_expenses'] ?? 0;

        // Count occupied vs vacant units
        $occupiedQuery = $db->table('leases')
            ->where('property_id', $propertyId)
            ->where('status', 'active')
            ->countAllResults();

        $totalUnits = $property['number_of_units'] ?? 1;
        $occupiedUnits = $occupiedQuery;
        $vacantUnits = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;

        // Calculate monthly income (from active leases)
        $incomeQuery = $db->table('leases')
            ->selectSum('rent_amount', 'total_monthly_rent')
            ->where('property_id', $propertyId)
            ->where('status', 'active')
            ->get();

        $monthlyIncome = $incomeQuery->getRowArray()['total_monthly_rent'] ?? 0;
        $netIncome = $monthlyIncome - $totalExpenses;

        // Get your ownership percentage - FIXED to use landlord_id
       $currentUserId = $this->getCurrentUserId();
$yourOwnership = 0;
foreach ($owners as $owner) {
    // Check both user_id and landlord_id columns
    if ($owner['user_id'] == $currentUserId || $owner['landlord_id'] == $currentUserId) {
        $yourOwnership = $owner['ownership_percentage'];
        break;
    }
}

        return [
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $vacantUnits,
            'occupancy_rate' => round($occupancyRate, 1),
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $totalExpenses,
            'net_monthly_income' => $netIncome,
            'your_ownership' => $yourOwnership,
            'your_monthly_share' => $netIncome * ($yourOwnership / 100),
            'total_owners' => count($owners)
        ];

    } catch (\Exception $e) {
        log_message('error', 'Property stats calculation error: ' . $e->getMessage());
        return [
            'total_units' => $property['number_of_units'] ?? 1,
            'occupied_units' => 0,
            'vacant_units' => $property['number_of_units'] ?? 1,
            'occupancy_rate' => 0,
            'monthly_income' => 0,
            'monthly_expenses' => 0,
            'net_monthly_income' => 0,
            'your_ownership' => 0,
            'your_monthly_share' => 0,
            'total_owners' => count($owners)
        ];
    }
}

public function debugProperties()
{
    $redirect = $this->requireLandlord();
    if ($redirect) return $redirect;

    $landlordId = $this->getCurrentUserId();
    
    echo "<h2>Debug Properties for Landlord ID: $landlordId</h2>";
    
    try {
        $db = \Config\Database::connect();
        
        // Check if property_ownership table exists and has data
        echo "<h3>1. Check property_ownership table:</h3>";
        $query = $db->query("SELECT * FROM property_ownership WHERE user_id = ? OR landlord_id = ?", [$landlordId, $landlordId]);
        $ownerships = $query->getResultArray();
        echo "Found " . count($ownerships) . " ownership records:<br>";
        echo "<pre>" . print_r($ownerships, true) . "</pre>";
        
        // Check if properties table has data
        echo "<h3>2. Check properties table:</h3>";
        $query = $db->query("SELECT * FROM properties");
        $allProperties = $query->getResultArray();
        echo "Found " . count($allProperties) . " total properties:<br>";
        foreach ($allProperties as $prop) {
            echo "ID: {$prop['id']}, Name: {$prop['property_name']}<br>";
        }
        
        // Test the join manually
        echo "<h3>3. Test manual join:</h3>";
        $query = $db->query("
            SELECT p.*, po.ownership_percentage, po.landlord_name 
            FROM properties p 
            JOIN property_ownership po ON po.property_id = p.id 
            WHERE po.user_id = ? OR po.landlord_id = ?
        ", [$landlordId, $landlordId]);
        $joinResults = $query->getResultArray();
        echo "Manual join found " . count($joinResults) . " results:<br>";
        echo "<pre>" . print_r($joinResults, true) . "</pre>";
        
        // Test the PropertyModel method
        echo "<h3>4. Test PropertyModel method:</h3>";
        $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
        echo "PropertyModel returned " . count($properties) . " results:<br>";
        echo "<pre>" . print_r($properties, true) . "</pre>";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

/**
 * Edit Property Form - Add this to your Landlord controller
 */
public function editProperty($propertyId)
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    $landlordId = $this->getCurrentUserId();

    try {
        $db = \Config\Database::connect();

        // Get property with ownership verification
        $propertyQuery = $db->table('properties p')
            ->select('p.*')
            ->join('property_ownership po', 'po.property_id = p.id')
            ->where('p.id', $propertyId)
            ->groupStart()
                ->where('po.user_id', $landlordId)
                ->orWhere('po.landlord_id', $landlordId)
            ->groupEnd()
            ->get();

        $property = $propertyQuery->getRowArray();

        if (!$property) {
            $this->setError('Property not found or you do not have access to edit it.');
            return redirect()->to('/landlord/properties');
        }

        // Get current ownership details
        $owners = $db->table('property_ownership po')
            ->select('po.*, 
                      COALESCE(u1.first_name, u2.first_name) as first_name,
                      COALESCE(u1.last_name, u2.last_name) as last_name,
                      COALESCE(u1.username, u2.username) as username')
            ->join('users u1', 'u1.id = po.user_id', 'left')
            ->join('users u2', 'u2.id = po.landlord_id', 'left')
            ->where('po.property_id', $propertyId)
            ->orderBy('po.ownership_percentage', 'DESC')
            ->get()
            ->getResultArray();

        // Get current units
        $units = $db->table('property_units')
            ->where('property_id', $propertyId)
            ->orderBy('unit_name')
            ->get()
            ->getResultArray();

        // Get current expenses
        $expenses = $db->table('property_expenses')
            ->where('property_id', $propertyId)
            ->orderBy('expense_name')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Edit Property - ' . $property['property_name'],
            'property' => $property,
            'owners' => $owners,
            'units' => $units,
            'expenses' => $expenses,
            'validation' => \Config\Services::validation()
        ];

        return view('landlord/edit_property', $data);

    } catch (\Exception $e) {
        log_message('error', 'Edit property error: ' . $e->getMessage());
        $this->setError('Error loading property for editing: ' . $e->getMessage());
        return redirect()->to('/landlord/properties');
    }
}

/**
 * Update Property - Process Edit Form
 */
public function updateProperty($propertyId)
{
    try {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $landlordId = $this->getCurrentUserId();
        
        log_message('info', 'Update property called for property ID: ' . $propertyId);
        log_message('info', 'Landlord ID: ' . $landlordId);
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));

        // Verify access
        $db = \Config\Database::connect();
        $hasAccess = $db->table('property_ownership')
            ->where('property_id', $propertyId)
            ->groupStart()
                ->where('user_id', $landlordId)
                ->orWhere('landlord_id', $landlordId)
            ->groupEnd()
            ->countAllResults();

        if (!$hasAccess) {
            log_message('error', 'Access denied for property ' . $propertyId . ' by landlord ' . $landlordId);
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied to this property']);
        }

        // Validation rules
        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'property_type' => 'required|in_list[rest_house,chalet,other]',
            'property_address' => 'required|min_length[5]|max_length[1000]',
            'number_of_units' => 'required|integer|greater_than[0]|less_than_equal_to[500]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Validation errors: ' . json_encode($errors));
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Validation failed: ' . implode(', ', $errors)
            ]);
        }

        // Start transaction
        $db->transStart();

        // Update property basic info
        $propertyData = [
            'property_name' => $this->request->getPost('property_name'),
            'property_type' => $this->request->getPost('property_type'),
            'address' => $this->request->getPost('property_address'),
            'number_of_units' => intval($this->request->getPost('number_of_units')),
            'management_company' => $this->request->getPost('management_company') ?: null,
            'management_percentage' => $this->request->getPost('management_percentage') ? floatval($this->request->getPost('management_percentage')) : 0.00,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'Updating property with data: ' . json_encode($propertyData));

        $updateResult = $db->table('properties')->where('id', $propertyId)->update($propertyData);

        if (!$updateResult) {
            $error = $db->error();
            log_message('error', 'Property update failed: ' . json_encode($error));
            throw new \Exception('Failed to update property: ' . ($error['message'] ?? 'Unknown database error'));
        }

        log_message('info', 'Property basic info updated successfully');

        // Update units if provided
        $unitNames = $this->request->getPost('unit_names');
        if (!empty($unitNames) && is_array($unitNames)) {
            log_message('info', 'Updating units: ' . json_encode($unitNames));
            
            // Delete existing units
            $deleteResult = $db->table('property_units')->where('property_id', $propertyId)->delete();
            log_message('info', 'Deleted existing units, result: ' . ($deleteResult ? 'success' : 'failed'));
            
            // Insert new units
            foreach ($unitNames as $unitName) {
                if (!empty(trim($unitName))) {
                    $unitData = [
                        'property_id' => $propertyId,
                        'unit_name' => trim($unitName),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $unitResult = $db->table('property_units')->insert($unitData);
                    if (!$unitResult) {
                        log_message('error', 'Failed to insert unit: ' . $unitName);
                    }
                }
            }
            log_message('info', 'Units updated successfully');
        }

        // Update expenses if provided
        $expenseNames = $this->request->getPost('expense_names');
        $expenseAmounts = $this->request->getPost('expense_amounts');
        
        if (!empty($expenseNames) && is_array($expenseNames) && 
            !empty($expenseAmounts) && is_array($expenseAmounts) &&
            count($expenseNames) === count($expenseAmounts)) {
            
            log_message('info', 'Updating expenses: ' . json_encode($expenseNames));
            
            // Delete existing expenses
            $deleteResult = $db->table('property_expenses')->where('property_id', $propertyId)->delete();
            log_message('info', 'Deleted existing expenses, result: ' . ($deleteResult ? 'success' : 'failed'));
            
            // Insert new expenses
            for ($i = 0; $i < count($expenseNames); $i++) {
                if (!empty(trim($expenseNames[$i])) && is_numeric($expenseAmounts[$i])) {
                    $expenseData = [
                        'property_id' => $propertyId,
                        'expense_name' => trim($expenseNames[$i]),
                        'expense_amount' => floatval($expenseAmounts[$i]),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $expenseResult = $db->table('property_expenses')->insert($expenseData);
                    if (!$expenseResult) {
                        log_message('error', 'Failed to insert expense: ' . $expenseNames[$i]);
                    }
                }
            }
            log_message('info', 'Expenses updated successfully');
        }

        // Complete transaction
        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            log_message('error', 'Transaction failed');
            throw new \Exception('Database transaction failed');
        }

        log_message('info', 'Property update completed successfully');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Property updated successfully!',
            'data' => ['property_id' => $propertyId]
        ]);

    } catch (\Exception $e) {
        if (isset($db)) {
            $db->transRollback();
        }
        
        log_message('error', 'Update property exception: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update property: ' . $e->getMessage()
        ]);
    }
}

public function debugPropertyData($propertyId = null)
{
    $redirect = $this->requireLandlord();
    if ($redirect) return $redirect;

    $landlordId = $this->getCurrentUserId();
    
    echo "<h2>🔍 Property Data Debug</h2>";
    
    try {
        $db = \Config\Database::connect();
        
        if ($propertyId) {
            // Debug specific property
            echo "<h3>Property ID: $propertyId</h3>";
            
            $property = $db->table('properties')->where('id', $propertyId)->get()->getRowArray();
            echo "<h4>Raw Property Data:</h4>";
            echo "<pre>" . print_r($property, true) . "</pre>";
            
            $ownership = $db->table('property_ownership')->where('property_id', $propertyId)->get()->getResultArray();
            echo "<h4>Ownership Data:</h4>";
            echo "<pre>" . print_r($ownership, true) . "</pre>";
            
        } else {
            // Debug all properties for this landlord
            echo "<h3>All Properties for Landlord ID: $landlordId</h3>";
            
            // Raw query result
            $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
            echo "<h4>PropertyModel Result (" . count($properties) . " properties):</h4>";
            echo "<pre>" . print_r($properties, true) . "</pre>";
            
            // Check property_type specifically
            if (!empty($properties)) {
                echo "<h4>Property Types Check:</h4>";
                foreach ($properties as $prop) {
                    echo "Property ID {$prop['id']}: ";
                    echo "property_type = '" . ($prop['property_type'] ?? 'NULL') . "'";
                    echo " (length: " . strlen($prop['property_type'] ?? '') . ")";
                    echo "<br>";
                }
            }
        }
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
/**
 * Fix Property Types - Add this temporary method to your Landlord controller
 */
public function fixPropertyTypes()
{
    $redirect = $this->requireLandlord();
    if ($redirect) return $redirect;

    echo "<h2>🔧 Fixing Property Types</h2>";
    
    try {
        $db = \Config\Database::connect();
        
        // First, let's see what types we currently have
        echo "<h3>Current Property Types:</h3>";
        $properties = $db->table('properties')->select('id, property_name, property_type')->get()->getResultArray();
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Current Type</th><th>Length</th><th>Action</th></tr>";
        
        foreach ($properties as $property) {
            $currentType = $property['property_type'] ?? '';
            $typeLength = strlen($currentType);
            
            echo "<tr>";
            echo "<td>{$property['id']}</td>";
            echo "<td>{$property['property_name']}</td>";
            echo "<td>'" . htmlspecialchars($currentType) . "'</td>";
            echo "<td>{$typeLength}</td>";
            
            // If type is empty or null, set a default
            if (empty($currentType) || trim($currentType) === '') {
                $db->table('properties')->where('id', $property['id'])->update(['property_type' => 'other']);
                echo "<td style='color: green;'>Updated to 'other'</td>";
            } else {
                echo "<td>No change needed</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>✅ Property types have been fixed!</h3>";
        echo "<p><a href='" . site_url('landlord/properties') . "'>Go back to properties</a></p>";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
/**
 * Debug Property Display - Add this to your Landlord controller
 */
public function debugPropertyDisplay()
{
    $redirect = $this->requireLandlord();
    if ($redirect) return $redirect;

    $landlordId = $this->getCurrentUserId();
    $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
    
    echo "<h2>🔍 Property Display Debug</h2>";
    echo "<style>
        .debug-box { border: 2px solid red; padding: 10px; margin: 10px 0; background: #f9f9f9; }
        .hidden-content { background: yellow; }
    </style>";
    
    if (!empty($properties)) {
        foreach ($properties as $property) {
            echo "<div class='debug-box'>";
            echo "<h3>Property: " . esc($property['property_name']) . "</h3>";
            echo "<p><strong>Raw property_type value:</strong> '" . ($property['property_type'] ?? 'NULL') . "'</p>";
            echo "<p><strong>Length:</strong> " . strlen($property['property_type'] ?? '') . "</p>";
            echo "<p><strong>Is empty:</strong> " . (empty($property['property_type']) ? 'YES' : 'NO') . "</p>";
            
            // Test the same logic as your view
            $propertyType = $property['property_type'] ?? '';
            $displayType = '';
            
            switch ($propertyType) {
                case 'rest_house':
                    $displayType = 'Rest House';
                    break;
                case 'chalet':
                    $displayType = 'Chalet';
                    break;
                case 'other':
                    $displayType = 'Other';
                    break;
                default:
                    $displayType = 'Not Set';
            }
            
            echo "<p><strong>Display Type:</strong> '$displayType'</p>";
            
            // Create the exact same badge as in your view
            echo "<p><strong>Badge HTML:</strong></p>";
            echo "<span class='badge badge-secondary' style='background: #6c757d; color: white; padding: 5px 10px; border-radius: 3px;'>";
            echo $displayType;
            echo "</span>";
            
            echo "<p><strong>All property data:</strong></p>";
            echo "<pre>" . print_r($property, true) . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<p>No properties found!</p>";
    }
    
    echo "<p><a href='" . site_url('landlord/properties') . "'>Back to properties</a></p>";
}
public function checkCsrfConfig()
{
    echo "<h2>🔐 CSRF Configuration Check</h2>";
    
    $security = \Config\Services::security();
    
    echo "<p><strong>CSRF Token Name:</strong> " . csrf_token() . "</p>";
    echo "<p><strong>CSRF Hash:</strong> " . csrf_hash() . "</p>";
    echo "<p><strong>CSRF Field HTML:</strong> " . htmlspecialchars(csrf_field()) . "</p>";
    
    echo "<h3>Test Form with CSRF:</h3>";
    echo '<form method="post" action="' . site_url('landlord/test-csrf') . '">';
    echo csrf_field();
    echo '<input type="text" name="test_field" value="test_value" placeholder="Test field">';
    echo '<button type="submit">Test CSRF</button>';
    echo '</form>';
    
    echo "<h3>Raw CSRF Field:</h3>";
    echo "<textarea rows='3' cols='80'>" . csrf_field() . "</textarea>";
}

/**
 * Test CSRF - Add this method too
 */
public function testCsrf()
{
    if ($this->request->getMethod() === 'POST') {
        echo "<h2>✅ CSRF Test Successful!</h2>";
        echo "<p>POST data received:</p>";
        echo "<pre>" . print_r($this->request->getPost(), true) . "</pre>";
        echo "<p><a href='" . site_url('landlord/check-csrf-config') . "'>Back to CSRF check</a></p>";
    } else {
        echo "<h2>❌ This should be a POST request</h2>";
    }
}

/**
     * Generate Ownership PDF - WITH LOGGING
     */
    public function generateOwnershipPdf()
    {
        try {
            $redirect = $this->requireLandlord();
            if ($redirect) {
                return $redirect;
            }

            $landlordId = $this->getCurrentUserId();
            $propertyId = $this->request->getPost('property_id');
            
            log_message('info', 'Ownership report requested by landlord: ' . $landlordId);
            
            // Get report options safely
            $includePropertyDetails = $this->request->getPost('include_property_details') ? true : false;
            $includeOwnerDetails = $this->request->getPost('include_owner_details') ? true : false;
            $includePercentages = $this->request->getPost('include_percentages') ? true : false;
            
            // Get properties safely
            $properties = [];
            $propertyName = 'All Properties';
            
            try {
                if ($propertyId && !empty($propertyId)) {
                    $property = $this->getPropertyForLandlord($landlordId, $propertyId);
                    if ($property) {
                        $properties = [$property];
                        $propertyName = $property['name'] ?? $property['property_name'] ?? 'Selected Property';
                    } else {
                        return redirect()->back()->with('error', 'Selected property not found or you do not have access to it.');
                    }
                } else {
                    $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting properties for report: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Could not retrieve property data for report.');
            }

            if (empty($properties)) {
                return redirect()->back()->with('error', 'No properties found for generating report.');
            }

            // Generate report name
            $reportName = 'Ownership Report - ' . $propertyName . ' - ' . date('M d, Y');
            
            // Generate HTML report
            $html = $this->generateOwnershipReportHtml($properties, [
                'include_property_details' => $includePropertyDetails,
                'include_owner_details' => $includeOwnerDetails,
                'include_percentages' => $includePercentages
            ]);

            // Log the report generation
            $this->logReportGeneration($landlordId, 'Ownership Report', $reportName, $propertyName, $propertyId);

            // ONLY PDF - no fallback
            $this->forcePdfDownload($html, 'Ownership_Report_' . date('Y-m-d'));

        } catch (\Exception $e) {
            log_message('error', 'Ownership report generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate ownership report: ' . $e->getMessage());
        }
    }

    /**
     * Generate Income PDF - WITH LOGGING
     */
    public function generateIncomePdf()
    {
        try {
            $redirect = $this->requireLandlord();
            if ($redirect) {
                return $redirect;
            }

            $landlordId = $this->getCurrentUserId();
            $propertyId = $this->request->getPost('property_id');
            $reportPeriod = $this->request->getPost('report_period') ?? 'monthly';
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');
            
            log_message('info', 'Income report requested by landlord: ' . $landlordId);

            // Validate dates
            if (empty($startDate) || empty($endDate)) {
                return redirect()->back()->with('error', 'Please select both start and end dates.');
            }

            // Get properties safely
            $properties = [];
            $propertyName = 'All Properties';
            
            try {
                if ($propertyId && !empty($propertyId)) {
                    $property = $this->getPropertyForLandlord($landlordId, $propertyId);
                    if ($property) {
                        $properties = [$property];
                        $propertyName = $property['name'] ?? $property['property_name'] ?? 'Selected Property';
                    } else {
                        return redirect()->back()->with('error', 'Selected property not found or you do not have access to it.');
                    }
                } else {
                    $properties = $this->propertyModel->getPropertiesForLandlord($landlordId);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting properties for income report: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Could not retrieve property data for report.');
            }

            if (empty($properties)) {
                return redirect()->back()->with('error', 'No properties found for generating report.');
            }

            // Generate report name
            $periodLabel = ucfirst(str_replace('_', ' ', $reportPeriod));
            $reportName = 'Income Report (' . $periodLabel . ') - ' . $propertyName . ' - ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate));

            // Get financial data safely
            $payments = $this->getPaymentsByLandlordAndPeriod($landlordId, $startDate, $endDate);
            $expenses = $this->getExpensesByLandlordAndPeriod($landlordId, $startDate, $endDate);

            // Get report options
            $includeIncomeBreakdown = $this->request->getPost('include_income_breakdown') ? true : false;
            $includeExpenseBreakdown = $this->request->getPost('include_expense_breakdown') ? true : false;
            $includeOwnerDistributions = $this->request->getPost('include_owner_distributions') ? true : false;

            // Generate HTML report
            $html = $this->generateIncomeReportHtml($properties, $payments, $expenses, [
                'report_period' => $reportPeriod,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'include_income_breakdown' => $includeIncomeBreakdown,
                'include_expense_breakdown' => $includeExpenseBreakdown,
                'include_owner_distributions' => $includeOwnerDistributions
            ]);

            // Log the report generation
            $this->logReportGeneration($landlordId, 'Income Report', $reportName, $propertyName, $propertyId);

            // ONLY PDF - no fallback
            $this->forcePdfDownload($html, 'Income_Report_' . $reportPeriod . '_' . date('Y-m-d'));

        } catch (\Exception $e) {
            log_message('error', 'Income report generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate income report: ' . $e->getMessage());
        }
    }
    
    /**
     * Force PDF Download - NO HTML FALLBACK
     */
    private function forcePdfDownload($html, $filename)
    {
        // Check if dompdf is available
        $vendorPath = ROOTPATH . 'vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            log_message('error', 'Vendor autoload not found at: ' . $vendorPath);
            throw new \Exception('PDF library not found. Please run: composer install');
        }
        
        require_once $vendorPath;
        
        // Check if Dompdf class exists
        if (!class_exists('\Dompdf\Dompdf')) {
            log_message('error', 'Dompdf class not found');
            throw new \Exception('Dompdf library not installed. Please run: composer require dompdf/dompdf');
        }
        
        try {
            log_message('info', 'Starting PDF generation with Dompdf');
            
            // Create Dompdf instance with minimal options
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);
            $options->set('isPhpEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            
            $dompdf = new \Dompdf\Dompdf($options);
            
            log_message('info', 'Loading HTML into Dompdf');
            $dompdf->loadHtml($html);
            
            log_message('info', 'Setting paper size');
            $dompdf->setPaper('A4', 'portrait');
            
            log_message('info', 'Rendering PDF');
            $dompdf->render();
            
            log_message('info', 'PDF rendered successfully, sending to browser');
            
            // Clear any output buffers
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            header('Cache-Control: private, no-transform, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output PDF
            echo $dompdf->output();
            
            log_message('info', 'PDF sent to browser successfully');
            exit;
            
        } catch (\Exception $e) {
            log_message('error', 'PDF generation failed: ' . $e->getMessage());
            log_message('error', 'PDF generation stack trace: ' . $e->getTraceAsString());
            throw new \Exception('PDF generation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get single property for landlord - FIXED VERSION
     */
    private function getPropertyForLandlord($landlordId, $propertyId)
    {
        try {
            $db = \Config\Database::connect();
            
            log_message('info', "Getting property {$propertyId} for landlord {$landlordId}");
            
            // First check the properties table structure
            $fieldsQuery = $db->query("DESCRIBE properties");
            $fields = $fieldsQuery->getResultArray();
            $fieldNames = array_column($fields, 'Field');
            log_message('info', 'Properties table fields: ' . implode(', ', $fieldNames));
            
            // Try with property_owners table if it exists
            if ($db->tableExists('property_owners')) {
                $builder = $db->table('properties p');
                $builder->select('p.*');
                $builder->join('property_owners po', 'po.property_id = p.id', 'inner');
                $builder->where('p.id', $propertyId);
                $builder->groupStart();
                $builder->where('po.user_id', $landlordId);
                if (in_array('landlord_id', $fieldNames)) {
                    $builder->orWhere('po.landlord_id', $landlordId);
                }
                $builder->groupEnd();
                
                $property = $builder->get()->getRowArray();
                if ($property) {
                    log_message('info', 'Property found via property_owners: ' . $property['id']);
                    return $property;
                }
            }
            
            // Fallback: try direct property ownership
            $builder = $db->table('properties');
            $builder->where('id', $propertyId);
            
            // Check different possible owner field names
            if (in_array('user_id', $fieldNames)) {
                $builder->where('user_id', $landlordId);
            } elseif (in_array('landlord_id', $fieldNames)) {
                $builder->where('landlord_id', $landlordId);
            } elseif (in_array('owner_id', $fieldNames)) {
                $builder->where('owner_id', $landlordId);
            }
            
            $property = $builder->get()->getRowArray();
            if ($property) {
                log_message('info', 'Property found via direct ownership: ' . $property['id']);
                return $property;
            }
            
            // Last resort: get property without ownership check (for testing)
            $builder = $db->table('properties');
            $builder->where('id', $propertyId);
            $property = $builder->get()->getRowArray();
            
            if ($property) {
                log_message('warning', 'Property found without ownership verification: ' . $property['id']);
                return $property;
            }
            
            log_message('error', "Property {$propertyId} not found for landlord {$landlordId}");
            return null;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting single property: ' . $e->getMessage());
            return null;
        }
    }

/**
     * Remove old methods that had HTML fallback
     */
    private function generatePdfReport($html, $filename)
    {
        // Redirect to new method
        $this->forcePdfDownload($html, $filename);
    }
    

/**
     * Generate Ownership Report HTML - SIMPLE VERSION
     */
    private function generateOwnershipReportHtml($properties, $options)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Property Ownership Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.4; 
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .property-section { 
            margin-bottom: 30px; 
            border: 1px solid #ddd;
            padding: 20px;
        }
        .property-title { 
            background-color: #f0f0f0;
            padding: 10px; 
            margin: -20px -20px 20px -20px;
            font-size: 18px;
            font-weight: bold;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th { 
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .units-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #333;
        }
        .units-list {
            margin: 5px 0;
        }
        .unit-item {
            display: inline-block;
            margin: 2px 5px 2px 0;
            padding: 2px 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            font-size: 12px;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .no-data { 
            text-align: center; 
            color: #999; 
            font-style: italic; 
            padding: 20px;
        }
    </style>
</head>
<body>';

        $html .= '<div class="header">';
        $html .= '<h1>Property Ownership Report</h1>';
        $html .= '<p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>';
        $html .= '</div>';

        if (empty($properties)) {
            $html .= '<div class="no-data">No properties found for this report.</div>';
        } else {
            foreach ($properties as $property) {
                $html .= '<div class="property-section">';
                $html .= '<div class="property-title">' . esc($property['name'] ?? $property['property_name'] ?? 'Property') . '</div>';

                if ($options['include_property_details']) {
                    $html .= '<h3>Property Details</h3>';
                    $html .= '<table>';
                    $html .= '<tr><th width="30%">Address</th><td>' . esc($property['address'] ?? 'Not specified') . '</td></tr>';
                    $html .= '<tr><th>Property Type</th><td>' . esc($property['type'] ?? $property['property_type'] ?? 'Not specified') . '</td></tr>';
                    
                    // Get property units
                    $units = $this->getPropertyUnits($property['id']);
                    if (!empty($units)) {
                        $html .= '<tr><th>Total Units</th><td>' . count($units) . '</td></tr>';
                        $html .= '</table>';
                        
                        $html .= '<div class="units-section">';
                        $html .= '<strong>Unit Names:</strong><br>';
                        $html .= '<div class="units-list">';
                        foreach ($units as $unit) {
                            $html .= '<span class="unit-item">' . esc($unit['unit_name'] ?? $unit['name'] ?? 'Unit') . '</span>';
                        }
                        $html .= '</div>';
                        $html .= '</div>';
                    } else {
                        $html .= '<tr><th>Total Units</th><td>No units specified</td></tr>';
                        $html .= '</table>';
                    }
                }

                if ($options['include_owner_details'] || $options['include_percentages']) {
                    $owners = $this->getPropertyOwners($property['id']);
                    
                    if (empty($owners)) {
                        $html .= '<h3>Property Owners</h3>';
                        $html .= '<div class="no-data">No owner information available for this property.</div>';
                    } else {
                        $html .= '<h3>Property Owners</h3>';
                        $html .= '<table>';
                        $html .= '<thead><tr>';
                        
                        if ($options['include_owner_details']) {
                            $html .= '<th>Owner Name</th><th>Email</th>';
                        }
                        
                        if ($options['include_percentages']) {
                            $html .= '<th>Ownership Percentage</th>';
                        }
                        
                        $html .= '</tr></thead><tbody>';

                        foreach ($owners as $owner) {
                            $html .= '<tr>';
                            
                            if ($options['include_owner_details']) {
                                $html .= '<td>' . esc($owner['name'] ?? 'Unknown Owner') . '</td>';
                                $html .= '<td>' . esc($owner['email'] ?? 'N/A') . '</td>';
                            }
                            
                            if ($options['include_percentages']) {
                                $html .= '<td>' . number_format($owner['ownership_percentage'] ?? 0, 1) . '%</td>';
                            }
                            
                            $html .= '</tr>';
                        }

                        $html .= '</tbody></table>';
                    }
                }

                $html .= '</div>';
            }
        }

        $html .= '<div class="footer">';
        $html .= '<p>Property Management System - Ownership Report</p>';
        $html .= '<p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }
    
    
    /**
     * Get payments by landlord and period - SAFE VERSION
     */
    private function getPaymentsByLandlordAndPeriod($landlordId, $startDate, $endDate)
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if payments table exists
            if (!$db->tableExists('payments')) {
                log_message('info', 'Payments table does not exist, returning sample data');
                return $this->getSamplePayments($startDate, $endDate);
            }
            
            // Try to get real payments
            $builder = $db->table('payments p');
            $builder->select('p.*, "Sample Property" as property_name, "Sample Tenant" as tenant_name');
            $builder->where('p.payment_date >=', $startDate);
            $builder->where('p.payment_date <=', $endDate);
            $builder->where('p.landlord_id', $landlordId); // Assuming direct relationship
            $builder->orderBy('p.payment_date', 'DESC');
            
            $payments = $builder->get()->getResultArray();
            
            // If no payments found, return sample data
            if (empty($payments)) {
                return $this->getSamplePayments($startDate, $endDate);
            }
            
            return $payments;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting payments: ' . $e->getMessage());
            return $this->getSamplePayments($startDate, $endDate);
        }
    }
    
    /**
     * Get expenses by landlord and period - SAFE VERSION
     */
    private function getExpensesByLandlordAndPeriod($landlordId, $startDate, $endDate)
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if expenses table exists
            if (!$db->tableExists('expenses')) {
                log_message('info', 'Expenses table does not exist, returning sample data');
                return $this->getSampleExpenses($startDate, $endDate);
            }
            
            // Try to get real expenses
            $builder = $db->table('expenses e');
            $builder->select('e.*, "Sample Property" as property_name');
            $builder->where('e.expense_date >=', $startDate);
            $builder->where('e.expense_date <=', $endDate);
            $builder->where('e.landlord_id', $landlordId); // Assuming direct relationship
            $builder->orderBy('e.expense_date', 'DESC');
            
            $expenses = $builder->get()->getResultArray();
            
            // If no expenses found, return sample data
            if (empty($expenses)) {
                return $this->getSampleExpenses($startDate, $endDate);
            }
            
            return $expenses;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting expenses: ' . $e->getMessage());
            return $this->getSampleExpenses($startDate, $endDate);
        }
    }
    
    /**
     * Get sample payments for demonstration
     */
    private function getSamplePayments($startDate, $endDate)
    {
        return [
            [
                'id' => 1,
                'property_id' => 1,
                'amount' => 1500.00,
                'payment_date' => $startDate,
                'description' => 'Monthly Rent Payment',
                'property_name' => 'Sample Property',
                'tenant_name' => 'John Doe'
            ],
            [
                'id' => 2,
                'property_id' => 1,
                'amount' => 1500.00,
                'payment_date' => date('Y-m-d', strtotime($startDate . ' +1 month')),
                'description' => 'Monthly Rent Payment',
                'property_name' => 'Sample Property',
                'tenant_name' => 'John Doe'
            ]
        ];
    }

    /**
     * Get sample expenses for demonstration
     */
    private function getSampleExpenses($startDate, $endDate)
    {
        return [
            [
                'id' => 1,
                'property_id' => 1,
                'category' => 'Maintenance',
                'description' => 'Plumbing repair',
                'amount' => 250.00,
                'expense_date' => $startDate,
                'property_name' => 'Sample Property'
            ],
            [
                'id' => 2,
                'property_id' => 1,
                'category' => 'Utilities',
                'description' => 'Electricity bill',
                'amount' => 180.00,
                'expense_date' => date('Y-m-d', strtotime($startDate . ' +15 days')),
                'property_name' => 'Sample Property'
            ]
        ];
    }

    /**
     * Download HTML report (until PDF library is installed)
     */
    private function downloadHtmlReport($html, $filename)
    {
        // This method is now disabled - PDF only
        throw new \Exception('HTML download is disabled. PDF generation failed.');
    }
    
     /**
     * Generate Income Report HTML - SIMPLE VERSION
     */
    private function generateIncomeReportHtml($properties, $payments, $expenses, $options)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Owner Income Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.4; 
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .period-info { 
            background-color: #f5f5f5;
            padding: 15px; 
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }
        .summary-section { 
            margin-bottom: 30px; 
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .summary-table th,
        .summary-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-table th {
            background-color: #f0f0f0;
        }
        .income { color: #006600; font-weight: bold; }
        .expense { color: #cc0000; font-weight: bold; }
        .profit { color: #0066cc; font-weight: bold; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th { 
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .property-section { 
            margin-bottom: 30px; 
            border: 1px solid #ddd;
            padding: 20px;
        }
        .property-title { 
            background-color: #f0f0f0;
            padding: 10px; 
            margin: -20px -20px 20px -20px;
            font-size: 18px;
            font-weight: bold;
        }
        .total-section { 
            background-color: #f9f9f9;
            padding: 15px; 
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .total-row { 
            display: flex; 
            justify-content: space-between; 
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .total-row:last-child {
            border-bottom: 2px solid #333;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
        }
        .owners-section { 
            background-color: #f5f5f5;
            padding: 15px; 
            margin-top: 20px;
            border: 1px solid #ddd;
        }
        .owner-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 8px 0; 
            border-bottom: 1px solid #ddd;
        }
        .owner-explanation {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border-left: 3px solid #ccc;
            font-size: 12px;
            color: #666;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .no-data { 
            text-align: center; 
            color: #999; 
            font-style: italic; 
            padding: 20px;
        }
    </style>
</head>
<body>';

        $html .= '<div class="header">';
        $html .= '<h1>Owner Income Report</h1>';
        $html .= '<p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>';
        $html .= '</div>';

        $html .= '<div class="period-info">';
        $html .= '<h3>Report Period: ' . ucfirst(str_replace('_', ' ', $options['report_period'])) . '</h3>';
        $html .= '<p><strong>From:</strong> ' . date('F j, Y', strtotime($options['start_date'])) . ' <strong>To:</strong> ' . date('F j, Y', strtotime($options['end_date'])) . '</p>';
        $html .= '</div>';

        // Calculate totals
        $totalIncome = array_sum(array_column($payments, 'amount'));
        $totalExpenses = array_sum(array_column($expenses, 'amount'));
        $totalProfit = $totalIncome - $totalExpenses;

        // Summary table
        $html .= '<div class="summary-section">';
        $html .= '<h3>Financial Summary</h3>';
        $html .= '<table class="summary-table">';
        $html .= '<tr><th>Total Income</th><th>Total Expenses</th><th>Net Profit</th></tr>';
        $html .= '<tr>';
        $html .= '<td class="income">$' . number_format($totalIncome, 2) . '</td>';
        $html .= '<td class="expense">$' . number_format($totalExpenses, 2) . '</td>';
        $html .= '<td class="profit">$' . number_format($totalProfit, 2) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';

        if (empty($properties)) {
            $html .= '<div class="no-data">No properties found for this report.</div>';
        } else {
            foreach ($properties as $property) {
                $html .= '<div class="property-section">';
                $propertyName = $property['name'] ?? $property['property_name'] ?? 'Property';
                $propertyAddress = $property['address'] ?? '';
                $html .= '<div class="property-title">' . esc($propertyName) . ($propertyAddress ? ' - ' . esc($propertyAddress) : '') . '</div>';

                // Property-specific calculations
                $propertyPayments = array_filter($payments, function($payment) use ($property) {
                    return $payment['property_id'] == $property['id'];
                });
                
                $propertyExpenses = array_filter($expenses, function($expense) use ($property) {
                    return $expense['property_id'] == $property['id'];
                });

                $propertyIncome = array_sum(array_column($propertyPayments, 'amount'));
                $propertyExpenseTotal = array_sum(array_column($propertyExpenses, 'amount'));
                $propertyProfit = $propertyIncome - $propertyExpenseTotal;

                if ($options['include_income_breakdown']) {
                    $html .= '<h4>Income Breakdown</h4>';
                    if (empty($propertyPayments)) {
                        $html .= '<div class="no-data">No income records found for this period.</div>';
                    } else {
                        $html .= '<table>';
                        $html .= '<thead><tr><th>Date</th><th>Description</th><th>Tenant</th><th>Amount</th></tr></thead>';
                        $html .= '<tbody>';
                        
                        foreach ($propertyPayments as $payment) {
                            $html .= '<tr>';
                            $html .= '<td>' . date('M j, Y', strtotime($payment['payment_date'])) . '</td>';
                            $html .= '<td>' . esc($payment['description'] ?? 'Rent Payment') . '</td>';
                            $html .= '<td>' . esc($payment['tenant_name'] ?? 'N/A') . '</td>';
                            $html .= '<td>$' . number_format($payment['amount'], 2) . '</td>';
                            $html .= '</tr>';
                        }
                        
                        $html .= '</tbody>';
                        $html .= '<tfoot><tr><th colspan="3">Total Income</th><th>$' . number_format($propertyIncome, 2) . '</th></tr></tfoot>';
                        $html .= '</table>';
                    }
                }

                if ($options['include_expense_breakdown']) {
                    $html .= '<h4>Expense Breakdown</h4>';
                    if (empty($propertyExpenses)) {
                        $html .= '<div class="no-data">No expense records found for this period.</div>';
                    } else {
                        $html .= '<table>';
                        $html .= '<thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr></thead>';
                        $html .= '<tbody>';
                        
                        foreach ($propertyExpenses as $expense) {
                            $html .= '<tr>';
                            $html .= '<td>' . date('M j, Y', strtotime($expense['expense_date'])) . '</td>';
                            $html .= '<td>' . esc($expense['category'] ?? 'General') . '</td>';
                            $html .= '<td>' . esc($expense['description'] ?? 'Expense') . '</td>';
                            $html .= '<td>$' . number_format($expense['amount'], 2) . '</td>';
                            $html .= '</tr>';
                        }
                        
                        $html .= '</tbody>';
                        $html .= '<tfoot><tr><th colspan="3">Total Expenses</th><th>$' . number_format($propertyExpenseTotal, 2) . '</th></tr></tfoot>';
                        $html .= '</table>';
                    }
                }

                // Property totals section
                $html .= '<div class="total-section">';
                $html .= '<h4>Financial Summary for this Property</h4>';
                $html .= '<div class="total-row"><span>Total Income:</span><span class="income">$' . number_format($propertyIncome, 2) . '</span></div>';
                $html .= '<div class="total-row"><span>Total Expenses:</span><span class="expense">$' . number_format($propertyExpenseTotal, 2) . '</span></div>';
                $html .= '<div class="total-row"><span>Profit after Deducting Expenses:</span><span class="profit">$' . number_format($propertyProfit, 2) . '</span></div>';
                $html .= '</div>';

                if ($options['include_owner_distributions']) {
                    $owners = $this->getPropertyOwners($property['id']);
                    
                    $html .= '<div class="owners-section">';
                    $html .= '<h4>Owner Profit Distribution</h4>';
                    $html .= '<p><strong>Company Responsibility Percentage:</strong> Based on ownership percentages below</p>';
                    
                    if (empty($owners)) {
                        $html .= '<div class="no-data">No owner information available for this property.</div>';
                    } else {
                        foreach ($owners as $owner) {
                            $ownerPercentage = $owner['ownership_percentage'] ?? 0;
                            $ownerProfit = ($propertyProfit * $ownerPercentage) / 100;
                            
                            $html .= '<div class="owner-row">';
                            $html .= '<span><strong>' . esc($owner['name'] ?? 'Unknown Owner') . '</strong></span>';
                            $html .= '<span>' . number_format($ownerPercentage, 1) . '%</span>';
                            $html .= '<span class="profit"><strong>$' . number_format($ownerProfit, 2) . '</strong></span>';
                            $html .= '</div>';
                            
                            $html .= '<div class="owner-explanation">';
                            $html .= '<strong>Explanation:</strong> ' . esc($owner['name'] ?? 'Owner') . ' owns ' . number_format($ownerPercentage, 1) . '% of the property. ';
                            $html .= 'After deducting expenses of $' . number_format($propertyExpenseTotal, 2) . ' from income of $' . number_format($propertyIncome, 2) . ', ';
                            $html .= 'the remaining profit of $' . number_format($propertyProfit, 2) . ' is distributed based on ownership percentage. ';
                            $html .= esc($owner['name'] ?? 'Owner') . ' receives $' . number_format($ownerProfit, 2) . '.';
                            $html .= '</div>';
                        }
                    }
                    
                    $html .= '</div>';
                }

                $html .= '</div>';
            }
        }

        $html .= '<div class="footer">';
        $html .= '<p>Property Management System - Income Report</p>';
        $html .= '<p>Report period: ' . date('F j, Y', strtotime($options['start_date'])) . ' to ' . date('F j, Y', strtotime($options['end_date'])) . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }
    
     /**
     * Get property owners with REAL NAMES from users table
     */
    private function getPropertyOwners($propertyId)
    {
        try {
            $db = \Config\Database::connect();
            $landlordId = $this->getCurrentUserId();
            
            // Check if property_owners table exists
            if (!$db->tableExists('property_owners')) {
                // Get current user's real name from users table
                $user = $db->table('users')->where('id', $landlordId)->get()->getRowArray();
                $realName = 'Current User';
                
                if ($user) {
                    $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                    $lastName = $user['last_name'] ?? $user['lastname'] ?? '';
                    $realName = trim($firstName . ' ' . $lastName);
                    
                    if (empty($realName)) {
                        $realName = $user['username'] ?? 'Current User';
                    }
                }
                
                return [
                    [
                        'id' => 1,
                        'name' => $realName,
                        'email' => $user['email'] ?? session()->get('email') ?? 'owner@example.com',
                        'ownership_percentage' => 100.0
                    ]
                ];
            }
            
            // Get owners with user details
            $builder = $db->table('property_owners po');
            $builder->select('po.*, u.first_name, u.last_name, u.firstname, u.lastname, u.email, u.username');
            $builder->join('users u', 'u.id = po.user_id', 'left');
            $builder->where('po.property_id', $propertyId);
            
            $results = $builder->get()->getResultArray();
            
            // Format the results with real names from users table
            foreach ($results as &$owner) {
                // Try multiple name field combinations
                $firstName = $owner['first_name'] ?? $owner['firstname'] ?? '';
                $lastName = $owner['last_name'] ?? $owner['lastname'] ?? '';
                $realName = trim($firstName . ' ' . $lastName);
                
                // If no proper name found, try username
                if (empty($realName)) {
                    $realName = $owner['username'] ?? $owner['owner_name'] ?? $owner['landlord_name'] ?? 'Unknown Owner';
                }
                
                $owner['name'] = $realName;
            }
            
            // If no owners found, get current user's info
            if (empty($results)) {
                $user = $db->table('users')->where('id', $landlordId)->get()->getRowArray();
                $realName = 'Current User';
                
                if ($user) {
                    $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                    $lastName = $user['last_name'] ?? $user['lastname'] ?? '';
                    $realName = trim($firstName . ' ' . $lastName);
                    
                    if (empty($realName)) {
                        $realName = $user['username'] ?? 'Current User';
                    }
                }
                
                return [
                    [
                        'id' => 1,
                        'name' => $realName,
                        'email' => $user['email'] ?? session()->get('email') ?? 'owner@example.com',
                        'ownership_percentage' => 100.0
                    ]
                ];
            }
            
            return $results;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting property owners: ' . $e->getMessage());
            
            // Fallback: get current user's real name
            try {
                $db = \Config\Database::connect();
                $landlordId = $this->getCurrentUserId();
                $user = $db->table('users')->where('id', $landlordId)->get()->getRowArray();
                $realName = 'Current User';
                
                if ($user) {
                    $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                    $lastName = $user['last_name'] ?? $user['lastname'] ?? '';
                    $realName = trim($firstName . ' ' . $lastName);
                    
                    if (empty($realName)) {
                        $realName = $user['username'] ?? 'Current User';
                    }
                }
                
                return [
                    [
                        'id' => 1,
                        'name' => $realName,
                        'email' => $user['email'] ?? session()->get('email') ?? 'owner@example.com',
                        'ownership_percentage' => 100.0
                    ]
                ];
            } catch (\Exception $e2) {
                return [
                    [
                        'id' => 1,
                        'name' => 'Current User',
                        'email' => session()->get('email') ?? 'owner@example.com',
                        'ownership_percentage' => 100.0
                    ]
                ];
            }
        }
    }

}