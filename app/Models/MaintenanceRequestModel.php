<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceRequestModel extends Model
{
    protected $table = 'maintenance_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    // Reflect the new DB columns
    protected $allowedFields = [
        'property_id',
        'unit_id',
        'assigned_staff_id',
        'title',
        'description',
        'priority',            // enum('low','normal','high','urgent')
        'status',              // enum('pending','approved','rejected','assigned','in_progress','completed','cancelled')
        'estimated_cost',
        'actual_cost',
        'materials_used',
        'work_notes',
        'rejection_reason',
        'requested_date',      // timestamp
        'approved_date',       // datetime
        'assigned_date',       // timestamp
        'completed_date',      // timestamp
        'rejected_date',       // datetime
        'created_by_landlord', // tinyint(1)
        'created_at',
        'updated_at',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'property_id' => 'required|integer',
        'unit_id' => 'permit_empty|integer',
        'title' => 'required|min_length[5]|max_length[200]',
        'description' => 'required|min_length[10]',
        'priority' => 'in_list[low,normal,high,urgent]',
        'status' => 'in_list[pending,approved,rejected,assigned,in_progress,completed,cancelled]',
        'estimated_cost' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'actual_cost' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'assigned_staff_id' => 'permit_empty|integer',
        'created_by_landlord' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['ensureRequestedDate'];
    protected $beforeUpdate = ['autoStampStatusDates'];

    /**
     * Ensure requested_date is set on insert
     */
    protected function ensureRequestedDate(array $data)
    {
        if (!isset($data['data']['requested_date'])) {
            // Use DB server time format
            $data['data']['requested_date'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * Automatically set date fields when status changes
     */
    protected function autoStampStatusDates(array $data)
    {
        if (!isset($data['data']['status'])) {
            return $data;
        }

        $status = $data['data']['status'];
        $now = date('Y-m-d H:i:s');

        switch ($status) {
            case 'approved':
                if (!isset($data['data']['approved_date'])) {
                    $data['data']['approved_date'] = $now;
                }
                break;

            case 'rejected':
                if (!isset($data['data']['rejected_date'])) {
                    $data['data']['rejected_date'] = $now;
                }
                // keep rejection_reason if passed by controller
                break;

            case 'assigned':
                if (!isset($data['data']['assigned_date'])) {
                    $data['data']['assigned_date'] = $now;
                }
                break;

            case 'completed':
                if (!isset($data['data']['completed_date'])) {
                    $data['data']['completed_date'] = $now;
                }
                break;

            default:
                // no-op for other states
                break;
        }

        return $data;
    }

    /**
     * Base query with common joins
     */
    protected function baseBuilder()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');

        // Join properties and optional unit, and maintenance staff (users.role='maintenance')
        $builder->select("
            mr.*,
            p.property_name,
            p.address AS property_address,
            pu.unit_name,
            s.first_name AS staff_first_name,
            s.last_name  AS staff_last_name,
            s.email      AS staff_email,
            s.phone      AS staff_phone
        ");
        $builder->join('properties p', 'p.id = mr.property_id', 'inner');
        $builder->join('property_units pu', 'pu.id = mr.unit_id', 'left');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');

        return $builder;
    }

    /**
     * Get all maintenance requests with filters
     */
    public function getAllRequests(?string $status = null, ?string $priority = null, ?int $limit = null)
    {
        $builder = $this->baseBuilder();

        if (!empty($status)) {
            $builder->where('mr.status', $status);
        }
        if (!empty($priority)) {
            $builder->where('mr.priority', $priority);
        }

        $builder->orderBy('FIELD(mr.priority, "urgent","high","normal","low")', '', false);
        $builder->orderBy('mr.requested_date', 'DESC');

        if (!empty($limit)) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function getPendingRequests(?int $limit = null)
    {
        return $this->getAllRequests('pending', null, $limit);
    }

    /**
     * Requests by landlord (via property_shareholders.user_id)
     */
    public function getRequestsByLandlord(int $landlordId, ?int $limit = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('maintenance_requests mr');

        $builder->select("
            mr.*,
            p.property_name,
            p.address AS property_address,
            pu.unit_name,
            s.first_name AS staff_first_name,
            s.last_name  AS staff_last_name,
            ps.ownership_percentage
        ");
        $builder->join('properties p', 'p.id = mr.property_id', 'inner');
        $builder->join('property_units pu', 'pu.id = mr.unit_id', 'left');
        $builder->join('property_shareholders ps', 'ps.property_id = mr.property_id', 'inner');
        $builder->join('users s', 's.id = mr.assigned_staff_id AND s.role = "maintenance"', 'left');

        $builder->where('ps.user_id', $landlordId);
        $builder->whereIn('ps.status', ['active', 'pending']); // adjust if you only want active

        $builder->orderBy('FIELD(mr.priority, "urgent","high","normal","low")', '', false);
        $builder->orderBy('mr.requested_date', 'DESC');

        if (!empty($limit)) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    // NEW: Unassigned pending queue, newest first
    public function getPendingQueue($limit = null)
    {
        $db = \Config\Database::connect();
        $b = $db->table('maintenance_requests mr');
        $b->select('mr.*,
                p.property_name, p.address as property_address,
                u.unit_name');
        $b->join('properties p', 'p.id = mr.property_id');
        $b->join('property_units u', 'u.id = mr.unit_id', 'left');
        $b->where('mr.status', 'pending');
        $b->groupStart()->where('mr.assigned_staff_id', null)->orWhere('mr.assigned_staff_id', 0)->groupEnd();
        // newest first
        $b->orderBy('mr.requested_date', 'DESC');
        if ($limit)
            $b->limit($limit);
        return $b->get()->getResultArray();
    }

    public function getRequestsByStaff($staffId, $status = null, $limit = null)
    {
        $db = \Config\Database::connect();
        $b = $db->table('maintenance_requests mr');
        $b->select('mr.*,
                p.property_name, p.address as property_address,
                u.unit_name');
        $b->join('properties p', 'p.id = mr.property_id');
        $b->join('property_units u', 'u.id = mr.unit_id', 'left');
        $b->where('mr.assigned_staff_id', $staffId);
        if ($status) {
            $b->where('mr.status', $status);
        }

        // Newest first: fall back through these timestamps
        $b->orderBy('mr.updated_at', 'DESC');
        $b->orderBy('mr.completed_date', 'DESC');
        $b->orderBy('mr.approved_date', 'DESC');
        $b->orderBy('mr.assigned_date', 'DESC');
        $b->orderBy('mr.requested_date', 'DESC');

        if ($limit) {
            $b->limit($limit);
        }
        return $b->get()->getResultArray();
    }

    /**
     * Assign staff (moves status to 'assigned')
     */
    public function assignStaff(int $requestId, int $staffId)
    {
        $data = [
            'assigned_staff_id' => $staffId,
            'status' => 'assigned',
            'assigned_date' => date('Y-m-d H:i:s'),
        ];
        return $this->update($requestId, $data);
    }

    /**
     * Update status with optional notes/reason
     */
    public function updateStatus(int $requestId, string $status, ?string $notes = null, ?string $rejectionReason = null)
    {
        $data = ['status' => $status];

        if (!empty($notes)) {
            $data['work_notes'] = $notes;
        }
        if ($status === 'completed') {
            $data['completed_date'] = date('Y-m-d H:i:s');
        }
        if ($status === 'approved' && empty($data['approved_date'])) {
            $data['approved_date'] = date('Y-m-d H:i:s');
        }
        if ($status === 'rejected') {
            $data['rejected_date'] = date('Y-m-d H:i:s');
            $data['rejection_reason'] = $rejectionReason ?? ($notes ?? null);
        }

        return $this->update($requestId, $data);
    }

    /**
     * Complete request with optional costs/materials/notes
     */
    public function completeRequest(int $requestId, ?float $actualCost = null, ?string $materialsUsed = null, ?string $notes = null)
    {
        $data = [
            'status' => 'completed',
            'completed_date' => date('Y-m-d H:i:s'),
        ];

        if ($actualCost !== null) {
            $data['actual_cost'] = $actualCost;
        }
        if (!empty($materialsUsed)) {
            $data['materials_used'] = $materialsUsed;
        }
        if (!empty($notes)) {
            $data['work_notes'] = $notes;
        }

        return $this->update($requestId, $data);
    }

    public function getUrgentRequests(?int $limit = null)
    {
        return $this->getAllRequests(null, 'urgent', $limit);
    }

    /**
     * High priority & not finished
     */
    public function getHighPriorityRequests(?int $limit = null)
    {
        $builder = $this->baseBuilder();
        $builder->whereIn('mr.priority', ['high', 'urgent']);
        $builder->whereIn('mr.status', ['pending', 'approved', 'assigned', 'in_progress']);
        $builder->orderBy('FIELD(mr.priority, "urgent","high")', '', false);
        $builder->orderBy('mr.requested_date', 'ASC');

        if (!empty($limit)) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Dashboard statistics
     */
    public function getMaintenanceStatistics(): array
    {
        $db = \Config\Database::connect();
        $stats = [];

        // Total
        $stats['total'] = (int) $db->table('maintenance_requests')->countAllResults();

        // By status
        $statusRows = $db->query("
            SELECT status, COUNT(*) AS cnt
            FROM maintenance_requests
            GROUP BY status
        ")->getResultArray();
        $stats['by_status'] = [];
        foreach ($statusRows as $r) {
            $stats['by_status'][$r['status']] = (int) $r['cnt'];
        }

        // By priority
        $prioRows = $db->query("
            SELECT priority, COUNT(*) AS cnt
            FROM maintenance_requests
            GROUP BY priority
        ")->getResultArray();
        $stats['by_priority'] = [];
        foreach ($prioRows as $r) {
            $stats['by_priority'][$r['priority']] = (int) $r['cnt'];
        }

        // Avg completion days
        $avg = $db->query("
            SELECT AVG(DATEDIFF(completed_date, requested_date)) AS avg_days
            FROM maintenance_requests
            WHERE status = 'completed' AND completed_date IS NOT NULL
        ")->getRowArray();
        $stats['avg_completion_days'] = round((float) ($avg['avg_days'] ?? 0), 1);

        // Total completed costs this month
        $cost = $db->query("
            SELECT SUM(actual_cost) AS total_cost
            FROM maintenance_requests
            WHERE status = 'completed'
              AND YEAR(completed_date) = YEAR(CURDATE())
              AND MONTH(completed_date) = MONTH(CURDATE())
        ")->getRowArray();
        $stats['monthly_cost'] = (float) ($cost['total_cost'] ?? 0);

        return $stats;
    }

    /**
     * Overdue = pending/assigned/approved older than X days
     */
    public function getOverdueRequests(int $days = 7): array
    {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $builder = $this->baseBuilder();
        $builder->whereIn('mr.status', ['pending', 'approved', 'assigned']);
        $builder->where('mr.requested_date <', $threshold);
        $builder->orderBy('mr.requested_date', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Full-textish search across key columns
     * (Fixed alias bug: use 's.' not 't.')
     */
    public function searchRequests(string $searchTerm = '', string $status = '', string $priority = '', $propertyId = '')
    {
        $builder = $this->baseBuilder();

        if ($searchTerm !== '') {
            $builder->groupStart()
                ->like('mr.title', $searchTerm)
                ->orLike('mr.description', $searchTerm)
                ->orLike('s.first_name', $searchTerm)
                ->orLike('s.last_name', $searchTerm)
                ->orLike('p.property_name', $searchTerm)
                ->orLike('p.address', $searchTerm)
                ->orLike('pu.unit_name', $searchTerm)
                ->groupEnd();
        }

        if ($status !== '') {
            $builder->where('mr.status', $status);
        }
        if ($priority !== '') {
            $builder->where('mr.priority', $priority);
        }
        if ($propertyId !== '') {
            $builder->where('mr.property_id', $propertyId);
        }

        $builder->orderBy('FIELD(mr.priority, "urgent","high","normal","low")', '', false);
        $builder->orderBy('mr.requested_date', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Monthly report (submitted vs completed, totals, avg time)
     */
    public function getMonthlyReport(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) date('Y');
        $month = $month ?? (int) date('m');

        $db = \Config\Database::connect();

        // Submitted by priority in the month (requested_date)
        $submitted = $db->query("
            SELECT COUNT(*) AS count, priority
            FROM maintenance_requests
            WHERE YEAR(requested_date) = ? AND MONTH(requested_date) = ?
            GROUP BY priority
        ", [$year, $month])->getResultArray();

        // Completed in the month (completed_date)
        $completed = $db->query("
            SELECT COUNT(*) AS count, SUM(actual_cost) AS total_cost
            FROM maintenance_requests
            WHERE status = 'completed'
              AND YEAR(completed_date) = ? AND MONTH(completed_date) = ?
        ", [$year, $month])->getRowArray();

        // Avg completion time in the month
        $avg = $db->query("
            SELECT AVG(DATEDIFF(completed_date, requested_date)) AS avg_days
            FROM maintenance_requests
            WHERE status = 'completed'
              AND YEAR(completed_date) = ? AND MONTH(completed_date) = ?
        ", [$year, $month])->getRowArray();

        return [
            'submitted_by_priority' => $submitted,
            'completed_count' => (int) ($completed['count'] ?? 0),
            'total_cost' => (float) ($completed['total_cost'] ?? 0),
            'avg_completion_days' => round((float) ($avg['avg_days'] ?? 0), 1),
        ];
    }

    /* -----------------------
       Helpers for related data
       ----------------------- */

    // Images for a request
    public function getImages(int $requestId): array
    {
        return \Config\Database::connect()
            ->table('maintenance_images')
            ->where('maintenance_request_id', $requestId)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();
    }

    public function addImage(int $requestId, string $path, string $type = 'issue', ?string $desc = null)
    {
        return \Config\Database::connect()
            ->table('maintenance_images')
            ->insert([
                'maintenance_request_id' => $requestId,
                'image_path' => $path,
                'image_type' => $type, // 'before','after','issue'
                'description' => $desc,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    }

    // Payments linked to a request
    public function getPayments(int $requestId): array
    {
        return \Config\Database::connect()
            ->table('maintenance_payments')
            ->where('maintenance_request_id', $requestId)
            ->orderBy('payment_date', 'DESC')
            ->get()->getResultArray();
    }

    public function addPayment(array $data)
    {
        // Expected keys: maintenance_request_id, unit_id, amount, payment_date, description, payment_method, receipt_file, status, created_by
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        return \Config\Database::connect()
            ->table('maintenance_payments')
            ->insert($data);
    }
}