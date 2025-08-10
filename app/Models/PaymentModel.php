<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'lease_id',
        'tenant_id',
        'property_id',
        'amount',
        'payment_date',
        'due_date',
        'payment_method',
        'transaction_id',
        'status',
        'late_fee',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'lease_id' => 'required|integer',
        'tenant_id' => 'required|integer',
        'property_id' => 'required|integer',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
        'payment_method' => 'required|in_list[cash,check,bank_transfer,credit_card,paypal]',
        'status' => 'in_list[paid,pending,overdue,failed]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['calculateLateFee'];
    protected $beforeUpdate = ['calculateLateFee'];

    /**
     * Calculate late fee if payment is overdue
     */
    protected function calculateLateFee(array $data)
    {
        if (isset($data['data']['due_date']) && isset($data['data']['payment_date'])) {
            $dueDate = strtotime($data['data']['due_date']);
            $paymentDate = strtotime($data['data']['payment_date']);
            
            if ($paymentDate > $dueDate) {
                // Get late fee settings from system settings
                $db = \Config\Database::connect();
                $lateFeeQuery = $db->table('system_settings')
                                  ->where('setting_key', 'late_fee_percentage')
                                  ->get()
                                  ->getRowArray();
                
                $lateFeePercentage = $lateFeeQuery ? (float)$lateFeeQuery['setting_value'] : 5;
                
                // Calculate days late
                $daysLate = ceil(($paymentDate - $dueDate) / (60 * 60 * 24));
                
                if ($daysLate > 0) {
                    $amount = $data['data']['amount'] ?? 0;
                    $data['data']['late_fee'] = ($amount * $lateFeePercentage / 100);
                }
            }
        }
        
        return $data;
    }

    /**
     * Get all payments with related data
     */
    public function getAllPayments($limit = null, $offset = 0)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments p');
        $builder->select('p.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, t.email as tenant_email,
                         pr.property_name, pr.address as property_address,
                         l.rent_amount as lease_rent');
        $builder->join('users t', 't.id = p.tenant_id');
        $builder->join('properties pr', 'pr.id = p.property_id');
        $builder->join('leases l', 'l.id = p.lease_id');
        $builder->orderBy('p.payment_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get payments for specific tenant
     */
    public function getPaymentsByTenant($tenantId, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments p');
        $builder->select('p.*, pr.property_name, pr.address as property_address');
        $builder->join('properties pr', 'pr.id = p.property_id');
        $builder->where('p.tenant_id', $tenantId);
        $builder->orderBy('p.payment_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get payments for specific landlord
     */
    public function getPaymentsByLandlord($landlordId, $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments p');
        $builder->select('p.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         pr.property_name, pr.address as property_address,
                         po.ownership_percentage');
        $builder->join('users t', 't.id = p.tenant_id');
        $builder->join('properties pr', 'pr.id = p.property_id');
        $builder->join('property_ownership po', 'po.property_id = p.property_id');
        $builder->where('po.landlord_id', $landlordId);
        $builder->orderBy('p.payment_date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get overdue payments
     */
    public function getOverduePayments($limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments p');
        $builder->select('p.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name, t.email as tenant_email, t.phone as tenant_phone,
                         pr.property_name, pr.address as property_address');
        $builder->join('users t', 't.id = p.tenant_id');
        $builder->join('properties pr', 'pr.id = p.property_id');
        $builder->where('p.status', 'overdue');
        $builder->orWhere('p.due_date <', date('Y-m-d'));
        $builder->where('p.status !=', 'paid');
        $builder->orderBy('p.due_date', 'ASC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get recent payments
     */
    public function getRecentPayments($limit = 10)
    {
        return $this->getAllPayments($limit);
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments($limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments p');
        $builder->select('p.*, 
                         t.first_name as tenant_first_name, t.last_name as tenant_last_name,
                         pr.property_name, pr.address as property_address');
        $builder->join('users t', 't.id = p.tenant_id');
        $builder->join('properties pr', 'pr.id = p.property_id');
        $builder->where('p.status', 'pending');
        $builder->orderBy('p.due_date', 'ASC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($paymentId, $status, $transactionId = null)
    {
        $data = ['status' => $status];
        
        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }
        
        if ($status === 'paid') {
            $data['payment_date'] = date('Y-m-d');
        }
        
        return $this->update($paymentId, $data);
    }

    /**
     * Record manual payment
     */
    public function recordManualPayment($data)
    {
        $paymentData = [
            'lease_id' => $data['lease_id'],
            'tenant_id' => $data['tenant_id'],
            'property_id' => $data['property_id'],
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'],
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'status' => 'paid',
            'notes' => $data['notes'] ?? null
        ];
        
        return $this->insert($paymentData);
    }

    /**
     * Get monthly payment statistics
     */
    public function getMonthlyStats($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        
        $db = \Config\Database::connect();
        
        // Total collected this month
        $collectedQuery = $db->query("
            SELECT SUM(amount) as total_collected, COUNT(*) as payment_count
            FROM payments 
            WHERE status = 'paid' 
            AND YEAR(payment_date) = ? 
            AND MONTH(payment_date) = ?
        ", [$year, $month]);
        $collected = $collectedQuery->getRowArray();
        
        // Total pending this month
        $pendingQuery = $db->query("
            SELECT SUM(amount) as total_pending, COUNT(*) as pending_count
            FROM payments 
            WHERE status IN ('pending', 'overdue') 
            AND YEAR(due_date) = ? 
            AND MONTH(due_date) = ?
        ", [$year, $month]);
        $pending = $pendingQuery->getRowArray();
        
        // Late fees collected
        $lateFeesQuery = $db->query("
            SELECT SUM(late_fee) as total_late_fees
            FROM payments 
            WHERE status = 'paid' 
            AND late_fee > 0
            AND YEAR(payment_date) = ? 
            AND MONTH(payment_date) = ?
        ", [$year, $month]);
        $lateFees = $lateFeesQuery->getRowArray();
        
        return [
            'total_collected' => $collected['total_collected'] ?? 0,
            'payment_count' => $collected['payment_count'] ?? 0,
            'total_pending' => $pending['total_pending'] ?? 0,
            'pending_count' => $pending['pending_count'] ?? 0,
            'total_late_fees' => $lateFees['total_late_fees'] ?? 0
        ];
    }

    /**
     * Get yearly payment statistics
     */
    public function getYearlyStats($year = null)
    {
        $year = $year ?? date('Y');
        
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                MONTH(payment_date) as month,
                SUM(amount) as total_amount,
                COUNT(*) as payment_count,
                SUM(late_fee) as total_late_fees
            FROM payments 
            WHERE status = 'paid' 
            AND YEAR(payment_date) = ?
            GROUP BY MONTH(payment_date)
            ORDER BY month
        ", [$year]);
        
        return $query->getResultArray();
    }

    /**
     * Generate rent payments for all active leases
     */
    public function generateMonthlyRentPayments($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        $dueDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        
        $db = \Config\Database::connect();
        
        // Get all active leases
        $leases = $db->table('leases l')
                    ->select('l.*, p.id as property_id')
                    ->join('properties p', 'p.id = l.property_id')
                    ->where('l.status', 'active')
                    ->where('l.lease_start <=', $dueDate)
                    ->where('l.lease_end >=', $dueDate)
                    ->get()
                    ->getResultArray();
        
        $inserted = 0;
        
        foreach ($leases as $lease) {
            // Check if payment already exists for this month
            $existing = $this->where('lease_id', $lease['id'])
                           ->where('YEAR(due_date)', $year)
                           ->where('MONTH(due_date)', $month)
                           ->first();
            
            if (!$existing) {
                $paymentData = [
                    'lease_id' => $lease['id'],
                    'tenant_id' => $lease['tenant_id'],
                    'property_id' => $lease['property_id'],
                    'amount' => $lease['rent_amount'],
                    'due_date' => $dueDate,
                    'payment_method' => 'pending',
                    'status' => 'pending'
                ];
                
                if ($this->insert($paymentData)) {
                    $inserted++;
                }
            }
        }
        
        return $inserted;
    }

    /**
     * Mark overdue payments
     */
    public function markOverduePayments()
    {
        $db = \Config\Database::connect();
        
        // Get grace period from settings
        $graceQuery = $db->table('system_settings')
                        ->where('setting_key', 'grace_period_days')
                        ->get()
                        ->getRowArray();
        
        $gracePeriod = $graceQuery ? (int)$graceQuery['setting_value'] : 5;
        $overdueDate = date('Y-m-d', strtotime("-{$gracePeriod} days"));
        
        return $db->table('payments')
                 ->where('status', 'pending')
                 ->where('due_date <', $overdueDate)
                 ->update(['status' => 'overdue']);
    }
}