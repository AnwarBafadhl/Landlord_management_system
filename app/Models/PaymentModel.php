<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payment_receipts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'property_id',
        'landlord_id',
        'tenant_id',
        'amount',
        'payment_date',
        'receipt_file',
        'payment_type',
        'description',
        'status',
        'verification_notes',
        'verified_by',
        'verified_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'property_id' => 'required|integer',
        'landlord_id' => 'required|integer',
        'amount' => 'required|decimal',
        'payment_date' => 'required|valid_date',
        'receipt_file' => 'uploaded[receipt_file]|max_size[receipt_file,5120]|ext_in[receipt_file,jpg,jpeg,png,pdf]'
    ];

    protected $validationMessages = [
        'property_id' => [
            'required' => 'Property ID is required.',
            'integer' => 'Property ID must be an integer.'
        ],
        'amount' => [
            'required' => 'Payment amount is required.',
            'decimal' => 'Amount must be a valid decimal number.'
        ],
        'payment_date' => [
            'required' => 'Payment date is required.',
            'valid_date' => 'Please enter a valid date.'
        ],
        'receipt_file' => [
            'uploaded' => 'Please upload a receipt file.',
            'max_size' => 'Receipt file size must not exceed 5MB.',
            'ext_in' => 'Receipt file must be JPG, PNG, or PDF format.'
        ]
    ];

    /**
     * Get payment receipts for a specific landlord
     */
    public function getPaymentsByLandlord($landlordId)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return sample data
        if (!$db->tableExists($this->table)) {
            return $this->getSamplePaymentReceipts($landlordId);
        }
        
        return $this->select('
                payment_receipts.*, 
                properties.property_name, 
                properties.address as property_address,
                users.first_name as tenant_first_name, 
                users.last_name as tenant_last_name,
                users.email as tenant_email,
                po.ownership_percentage
            ')
            ->join('properties', 'properties.id = payment_receipts.property_id', 'left')
            ->join('users', 'users.id = payment_receipts.tenant_id', 'left')
            ->join('property_ownership po', 'po.property_id = payment_receipts.property_id AND po.landlord_id = ' . (int)$landlordId, 'left')
            ->where('payment_receipts.landlord_id', $landlordId)
            ->orderBy('payment_receipts.payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get payment receipts for a specific property
     */
    public function getPaymentsByProperty($propertyId)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return empty array
        if (!$db->tableExists($this->table)) {
            return [];
        }
        
        return $this->select('
                payment_receipts.*, 
                users.first_name as tenant_first_name, 
                users.last_name as tenant_last_name,
                users.email as tenant_email
            ')
            ->join('users', 'users.id = payment_receipts.tenant_id', 'left')
            ->where('payment_receipts.property_id', $propertyId)
            ->orderBy('payment_receipts.payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get pending verification receipts
     */
    public function getPendingVerification($landlordId = null)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return empty array
        if (!$db->tableExists($this->table)) {
            return [];
        }
        
        $builder = $this->select('
                payment_receipts.*, 
                properties.property_name, 
                users.first_name as tenant_first_name, 
                users.last_name as tenant_last_name,
                users.email as tenant_email
            ')
            ->join('properties', 'properties.id = payment_receipts.property_id', 'left')
            ->join('users', 'users.id = payment_receipts.tenant_id', 'left')
            ->where('payment_receipts.status', 'pending');
        
        if ($landlordId) {
            $builder->where('payment_receipts.landlord_id', $landlordId);
        }
        
        return $builder->orderBy('payment_receipts.created_at', 'ASC')->findAll();
    }

    /**
     * Get verified receipts by date range
     */
    public function getVerifiedPaymentsByDateRange($landlordId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return sample data
        if (!$db->tableExists($this->table)) {
            return $this->getSamplePaymentReceipts($landlordId);
        }
        
        return $this->select('
                payment_receipts.*, 
                properties.property_name, 
                users.first_name as tenant_first_name, 
                users.last_name as tenant_last_name,
                users.email as tenant_email
            ')
            ->join('properties', 'properties.id = payment_receipts.property_id', 'left')
            ->join('users', 'users.id = payment_receipts.tenant_id', 'left')
            ->where('payment_receipts.landlord_id', $landlordId)
            ->where('payment_receipts.payment_date >=', $startDate)
            ->where('payment_receipts.payment_date <=', $endDate)
            ->where('payment_receipts.status', 'verified')
            ->orderBy('payment_receipts.payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Upload payment receipt
     */
    public function uploadPaymentReceipt($data, $receiptFile)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return false
        if (!$db->tableExists($this->table)) {
            return false;
        }
        
        // Handle file upload
        if ($receiptFile && $receiptFile->isValid() && !$receiptFile->hasMoved()) {
            // Create uploads directory if it doesn't exist
            $uploadPath = ROOTPATH . 'public/uploads/receipts/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Generate unique filename
            $fileName = 'receipt_' . time() . '_' . random_string('alnum', 8) . '.' . $receiptFile->getExtension();
            
            // Move file to uploads directory
            if ($receiptFile->move($uploadPath, $fileName)) {
                $data['receipt_file'] = $fileName;
            } else {
                return false;
            }
        }
        
        $data['status'] = 'verified'; // Auto-verified since no pending verification needed
        $data['verified_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return false;
    }

    /**
     * Verify payment receipt (legacy method - now auto-verified)
     */
    public function verifyReceipt($receiptId, $verifiedBy, $notes = null)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return false
        if (!$db->tableExists($this->table)) {
            return false;
        }
        
        $updateData = [
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s'),
            'verification_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($receiptId, $updateData);
    }

    /**
     * Add note to payment receipt
     */
    public function addNoteToReceipt($receiptId, $notes)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return false
        if (!$db->tableExists($this->table)) {
            return false;
        }
        
        $updateData = [
            'verification_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($receiptId, $updateData);
    }

    /**
     * Reject payment receipt (legacy method)
     */
    public function rejectReceipt($receiptId, $verifiedBy, $notes)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return false
        if (!$db->tableExists($this->table)) {
            return false;
        }
        
        $updateData = [
            'status' => 'rejected',
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s'),
            'verification_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($receiptId, $updateData);
    }

    /**
     * Get monthly income summary from verified receipts
     */
    public function getMonthlyIncome($landlordId, $year = null)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return sample data
        if (!$db->tableExists($this->table)) {
            return [
                ['month' => 1, 'total' => 4500],
                ['month' => 2, 'total' => 4800],
                ['month' => 3, 'total' => 4650],
                ['month' => 4, 'total' => 5200],
                ['month' => 5, 'total' => 5100],
                ['month' => 6, 'total' => 4950]
            ];
        }
        
        if (!$year) {
            $year = date('Y');
        }
        
        $builder = $db->table($this->table);
        $builder->select('MONTH(payment_date) as month, SUM(amount) as total');
        $builder->where('landlord_id', $landlordId);
        $builder->where('YEAR(payment_date)', $year);
        $builder->where('status', 'verified');
        $builder->groupBy('MONTH(payment_date)');
        $builder->orderBy('month');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats($landlordId)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return sample data
        if (!$db->tableExists($this->table)) {
            return [
                'total_payments' => 25,
                'current_month_payments' => 8,
                'total_income' => 45000,
                'average_payment' => 1800,
                'verified_receipts' => 25,
                'pending_receipts' => 0,
                'verification_rate' => 100.0
            ];
        }
        
        $currentMonth = date('Y-m');
        $builder = $db->table($this->table);
        $builder->select('
            COUNT(*) as total_payments,
            SUM(CASE WHEN DATE_FORMAT(payment_date, "%Y-%m") = "' . $currentMonth . '" THEN 1 ELSE 0 END) as current_month_payments,
            SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) as verified_receipts,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_receipts,
            SUM(CASE WHEN DATE_FORMAT(payment_date, "%Y-%m") = "' . $currentMonth . '" THEN amount ELSE 0 END) as total_income,
            AVG(amount) as average_payment
        ');
        $builder->where('landlord_id', $landlordId);
        
        $result = $builder->get()->getRowArray();
        
        if ($result) {
            $result['verification_rate'] = $result['total_payments'] > 0 
                ? round(($result['verified_receipts'] / $result['total_payments']) * 100, 1) 
                : 0;
            $result['average_payment'] = $result['average_payment'] ? round($result['average_payment'], 2) : 0;
            $result['total_income'] = $result['total_income'] ? round($result['total_income'], 2) : 0;
        }
        
        return $result ?: [
            'total_payments' => 0,
            'current_month_payments' => 0,
            'verified_receipts' => 0,
            'pending_receipts' => 0,
            'total_income' => 0,
            'average_payment' => 0,
            'verification_rate' => 0
        ];
    }

    /**
     * Search payment receipts
     */
    public function searchReceipts($landlordId, $searchTerm, $status = null, $dateFrom = null, $dateTo = null)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return empty array
        if (!$db->tableExists($this->table)) {
            return [];
        }
        
        $builder = $this->select('
                payment_receipts.*, 
                properties.property_name, 
                users.first_name as tenant_first_name, 
                users.last_name as tenant_last_name,
                users.email as tenant_email
            ')
            ->join('properties', 'properties.id = payment_receipts.property_id', 'left')
            ->join('users', 'users.id = payment_receipts.tenant_id', 'left')
            ->where('payment_receipts.landlord_id', $landlordId);
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('payment_receipts.description', $searchTerm)
                   ->orLike('payment_receipts.amount', $searchTerm)
                   ->orLike('properties.property_name', $searchTerm)
                   ->orLike('users.first_name', $searchTerm)
                   ->orLike('users.last_name', $searchTerm)
                   ->groupEnd();
        }
        
        if ($status) {
            $builder->where('payment_receipts.status', $status);
        }
        
        if ($dateFrom) {
            $builder->where('payment_receipts.payment_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('payment_receipts.payment_date <=', $dateTo);
        }
        
        return $builder->orderBy('payment_receipts.payment_date', 'DESC')->findAll();
    }

    /**
     * Get receipt file path
     */
    public function getReceiptFilePath($receiptId)
    {
        $receipt = $this->find($receiptId);
        if ($receipt && $receipt['receipt_file']) {
            return base_url('uploads/receipts/' . $receipt['receipt_file']);
        }
        return null;
    }

    /**
     * Delete receipt and file
     */
    public function deleteReceipt($receiptId)
    {
        $db = \Config\Database::connect();
        
        // If table doesn't exist, return false
        if (!$db->tableExists($this->table)) {
            return false;
        }
        
        $receipt = $this->find($receiptId);
        if ($receipt) {
            // Delete file if exists
            if ($receipt['receipt_file']) {
                $filePath = ROOTPATH . 'public/uploads/receipts/' . $receipt['receipt_file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete database record
            return $this->delete($receiptId);
        }
        
        return false;
    }

    /**
     * Get sample payment receipts for testing (when table doesn't exist)
     */
    private function getSamplePaymentReceipts($landlordId)
    {
        return [
            [
                'id' => 1,
                'property_id' => 1,
                'landlord_id' => $landlordId,
                'tenant_id' => 1,
                'amount' => 1500.00,
                'payment_date' => date('Y-m-d', strtotime('-1 month')),
                'receipt_file' => 'sample_receipt_1.jpg',
                'payment_type' => 'rent',
                'description' => 'Monthly rent payment',
                'status' => 'verified',
                'verification_notes' => 'Receipt verified and approved',
                'verified_by' => $landlordId,
                'verified_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'property_name' => 'Sample Property',
                'property_address' => '123 Sample St, Sample City',
                'tenant_first_name' => 'John',
                'tenant_last_name' => 'Doe',
                'tenant_email' => 'john.doe@example.com',
                'ownership_percentage' => 100
            ],
            [
                'id' => 2,
                'property_id' => 1,
                'landlord_id' => $landlordId,
                'tenant_id' => 1,
                'amount' => 1500.00,
                'payment_date' => date('Y-m-d'),
                'receipt_file' => 'sample_receipt_2.pdf',
                'payment_type' => 'rent',
                'description' => 'Current month rent',
                'status' => 'verified',
                'verification_notes' => 'Auto-verified payment',
                'verified_by' => $landlordId,
                'verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'property_name' => 'Sample Property',
                'property_address' => '123 Sample St, Sample City',
                'tenant_first_name' => 'John',
                'tenant_last_name' => 'Doe',
                'tenant_email' => 'john.doe@example.com',
                'ownership_percentage' => 100
            ],
            [
                'id' => 3,
                'property_id' => 2,
                'landlord_id' => $landlordId,
                'tenant_id' => 2,
                'amount' => 1200.00,
                'payment_date' => date('Y-m-d', strtotime('-2 weeks')),
                'receipt_file' => 'sample_receipt_3.jpg',
                'payment_type' => 'rent',
                'description' => 'Monthly rent payment',
                'status' => 'verified',
                'verification_notes' => 'Payment confirmed',
                'verified_by' => $landlordId,
                'verified_at' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                'property_name' => 'Downtown Apartment',
                'property_address' => '456 Main St, Downtown',
                'tenant_first_name' => 'Jane',
                'tenant_last_name' => 'Smith',
                'tenant_email' => 'jane.smith@example.com',
                'ownership_percentage' => 50
            ]
        ];
    }
    
    public function getPaymentsByLandlordAndPeriod($landlordId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        
        // Check if all required tables exist
        if (!$db->tableExists('payments') || !$db->tableExists('leases') || !$db->tableExists('properties')) {
            return []; // Return empty array if tables don't exist
        }
        
        $builder = $db->table('payments p');
        $builder->select('p.*, p.property_id, prop.name as property_name, u.first_name, u.last_name');
        $builder->join('leases l', 'l.id = p.lease_id', 'left');
        $builder->join('properties prop', 'prop.id = p.property_id', 'left'); // Use p.property_id directly
        $builder->join('property_owners po', 'po.property_id = prop.id', 'inner');
        $builder->join('users u', 'u.id = l.tenant_id', 'left');
        $builder->where('po.user_id', $landlordId);
        $builder->where('p.payment_date >=', $startDate);
        $builder->where('p.payment_date <=', $endDate);
        $builder->where('p.status', 'paid'); // Only include paid payments
        $builder->orderBy('p.payment_date', 'DESC');
        
        $results = $builder->get()->getResultArray();
        
        // Add tenant_name for easier processing
        foreach ($results as &$payment) {
            $payment['tenant_name'] = trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''));
            if (empty($payment['tenant_name'])) {
                $payment['tenant_name'] = 'Unknown Tenant';
            }
        }
        
        return $results;
    }
}