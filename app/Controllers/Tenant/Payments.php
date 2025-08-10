<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PaymentModel;
use App\Models\LeaseModel;
use App\Models\TransactionModel;

class Payments extends BaseController
{
    protected $paymentModel;
    protected $leaseModel;
    protected $transactionModel;
    
    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->leaseModel = new LeaseModel();
        $this->transactionModel = new TransactionModel();
        
        // Load payment processor libraries
        helper(['url', 'form']);
    }

    public function make()
    {
        $tenantId = session()->get('user_id');
        
        // Get pending payments
        $pendingPayments = $this->paymentModel->getPendingPayments($tenantId);
        
        // Get lease information
        $lease = $this->leaseModel->getActiveLease($tenantId);
        
        $data = [
            'pending_payments' => $pendingPayments,
            'lease' => $lease
        ];
        
        return view('tenant/make_payment', $data);
    }

    public function processStripe()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $input = json_decode($this->request->getBody(), true);
        
        // Validate CSRF
        if (!$this->validateCSRF($input)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Security token mismatch']);
        }
        
        try {
            // Initialize Stripe
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            
            $paymentId = $input['payment_id'];
            $stripeToken = $input['stripe_token'];
            $amount = floatval($input['amount']);
            
            // Get payment details
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment || $payment['tenant_id'] != session()->get('user_id')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment not found']);
            }
            
            // Add processing fee
            $totalAmount = ($amount + 2.50) * 100; // Convert to cents
            
            // Create Stripe charge
            $charge = \Stripe\Charge::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
                'source' => $stripeToken,
                'description' => 'Rent Payment - ' . $payment['description'],
                'metadata' => [
                    'payment_id' => $paymentId,
                    'tenant_id' => session()->get('user_id')
                ]
            ]);
            
            if ($charge->status === 'succeeded') {
                // Update payment status
                $this->paymentModel->update($paymentId, [
                    'status' => 'paid',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'stripe_card',
                    'transaction_id' => $charge->id,
                    'amount_paid' => $amount + 2.50
                ]);
                
                // Log payment transaction
                $this->logPaymentTransaction($paymentId, 'stripe', $charge->id, $amount + 2.50);
                
                return $this->response->setJSON([
                    'success' => true, 
                    'payment_id' => $paymentId,
                    'transaction_id' => $charge->id
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment failed']);
            }
            
        } catch (\Stripe\Exception\CardException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getError()->message]);
        } catch (\Exception $e) {
            log_message('error', 'Stripe payment error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Payment processing failed']);
        }
    }

    public function processPayPal()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $input = json_decode($this->request->getBody(), true);
        
        // Validate CSRF
        if (!$this->validateCSRF($input)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Security token mismatch']);
        }
        
        try {
            $paymentId = $input['payment_id'];
            $paypalOrderId = $input['paypal_order_id'];
            $amount = floatval($input['amount']);
            
            // Get payment details
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment || $payment['tenant_id'] != session()->get('user_id')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment not found']);
            }
            
            // Verify PayPal payment (you would implement PayPal API verification here)
            $paypalVerified = $this->verifyPayPalPayment($paypalOrderId, $amount + 2.50);
            
            if ($paypalVerified) {
                // Update payment status
                $this->paymentModel->update($paymentId, [
                    'status' => 'paid',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'paypal',
                    'transaction_id' => $paypalOrderId,
                    'amount_paid' => $amount + 2.50
                ]);
                
                // Log payment transaction
                $this->logPaymentTransaction($paymentId, 'paypal', $paypalOrderId, $amount + 2.50);
                
                return $this->response->setJSON([
                    'success' => true, 
                    'payment_id' => $paymentId,
                    'transaction_id' => $paypalOrderId
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'PayPal verification failed']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'PayPal payment error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Payment processing failed']);
        }
    }

    public function processApplePay()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $input = json_decode($this->request->getBody(), true);
        
        // Validate CSRF
        if (!$this->validateCSRF($input)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Security token mismatch']);
        }
        
        try {
            // Initialize Stripe (Apple Pay through Stripe)
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            
            $paymentId = $input['payment_id'];
            $applePayToken = $input['apple_pay_token'];
            $amount = floatval($input['amount']);
            
            // Get payment details
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment || $payment['tenant_id'] != session()->get('user_id')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment not found']);
            }
            
            $totalAmount = ($amount + 2.50) * 100; // Convert to cents
            
            // Create Stripe charge with Apple Pay token
            $charge = \Stripe\Charge::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
                'source' => $applePayToken,
                'description' => 'Rent Payment - ' . $payment['description'],
                'metadata' => [
                    'payment_id' => $paymentId,
                    'tenant_id' => session()->get('user_id'),
                    'method' => 'apple_pay'
                ]
            ]);
            
            if ($charge->status === 'succeeded') {
                // Update payment status
                $this->paymentModel->update($paymentId, [
                    'status' => 'paid',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'apple_pay',
                    'transaction_id' => $charge->id,
                    'amount_paid' => $amount + 2.50
                ]);
                
                // Log payment transaction
                $this->logPaymentTransaction($paymentId, 'apple_pay', $charge->id, $amount + 2.50);
                
                return $this->response->setJSON([
                    'success' => true, 
                    'payment_id' => $paymentId,
                    'transaction_id' => $charge->id
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment failed']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Apple Pay payment error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Payment processing failed']);
        }
    }

    public function applePayValidate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $input = json_decode($this->request->getBody(), true);
        
        try {
            // Apple Pay merchant validation
            $validationURL = $input['validationURL'];
            
            // You would implement Apple Pay merchant validation here
            // This involves calling Apple's validation endpoint with your merchant certificates
            $merchantSession = $this->validateApplePayMerchant($validationURL);
            
            return $this->response->setJSON($merchantSession);
            
        } catch (\Exception $e) {
            log_message('error', 'Apple Pay validation error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Validation failed']);
        }
    }

    public function process()
    {
        // Handle traditional payment methods (bank transfer, crypto, etc.)
        $validation = \Config\Services::validation();
        
        $rules = [
            'payment_id' => 'required|integer',
            'payment_method' => 'required|in_list[bank_transfer,crypto]',
            'amount' => 'required|decimal'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $validation);
        }
        
        $paymentId = $this->request->getPost('payment_id');
        $paymentMethod = $this->request->getPost('payment_method');
        $amount = $this->request->getPost('amount');
        
        // Get payment details
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment['tenant_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'Payment not found');
        }
        
        try {
            if ($paymentMethod === 'bank_transfer') {
                $this->processBankTransfer($paymentId, $amount);
            } elseif ($paymentMethod === 'crypto') {
                $this->processCryptoPayment($paymentId, $amount);
            }
            
            return redirect()->to('tenant/payments/success?payment_id=' . $paymentId);
            
        } catch (\Exception $e) {
            log_message('error', 'Payment processing error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    public function success()
    {
        $paymentId = $this->request->getGet('payment_id');
        
        if ($paymentId) {
            $payment = $this->paymentModel->find($paymentId);
            
            if ($payment && $payment['tenant_id'] == session()->get('user_id')) {
                $data = ['payment' => $payment];
                return view('tenant/payment_success', $data);
            }
        }
        
        return redirect()->to('tenant/payments');
    }

    // Helper methods
    private function validateCSRF($input)
    {
        $csrfToken = csrf_token();
        return isset($input[$csrfToken]) && hash_equals(csrf_hash(), $input[$csrfToken]);
    }

    private function verifyPayPalPayment($orderId, $amount)
    {
        // Implement PayPal payment verification
        // This would involve calling PayPal's API to verify the payment
        return true; // Simplified for demo
    }

    private function validateApplePayMerchant($validationURL)
    {
        // Implement Apple Pay merchant validation
        // This requires your Apple Pay merchant certificates
        return ['merchantSession' => 'example_session']; // Simplified for demo
    }

    private function processBankTransfer($paymentId, $amount)
    {
        // Update payment status to pending (bank transfers need manual verification)
        $this->paymentModel->update($paymentId, [
            'status' => 'pending_verification',
            'payment_method' => 'bank_transfer',
            'amount_paid' => $amount + 2.50,
            'notes' => 'Bank transfer initiated - pending verification'
        ]);
        
        // You would typically send this to a payment processor or mark for manual review
    }

    private function processCryptoPayment($paymentId, $amount)
    {
        $cryptoType = $this->request->getPost('crypto_type');
        
        // Generate crypto payment address/invoice
        // This would integrate with a crypto payment processor like BitPay or Coinbase Commerce
        
        $this->paymentModel->update($paymentId, [
            'status' => 'pending_crypto',
            'payment_method' => 'crypto_' . $cryptoType,
            'amount_paid' => $amount + 2.50,
            'notes' => 'Cryptocurrency payment initiated'
        ]);
    }

    private function logPaymentTransaction($paymentId, $method, $transactionId, $amount)
    {
        // Log transaction for audit trail
        $this->transactionModel->logPaymentTransaction([
            'payment_id' => $paymentId,
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'completed',
            'tenant_id' => session()->get('user_id'),
            'metadata' => [
                'processor' => $method,
                'processed_at' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]
        ]);
    }
}