<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'landlord_id',
        'name',
        'description',
        'type',
        'properties',
        'period_start',
        'period_end',
        'income_period',
        'file_path',
        'status',
        'generated_at',
        'generated_by',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'landlord_id' => 'required|integer',
        'name' => 'required|string|max_length[255]',
        'type' => 'required|in_list[ownership,owner_income,financial,maintenance,occupancy]',
        'status' => 'in_list[pending,processing,completed,failed]'
    ];

    protected $validationMessages = [
        'landlord_id' => [
            'required' => 'Landlord ID is required.',
            'integer' => 'Landlord ID must be an integer.'
        ],
        'name' => [
            'required' => 'Report name is required.',
            'max_length' => 'Report name cannot exceed 255 characters.'
        ],
        'type' => [
            'required' => 'Report type is required.',
            'in_list' => 'Invalid report type selected.'
        ]
    ];

    /**
     * Get reports for a specific landlord
     */
    public function getReportsForLandlord($landlordId, $limit = null)
    {
        $builder = $this->where('landlord_id', $landlordId)
                       ->orderBy('generated_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Get reports by type for a landlord
     */
    public function getReportsByType($landlordId, $type)
    {
        return $this->where([
            'landlord_id' => $landlordId,
            'type' => $type
        ])->orderBy('generated_at', 'DESC')->findAll();
    }

    /**
     * Get completed reports for a landlord
     */
    public function getCompletedReports($landlordId)
    {
        return $this->where([
            'landlord_id' => $landlordId,
            'status' => 'completed'
        ])->orderBy('generated_at', 'DESC')->findAll();
    }

    /**
     * Create a new report record
     */
    public function createReport($data)
    {
        $data['status'] = 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return false;
    }

    /**
     * Update report status
     */
    public function updateReportStatus($reportId, $status, $additionalData = [])
    {
        $updateData = array_merge($additionalData, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($status === 'completed') {
            $updateData['generated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($reportId, $updateData);
    }

    /**
     * Delete reports older than specified days
     */
    public function deleteOldReports($days = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $cutoffDate)->delete();
    }

    /**
     * Get report statistics for a landlord
     */
    public function getReportStats($landlordId)
    {
        $db = \Config\Database::connect();
        
        // Total reports
        $totalReports = $this->where('landlord_id', $landlordId)->countAllResults();
        
        // Reports by status
        $statusCounts = $db->table($this->table)
            ->select('status, COUNT(*) as count')
            ->where('landlord_id', $landlordId)
            ->groupBy('status')
            ->get()
            ->getResultArray();
        
        // Reports by type
        $typeCounts = $db->table($this->table)
            ->select('type, COUNT(*) as count')
            ->where('landlord_id', $landlordId)
            ->groupBy('type')
            ->get()
            ->getResultArray();
        
        return [
            'total_reports' => $totalReports,
            'status_breakdown' => $statusCounts,
            'type_breakdown' => $typeCounts
        ];
    }

    /**
     * Search reports
     */
    public function searchReports($landlordId, $searchTerm, $type = null, $status = null)
    {
        $builder = $this->where('landlord_id', $landlordId);
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('description', $searchTerm)
                   ->orLike('notes', $searchTerm)
                   ->groupEnd();
        }
        
        if ($type) {
            $builder->where('type', $type);
        }
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('generated_at', 'DESC')->findAll();
    }

    /**
     * Get monthly report generation statistics
     */
    public function getMonthlyReportStats($landlordId, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        $db = \Config\Database::connect();
        
        return $db->table($this->table)
            ->select('MONTH(generated_at) as month, COUNT(*) as count')
            ->where('landlord_id', $landlordId)
            ->where('YEAR(generated_at)', $year)
            ->where('status', 'completed')
            ->groupBy('MONTH(generated_at)')
            ->orderBy('month')
            ->get()
            ->getResultArray();
    }
}