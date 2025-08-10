<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;

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
        if ($redirect) return $redirect;

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
        if ($redirect) return $redirect;

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
        if ($redirect) return $redirect;

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
        if ($redirect) return $redirect;

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
        if ($redirect) return $redirect;

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
        if ($redirect) return $redirect;

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
     * Profile Management
     */
    public function profile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

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
            'stats' => $stats,
            'login_history' => [] // You can implement this later
        ];

        return view('landlord/profile', $data);
    }

    /**
     * Update Profile
     */
    public function updateProfile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'phone' => 'max_length[20]',
            'address' => 'max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError('Validation failed', 400);
        }

        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code'),
            'bio' => $this->request->getPost('bio'),
            'business_name' => $this->request->getPost('business_name'),
            'business_type' => $this->request->getPost('business_type'),
            'tax_id' => $this->request->getPost('tax_id'),
            'license_number' => $this->request->getPost('license_number')
        ];

        // Remove empty fields
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null && $value !== '';
        });

        if ($this->userModel->update($userId, $updateData)) {
            return $this->respondWithSuccess([], 'Profile updated successfully');
        } else {
            return $this->respondWithError('Failed to update profile');
        }
    }

    /**
     * Get Property Request Details
     */
    public function getPropertyRequestDetails($requestId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

        $landlordId = $this->getCurrentUserId();

        $db = \Config\Database::connect();
        $request = $db->table('property_requests')
            ->where('id', $requestId)
            ->where('landlord_id', $landlordId)
            ->get()
            ->getRowArray();

        if (!$request) {
            return $this->respondWithError('Request not found');
        }

        if ($this->request->isAJAX()) {
            $data = ['request' => $request];
            return view('landlord/property_request_details_modal', $data);
        }

        return $this->respondWithError('Invalid request');
    }

    /**
     * Request New Property (shows form to contact admin)
     */
    public function requestProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

        $landlordId = $this->getCurrentUserId();

        // Get previous requests
        $db = \Config\Database::connect();
        $previous_requests = $db->table('property_requests')
            ->where('landlord_id', $landlordId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Request New Property',
            'previous_requests' => $previous_requests
        ];

        return view('landlord/request_property', $data);
    }

    /**
     * Submit Property Request
     */
    public function submitPropertyRequest()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

        // Updated validation rules with more reasonable lengths
        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'property_address' => 'required|min_length[5]|max_length[500]', 
            'ownership_percentage' => 'required|decimal|greater_than[0]|less_than_equal_to[100]',
            'property_type' => 'max_length[50]',
            'bedrooms' => 'permit_empty|integer|greater_than_equal_to[0]',
            'bathrooms' => 'permit_empty|integer|greater_than_equal_to[1]',
            'square_feet' => 'permit_empty|integer|greater_than_equal_to[0]',
            'estimated_rent' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'property_description' => 'max_length[1000]',
            'message' => 'max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Validation failed: ' . json_encode($errors));
            return $this->respondWithError('Validation failed: ' . implode(', ', $errors), 400);
        }

        $landlordId = $this->getCurrentUserId();

        // Insert property request with ALL form fields
        $db = \Config\Database::connect();
        $requestData = [
            'landlord_id' => $landlordId,
            'property_name' => $this->request->getPost('property_name'),
            'property_address' => $this->request->getPost('property_address'),
            'property_type' => $this->request->getPost('property_type'),
            'bedrooms' => $this->request->getPost('bedrooms') ?: null,
            'bathrooms' => $this->request->getPost('bathrooms') ?: null,
            'square_feet' => $this->request->getPost('square_feet') ?: null,
            'estimated_rent' => $this->request->getPost('estimated_rent') ?: null,
            'ownership_percentage' => $this->request->getPost('ownership_percentage'),
            'property_description' => $this->request->getPost('property_description'),
            'message' => $this->request->getPost('message'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Remove empty values to avoid database issues
        $requestData = array_filter($requestData, function ($value) {
            return $value !== null && $value !== '';
        });

        if ($db->table('property_requests')->insert($requestData)) {
            return $this->respondWithSuccess([], 'Property request submitted successfully');
        } else {
            $error = $db->error();
            log_message('error', 'Database insert failed: ' . json_encode($error));
            return $this->respondWithError('Failed to submit property request: ' . $error['message'], 500);
        }
    }

    // Separate method for file handling
    /*private function handleFileUploads($requestId, $db)
    {
        $uploadedImageCount = 0;

        try {
            $files = $this->request->getFiles();

            if (!isset($files['supporting_documents'])) {
                return $uploadedImageCount;
            }

            // Create upload directory
            $imageUploadPath = WRITEPATH . 'uploads/property_images/';
            if (!is_dir($imageUploadPath)) {
                if (!mkdir($imageUploadPath, 0755, true)) {
                    throw new \Exception('Failed to create upload directory');
                }
            }

            foreach ($files['supporting_documents'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $mimeType = $file->getMimeType();

                    // Only handle images for now
                    if (strpos($mimeType, 'image/') === 0) {
                        $newName = $file->getRandomName();

                        if ($file->move($imageUploadPath, $newName)) {
                            $imagePath = 'property_images/' . $newName;

                            // Insert into property_images table
                            $imageData = [
                                'property_request_id' => $requestId,
                                'image_path' => $imagePath,
                                'alt_text' => 'Property Request Image',
                                'sort_order' => $uploadedImageCount + 1,
                                'is_primary' => $uploadedImageCount === 0 ? 1 : 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];

                            if ($db->table('property_images')->insert($imageData)) {
                                $uploadedImageCount++;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            // Don't throw - just log and continue
        }

        return $uploadedImageCount;
    }

    // Fallback method if property_images doesn't exist
    private function saveRequestWithoutImages($db, $landlordId)
    {
        $requestData = [
            'landlord_id' => $landlordId,
            'property_name' => $this->request->getPost('property_name'),
            'property_address' => $this->request->getPost('property_address'),
            'ownership_percentage' => $this->request->getPost('ownership_percentage'),
            'message' => $this->request->getPost('message'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($db->table('property_requests')->insert($requestData)) {
            return $this->respondWithSuccess([], 'Property request submitted successfully (images will be handled later)');
        } else {
            return $this->respondWithError('Failed to submit property request');
        }
    }*/

    // ... (keeping all your existing methods)

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
        if (count($properties) === 0) return 0;

        $occupiedCount = count(array_filter($properties, function ($p) {
            return isset($p['lease_status']) && $p['lease_status'] === 'active';
        }));

        return ($occupiedCount / count($properties)) * 100;
    }

    // ... (keep all your existing private methods like getLandlordStats, verifyPropertyOwnership, etc.)

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

    public function sendAdminMessage()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) return $redirect;

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
}
