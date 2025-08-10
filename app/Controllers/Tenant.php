<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;
use App\Controllers\BaseController;

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
     * Tenant Dashboard
     */
    public function dashboard()
    {
        // Check tenant access
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        // Get tenant's current lease
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);

        // Get recent payments
        $recentPayments = $this->paymentModel->getPaymentsByTenant($tenantId, 5);

        // Get recent maintenance requests
        $maintenanceRequests = $this->maintenanceModel->getRequestsByTenant($tenantId, 5);

        // Get next payment due
        $nextPayment = $this->getNextPaymentDue($tenantId);

        // Calculate dashboard statistics
        $stats = $this->getTenantStats($tenantId);

        $data = [
            'title' => 'Tenant Dashboard',
            'lease' => $lease,
            'recent_payments' => $recentPayments,
            'maintenance_requests' => $maintenanceRequests,
            'next_payment' => $nextPayment,
            'stats' => $stats
        ];

        return view('tenant/dashboard', $data);
    }

    /**
     * View Payments
     */
    public function payments()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();
        $payments = $this->paymentModel->getPaymentsByTenant($tenantId);

        // Filter by status if requested
        $status = $this->request->getGet('status');
        if ($status) {
            $payments = array_filter($payments, function ($payment) use ($status) {
                return $payment['status'] == $status;
            });
        }

        $data = [
            'title' => 'Payment History',
            'payments' => array_values($payments),
            'current_status' => $status
        ];

        return view('tenant/payments', $data);
    }

    /**
     * Make Payment
     */
    public function makePayment()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        // Get pending payments
        $pendingPayments = $this->paymentModel->getPaymentsByTenant($tenantId);
        $pendingPayments = array_filter($pendingPayments, function ($payment) {
            return in_array($payment['status'], ['pending', 'overdue']);
        });

        // Get tenant's lease for rent amount
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);

        $data = [
            'title' => 'Make Payment',
            'pending_payments' => array_values($pendingPayments),
            'lease' => $lease
        ];

        return view('tenant/make_payment', $data);
    }

    /**
     * Process Payment
     */
    public function processPayment()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $rules = [
            'payment_id' => 'required|integer',
            'payment_method' => 'required|in_list[credit_card,bank_transfer,paypal]',
            'amount' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $tenantId = $this->getCurrentUserId();
        $paymentId = $this->request->getPost('payment_id');
        $paymentMethod = $this->request->getPost('payment_method');
        $amount = $this->request->getPost('amount');

        // Verify payment belongs to tenant
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment['tenant_id'] != $tenantId) {
            $this->setError('Payment not found or access denied');
            return redirect()->to('/tenant/payments');
        }

        // Verify amount matches
        if ($payment['amount'] != $amount) {
            $this->setError('Payment amount does not match');
            return redirect()->back()->withInput();
        }

        // Process payment based on method
        $result = $this->processPaymentMethod($paymentMethod, $amount, $payment);

        if ($result['success']) {
            // Update payment status
            $updateData = [
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'transaction_id' => $result['transaction_id'],
                'payment_date' => date('Y-m-d')
            ];

            if ($this->paymentModel->update($paymentId, $updateData)) {
                $this->setSuccess('Payment processed successfully! Transaction ID: ' . $result['transaction_id']);
                return redirect()->to('/tenant/payments');
            } else {
                $this->setError('Payment processed but failed to update record. Please contact support.');
                return redirect()->to('/tenant/payments');
            }
        } else {
            $this->setError('Payment failed: ' . $result['message']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Payment Receipt
     */
    public function paymentReceipt($paymentId)
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        // Get payment details
        $payments = $this->paymentModel->getPaymentsByTenant($tenantId);
        $payment = null;

        foreach ($payments as $p) {
            if ($p['id'] == $paymentId) {
                $payment = $p;
                break;
            }
        }

        if (!$payment) {
            $this->setError('Payment receipt not found');
            return redirect()->to('/tenant/payments');
        }

        $data = [
            'title' => 'Payment Receipt',
            'payment' => $payment
        ];

        return view('tenant/payment_receipt', $data);
    }

    /**
     * View Maintenance Requests
     */
    public function maintenance()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();
        $requests = $this->maintenanceModel->getRequestsByTenant($tenantId);

        $data = [
            'title' => 'Maintenance Requests',
            'requests' => $requests
        ];

        return view('tenant/maintenance', $data);
    }

    /**
     * Create Maintenance Request
     */
    public function createMaintenance()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        // Get tenant's lease info
        $tenantId = $this->getCurrentUserId();
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);

        if (!$lease) {
            $this->setError('No active lease found. Cannot submit maintenance request.');
            return redirect()->to('/tenant/dashboard');
        }

        $data = [
            'title' => 'Submit Maintenance Request',
            'lease' => $lease,
            'validation' => \Config\Services::validation()
        ];

        return view('tenant/create_maintenance', $data);
    }

    /**
     * Store Maintenance Request
     */
    public function storeMaintenance()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $rules = [
            'title' => 'required|min_length[5]|max_length[200]',
            'description' => 'required|min_length[10]',
            'priority' => 'required|in_list[low,normal,high,urgent]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $tenantId = $this->getCurrentUserId();
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);

        if (!$lease) {
            $this->setError('No active lease found');
            return redirect()->to('/tenant/dashboard');
        }

        $requestData = [
            'tenant_id' => $tenantId,
            'property_id' => $lease['property_id'],
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority' => $this->request->getPost('priority'),
            'status' => 'pending'
        ];

        if ($requestId = $this->maintenanceModel->insert($requestData)) {
            // Handle image uploads if any
            $images = $this->request->getFiles();
            if (isset($images['images'])) {
                foreach ($images['images'] as $image) {
                    if ($image->isValid() && !$image->hasMoved()) {
                        try {
                            $imagePath = $this->handleFileUpload('images', 'maintenance/', ['jpg', 'jpeg', 'png']);
                            if ($imagePath) {
                                $db = \Config\Database::connect();
                                $db->table('maintenance_images')->insert([
                                    'maintenance_request_id' => $requestId,
                                    'image_path' => $imagePath,
                                    'image_type' => 'issue'
                                ]);
                            }
                        } catch (\Exception $e) {
                            // Continue without image if upload fails
                        }
                    }
                }
            }

            $this->setSuccess('Maintenance request submitted successfully');
            return redirect()->to('/tenant/maintenance');
        } else {
            $this->setError('Failed to submit maintenance request');
            return redirect()->back()->withInput();
        }
    }

    /**
     * View Maintenance Request
     */
    public function viewMaintenance($requestId)
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();
        $requests = $this->maintenanceModel->getRequestsByTenant($tenantId);

        $request = null;
        foreach ($requests as $r) {
            if ($r['id'] == $requestId) {
                $request = $r;
                break;
            }
        }

        if (!$request) {
            $this->setError('Maintenance request not found');
            return redirect()->to('/tenant/maintenance');
        }

        // Get images for this request
        $db = \Config\Database::connect();
        $images = $db->table('maintenance_images')
            ->where('maintenance_request_id', $requestId)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Maintenance Request Details',
            'request' => $request,
            'images' => $images
        ];

        return view('tenant/maintenance_view', $data);
    }

    /**
     * View Messages
     */
    public function messages()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        $db = \Config\Database::connect();
        $messages = $db->table('messages m')
            ->select('m.*, u.first_name as sender_first_name, u.last_name as sender_last_name')
            ->join('users u', 'u.id = m.sender_id')
            ->where('m.receiver_id', $tenantId)
            ->orderBy('m.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Messages',
            'messages' => $messages
        ];

        return view('tenant/messages', $data);
    }

    /**
     * Send Message
     */
    public function sendMessage()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $rules = [
            'subject' => 'required|min_length[3]|max_length[200]',
            'message' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError('Validation failed', 400);
        }

        $tenantId = $this->getCurrentUserId();

        // Get landlord from current lease
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);
        if (!$lease) {
            return $this->respondWithError('No active lease found', 400);
        }

        // Get property landlord
        $db = \Config\Database::connect();
        $landlord = $db->table('property_ownership po')
            ->join('users u', 'u.id = po.landlord_id')
            ->where('po.property_id', $lease['property_id'])
            ->get()
            ->getRowArray();

        if (!$landlord) {
            return $this->respondWithError('Landlord not found', 400);
        }

        $messageData = [
            'sender_id' => $tenantId,
            'receiver_id' => $landlord['landlord_id'],
            'property_id' => $lease['property_id'],
            'subject' => $this->request->getPost('subject'),
            'message' => $this->request->getPost('message'),
            'message_type' => 'general'
        ];

        if ($db->table('messages')->insert($messageData)) {
            return $this->respondWithSuccess([], 'Message sent successfully');
        } else {
            return $this->respondWithError('Failed to send message');
        }
    }

    /**
     * Mark Message as Read
     */
    public function markMessageRead($messageId)
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $tenantId = $this->getCurrentUserId();

        $db = \Config\Database::connect();
        $updated = $db->table('messages')
            ->where('id', $messageId)
            ->where('receiver_id', $tenantId)
            ->update(['is_read' => 1]);

        if ($updated) {
            return $this->respondWithSuccess([], 'Message marked as read');
        } else {
            return $this->respondWithError('Failed to update message');
        }
    }

    /**
     * View Profile
     */
    public function profile()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($this->getCurrentUserId());

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('tenant/profile', $data);
    }

    /**
     * Update Profile
     */
    public function updateProfile()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();

        // Debug: Check if we have a user ID
        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
            }
            return redirect()->to('/auth/login');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
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

        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Try to update and check for errors
        try {
            $result = $this->userModel->update($userId, $updateData);

            if ($result) {
                // Update session data
                session()->set([
                    'full_name' => $updateData['first_name'] . ' ' . $updateData['last_name'],
                    'email' => $updateData['email']
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Profile updated successfully!'
                    ]);
                }

                $this->setSuccess('Profile updated successfully');
                return redirect()->to('/tenant/profile');
            } else {
                // Get database errors
                $db = \Config\Database::connect();
                $error = $db->error();

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Database error: ' . $error['message']
                    ]);
                }

                $this->setError('Failed to update profile: ' . $error['message']);
                return redirect()->back();
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }

            $this->setError('Error updating profile: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function changePassword()
{
    // Allow only POST requests
    if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }

    $redirect = $this->requireTenant();
    if ($redirect) {
        return $this->response->setStatusCode(401)->setJSON([
            'success' => false,
            'message' => 'Authentication required'
        ]);
    }

    $userId = $this->getCurrentUserId();
    
    $rules = [
        'current_password' => 'required',
        'new_password'     => 'required|min_length[6]',
        'confirm_password' => 'required|matches[new_password]'
    ];

    if (!$this->validate($rules)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $this->validator->getErrors()
        ]);
    }

    // Get current user
    $user = $this->userModel->find($userId);
    
    if (!$user) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    // Verify current password
    if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
    }

    // Update password
    $data = [
        'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    try {
        if ($this->userModel->update($userId, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to change password'
            ]);
        }
    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
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
        $lease = $this->leaseModel->getLeaseByTenant($tenantId);

        // Instead of redirecting, show a message
        if (!$lease) {
            $data = [
                'title' => 'Lease Information',
                'lease' => null,
                'landlords' => [],
                'message' => 'No active lease found. Please contact your property manager.'
            ];
            return view('tenant/lease', $data);
        }

        // Get landlord information
        $db = \Config\Database::connect();
        $landlords = $db->table('property_ownership po')
            ->select('u.first_name, u.last_name, u.email, u.phone, po.ownership_percentage')
            ->join('users u', 'u.id = po.landlord_id')
            ->where('po.property_id', $lease['property_id'])
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Lease Information',
            'lease' => $lease,
            'landlords' => $landlords
        ];

        return view('tenant/lease', $data);
    }

    public function testLease()
    {
        return view('tenant/lease', [
            'title' => 'Lease Information - Test',
            'lease' => null,
            'landlords' => []
        ]);
    }

    public function testProfile()
    {
        return view('tenant/profile', [
            'title' => 'My Profile - Test',
            'user' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com'
            ]
        ]);
    }

    /**
     * Get Next Payment Due
     */
    private function getNextPaymentDue($tenantId)
    {
        $payments = $this->paymentModel->getPaymentsByTenant($tenantId);
        $pendingPayments = array_filter($payments, function ($payment) {
            return in_array($payment['status'], ['pending', 'overdue']);
        });

        if (empty($pendingPayments)) {
            return null;
        }

        // Sort by due date and return earliest
        usort($pendingPayments, function ($a, $b) {
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });

        return $pendingPayments[0];
    }

    /**
     * Get Tenant Statistics
     */
    private function getTenantStats($tenantId)
    {
        $payments = $this->paymentModel->getPaymentsByTenant($tenantId);
        $maintenance = $this->maintenanceModel->getRequestsByTenant($tenantId);

        $stats = [
            'total_payments' => count($payments),
            'paid_payments' => count(array_filter($payments, function ($p) {
                return $p['status'] == 'paid';
            })),
            'pending_payments' => count(array_filter($payments, function ($p) {
                return $p['status'] == 'pending';
            })),
            'overdue_payments' => count(array_filter($payments, function ($p) {
                return $p['status'] == 'overdue';
            })),
            'total_maintenance' => count($maintenance),
            'pending_maintenance' => count(array_filter($maintenance, function ($m) {
                return $m['status'] == 'pending';
            })),
            'completed_maintenance' => count(array_filter($maintenance, function ($m) {
                return $m['status'] == 'completed';
            }))
        ];

        // Calculate total paid amount
        $stats['total_paid_amount'] = 0;
        foreach ($payments as $payment) {
            if ($payment['status'] == 'paid') {
                $stats['total_paid_amount'] += $payment['amount'];
            }
        }

        return $stats;
    }

    /**
     * Process Payment Method
     */
    private function processPaymentMethod($paymentMethod, $amount, $payment)
    {
        // This is a simplified payment processing simulation
        // In a real application, you would integrate with actual payment gateways

        switch ($paymentMethod) {
            case 'credit_card':
                return $this->processCreditCard($amount, $payment);
            case 'bank_transfer':
                return $this->processBankTransfer($amount, $payment);
            case 'paypal':
                return $this->processPayPal($amount, $payment);
            default:
                return ['success' => false, 'message' => 'Invalid payment method'];
        }
    }

    /**
     * Process Credit Card Payment (Simulation)
     */
    private function processCreditCard($amount, $payment)
    {
        // Simulate credit card processing
        // In reality, you would use Stripe, Square, or another payment processor

        $cardNumber = $this->request->getPost('card_number');
        $expiryDate = $this->request->getPost('expiry_date');
        $cvv = $this->request->getPost('cvv');
        $cardName = $this->request->getPost('card_name');

        // Basic validation
        if (empty($cardNumber) || empty($expiryDate) || empty($cvv) || empty($cardName)) {
            return ['success' => false, 'message' => 'Missing credit card information'];
        }

        // Simulate successful payment
        $transactionId = 'CC_' . time() . '_' . rand(1000, 9999);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Credit card payment processed successfully'
        ];
    }

    /**
     * Process Bank Transfer Payment (Simulation)
     */
    private function processBankTransfer($amount, $payment)
    {
        // Simulate bank transfer processing
        $bankAccount = $this->request->getPost('bank_account');
        $routingNumber = $this->request->getPost('routing_number');

        if (empty($bankAccount) || empty($routingNumber)) {
            return ['success' => false, 'message' => 'Missing bank information'];
        }

        // Simulate successful transfer
        $transactionId = 'BT_' . time() . '_' . rand(1000, 9999);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Bank transfer initiated successfully'
        ];
    }

    /**
     * Process PayPal Payment (Simulation)
     */
    private function processPayPal($amount, $payment)
    {
        // Simulate PayPal processing
        $paypalEmail = $this->request->getPost('paypal_email');

        if (empty($paypalEmail)) {
            return ['success' => false, 'message' => 'Missing PayPal email'];
        }

        // Simulate successful PayPal payment
        $transactionId = 'PP_' . time() . '_' . rand(1000, 9999);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'PayPal payment processed successfully'
        ];
    }
}
