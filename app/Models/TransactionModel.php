<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'payment_id',
        'transaction_type',
        'payment_method',
        'transaction_id',
        'amount',
        'status',
        'tenant_id',
        'metadata',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'payment_id' => 'required|integer',
        'transaction_type' => 'required|string|max_length[50]',
        'payment_method' => 'required|string|max_length[50]',
        'transaction_id' => 'required|string|max_length[255]',
        'amount' => 'required|decimal',
        'status' => 'required|string|max_length[50]',
        'tenant_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'payment_id' => [
            'required' => 'Payment ID is required',
            'integer' => 'Payment ID must be a valid integer'
        ],
        'amount' => [
            'required' => 'Transaction amount is required',
            'decimal' => 'Amount must be a valid decimal number'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Allow callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get transactions for a specific tenant
     */
    public function getTransactionsByTenant($tenantId, $limit = 10)
    {
        return $this->where('tenant_id', $tenantId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get transactions for a specific payment
     */
    public function getTransactionsByPayment($paymentId)
    {
        return $this->where('payment_id', $paymentId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get transactions by status
     */
    public function getTransactionsByStatus($status, $tenantId = null)
    {
        $builder = $this->where('status', $status);
        
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get successful transactions for a tenant
     */
    public function getSuccessfulTransactions($tenantId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('tenant_id', $tenantId)
                       ->where('status', 'completed');
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get total amount paid by tenant
     */
    public function getTotalPaidByTenant($tenantId, $startDate = null, $endDate = null)
    {
        $builder = $this->selectSum('amount')
                       ->where('tenant_id', $tenantId)
                       ->where('status', 'completed')
                       ->where('transaction_type', 'payment');
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }
        
        $result = $builder->first();
        return $result['amount'] ?? 0;
    }

    /**
     * Log a payment transaction
     */
    public function logPaymentTransaction($data)
    {
        $transactionData = [
            'payment_id' => $data['payment_id'],
            'transaction_type' => 'payment',
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'completed',
            'tenant_id' => $data['tenant_id'],
            'metadata' => json_encode($data['metadata'] ?? [])
        ];
        
        return $this->insert($transactionData);
    }

    /**
     * Log a refund transaction
     */
    public function logRefundTransaction($data)
    {
        $transactionData = [
            'payment_id' => $data['payment_id'],
            'transaction_type' => 'refund',
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'],
            'amount' => -abs($data['amount']), // Negative amount for refunds
            'status' => $data['status'] ?? 'completed',
            'tenant_id' => $data['tenant_id'],
            'metadata' => json_encode($data['metadata'] ?? [])
        ];
        
        return $this->insert($transactionData);
    }

    /**
     * Get transaction statistics for a tenant
     */
    public function getTransactionStats($tenantId, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        // Get monthly payment totals
        $monthlyPayments = $this->select('MONTH(created_at) as month, SUM(amount) as total')
                               ->where('tenant_id', $tenantId)
                               ->where('transaction_type', 'payment')
                               ->where('status', 'completed')
                               ->where('YEAR(created_at)', $year)
                               ->groupBy('MONTH(created_at)')
                               ->orderBy('month')
                               ->findAll();
        
        // Get payment method breakdown
        $paymentMethods = $this->select('payment_method, COUNT(*) as count, SUM(amount) as total')
                              ->where('tenant_id', $tenantId)
                              ->where('transaction_type', 'payment')
                              ->where('status', 'completed')
                              ->where('YEAR(created_at)', $year)
                              ->groupBy('payment_method')
                              ->findAll();
        
        return [
            'monthly_payments' => $monthlyPayments,
            'payment_methods' => $paymentMethods
        ];
    }
}