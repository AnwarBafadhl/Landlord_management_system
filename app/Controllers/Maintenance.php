<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\MaintenanceRequestModel;

class Maintenance extends BaseController
{
    protected $userModel;
    protected $propertyModel;
    protected $maintenanceModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->propertyModel = new PropertyModel();
        $this->maintenanceModel = new MaintenanceRequestModel();
    }

    /**
     * Dashboard view
     */
    public function dashboard()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        try {
            // Get dashboard statistics
            $stats = $this->getDashboardStats($staffId, $db);
            
            // Get today's work
            $today = date('Y-m-d');
            $todayRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, p.address as property_address')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->where('mr.assigned_staff_id', $staffId)
                ->where('DATE(mr.assigned_date)', $today)
                ->whereIn('mr.status', ['approved', 'in_progress'])
                ->orderBy('mr.priority', 'DESC')
                ->get()
                ->getResultArray();

            // Get urgent requests
            $urgentRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->where('mr.assigned_staff_id', $staffId)
                ->where('mr.priority', 'urgent')
                ->whereIn('mr.status', ['approved', 'in_progress'])
                ->orderBy('mr.requested_date', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Get recent assigned requests
            $assignedRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, p.address as property_address')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->where('mr.assigned_staff_id', $staffId)
                ->orderBy('mr.assigned_date', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $data = [
                'stats' => $stats,
                'today_requests' => $todayRequests,
                'urgent_requests' => $urgentRequests,
                'assigned_requests' => $assignedRequests
            ];

            return view('maintenance/dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());
            return view('maintenance/dashboard', [
                'stats' => ['total_assigned' => 0, 'completed' => 0, 'today_work' => 0, 'in_progress' => 0, 'urgent' => 0, 'avg_completion_time' => 0],
                'today_requests' => [],
                'urgent_requests' => [],
                'assigned_requests' => []
            ]);
        }
    }

    /**
     * Requests listing view
     */
    public function requests()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        // Get filter parameters
        $status = $this->request->getGet('status');
        $priority = $this->request->getGet('priority');

        try {
            // Get pending requests (queue)
            $pendingQuery = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_name')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->where('mr.status', 'pending');

            if ($priority) {
                $pendingQuery->where('mr.priority', $priority);
            }

            $pendingRequests = $pendingQuery->orderBy('mr.priority', 'DESC')
                ->orderBy('mr.requested_date', 'ASC')
                ->get()
                ->getResultArray();

            // Get my assigned requests
            $myQuery = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_name')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->where('mr.assigned_staff_id', $staffId);

            if ($status) {
                $myQuery->where('mr.status', $status);
            }
            if ($priority) {
                $myQuery->where('mr.priority', $priority);
            }

            $myRequests = $myQuery->orderBy('mr.assigned_date', 'DESC')
                ->get()
                ->getResultArray();

            // Get cancelled request IDs by this staff
            $cancelledIds = $db->table('maintenance_cancellations')
                ->select('request_id')
                ->where('staff_id', $staffId)
                ->get()
                ->getResultArray();
            $cancelledIds = array_column($cancelledIds, 'request_id');

            $data = [
                'pending_requests' => $pendingRequests,
                'requests' => $myRequests,
                'cancelled_ids' => $cancelledIds,
                'current_status' => $status,
                'current_priority' => $priority
            ];

            return view('maintenance/requests', $data);

        } catch (\Exception $e) {
            log_message('error', 'Requests listing error: ' . $e->getMessage());
            return view('maintenance/requests', [
                'pending_requests' => [],
                'requests' => [],
                'cancelled_ids' => [],
                'current_status' => $status,
                'current_priority' => $priority
            ]);
        }
    }

    /**
     * View specific request details
     */
    public function view($id)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        try {
            // Get request details
            $request = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, p.address as property_address, pu.unit_name')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->where('mr.id', $id)
                ->get()
                ->getRowArray();

            if (!$request) {
                return redirect()->to('/maintenance/requests')->with('error', 'Request not found');
            }

            // Check access permissions
            if ($request['status'] !== 'pending' && $request['assigned_staff_id'] != $staffId) {
                return redirect()->to('/maintenance/requests')->with('error', 'Access denied');
            }

            // Get images
            $images = $db->table('maintenance_images')
                ->where('maintenance_request_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();

            // Determine available actions
            $canAccept = ($request['status'] === 'pending');
            $canStart = ($request['status'] === 'approved' && $request['assigned_staff_id'] == $staffId);
            $canComplete = ($request['status'] === 'in_progress' && $request['assigned_staff_id'] == $staffId);
            $canUpload = in_array($request['status'], ['approved', 'in_progress', 'completed']) && $request['assigned_staff_id'] == $staffId;
            $canCancel = in_array($request['status'], ['approved', 'in_progress']) && $request['assigned_staff_id'] == $staffId;

            $data = [
                'request' => $request,
                'images' => $images,
                'canAccept' => $canAccept,
                'canStart' => $canStart,
                'canComplete' => $canComplete,
                'canUpload' => $canUpload,
                'canCancel' => $canCancel
            ];

            return view('maintenance/request_details', $data);

        } catch (\Exception $e) {
            log_message('error', 'View request error: ' . $e->getMessage());
            return redirect()->to('/maintenance/requests')->with('error', 'An error occurred');
        }
    }

    /**
     * Accept/Approve a pending maintenance request
     */
    public function accept($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get request details
            $request = $db->table('maintenance_requests')
                ->where('id', $requestId)
                ->where('status', 'pending')
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found or not available for approval'
                ]);
            }

            $approvedCost = $this->request->getPost('approved_cost');
            if (!$approvedCost || $approvedCost < 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Valid approved cost is required'
                ]);
            }

            // Validate cost against estimate if exists
            if ($request['estimated_cost'] && $approvedCost > ($request['estimated_cost'] + 150)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Approved cost cannot exceed estimate + 150 SAR'
                ]);
            }

            // Get staff details
            $staff = $db->table('users')
                ->where('id', $staffId)
                ->get()
                ->getRowArray();

            // Update to approved status
            $updateData = [
                'status' => 'approved',
                'assigned_staff_id' => $staffId,
                'assigned_date' => date('Y-m-d H:i:s'),
                'approved_date' => date('Y-m-d H:i:s'),
                'approved_by_staff_id' => $staffId,
                'approved_cost' => $approvedCost,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($staff) {
                $updateData['approved_by_name'] = trim($staff['first_name'] . ' ' . $staff['last_name']);
            }

            if ($db->table('maintenance_requests')->where('id', $requestId)->update($updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Request approved successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to approve request'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Accept request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while approving the request'
            ]);
        }
    }

    /**
     * Start work on an approved request
     */
    public function start($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Verify request is assigned to this staff and approved
            $request = $db->table('maintenance_requests')
                ->where('id', $requestId)
                ->where('assigned_staff_id', $staffId)
                ->where('status', 'approved')
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found or not assigned to you'
                ]);
            }

            $durationDays = $this->request->getPost('duration_days');
            $workNotes = $this->request->getPost('work_notes');

            if (!$durationDays || $durationDays < 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Valid duration is required'
                ]);
            }

            // Update to in_progress
            $updateData = [
                'status' => 'in_progress',
                'assigned_date' => date('Y-m-d H:i:s'), // Update assignment date to start date
                'work_notes' => $workNotes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('maintenance_requests')->where('id', $requestId)->update($updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Work started successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to start work'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Start work error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while starting work'
            ]);
        }
    }

    /**
     * Complete a maintenance request
     */
    public function complete($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Verify request is assigned to this staff and in progress
            $request = $db->table('maintenance_requests')
                ->where('id', $requestId)
                ->where('assigned_staff_id', $staffId)
                ->where('status', 'in_progress')
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found, not assigned to you, or not in progress'
                ]);
            }

            $completionNotes = $this->request->getPost('completion_notes');
            $actualCost = $this->request->getPost('actual_cost');
            $materialsUsed = $this->request->getPost('materials_used');

            if (!$completionNotes) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Completion notes are required'
                ]);
            }

            // Handle completion images upload
            $completionImages = $this->request->getFiles();
            $uploadedImages = [];

            if (!empty($completionImages['completion_images'])) {
                foreach ($completionImages['completion_images'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $uploadPath = WRITEPATH . 'uploads/maintenance/';
                        if (!is_dir($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }

                        $fileName = 'completion_' . $requestId . '_' . time() . '_' . uniqid() . '.' . $file->getClientExtension();
                        if ($file->move($uploadPath, $fileName)) {
                            $uploadedImages[] = [
                                'maintenance_request_id' => $requestId,
                                'image_path' => 'uploads/maintenance/' . $fileName,
                                'image_type' => 'after',
                                'description' => 'Completion image',
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }

            // Require at least one completion image
            if (empty($uploadedImages)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'At least one completion image is required'
                ]);
            }

            $db->transStart();

            // Update request to completed
            $updateData = [
                'status' => 'completed',
                'actual_cost' => $actualCost ? (float) $actualCost : null,
                'materials_used' => $materialsUsed,
                'work_notes' => ($request['work_notes'] ?? '') . "\n\nCompletion: " . $completionNotes,
                'completed_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('maintenance_requests')->where('id', $requestId)->update($updateData);

            // Save uploaded images
            foreach ($uploadedImages as $imageData) {
                $db->table('maintenance_images')->insert($imageData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to complete request'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Request completed successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Complete request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while completing the request'
            ]);
        }
    }

    /**
     * Upload image for a request
     */
    public function uploadImage($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Verify request is assigned to this staff
            $request = $db->table('maintenance_requests')
                ->where('id', $requestId)
                ->where('assigned_staff_id', $staffId)
                ->whereIn('status', ['approved', 'in_progress', 'completed'])
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found or not assigned to you'
                ]);
            }

            // Handle file upload
            $imageFile = $this->request->getFile('image');
            if (!$imageFile || !$imageFile->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No valid image file provided'
                ]);
            }

            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($imageFile->getMimeType(), $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Only JPEG and PNG images are allowed'
                ]);
            }

            if ($imageFile->getSize() > 5 * 1024 * 1024) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Image file size must be less than 5MB'
                ]);
            }

            // Upload file
            $uploadPath = WRITEPATH . 'uploads/maintenance/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fileName = 'image_' . $requestId . '_' . time() . '_' . uniqid() . '.' . $imageFile->getClientExtension();

            if ($imageFile->move($uploadPath, $fileName)) {
                // Save image record
                $imageData = [
                    'maintenance_request_id' => $requestId,
                    'image_path' => 'uploads/maintenance/' . $fileName,
                    'image_type' => $this->request->getPost('image_type') ?? 'issue',
                    'description' => $this->request->getPost('description') ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($db->table('maintenance_images')->insert($imageData)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Image uploaded successfully'
                    ]);
                } else {
                    unlink($uploadPath . $fileName);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to save image record'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to upload image file'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Upload image error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while uploading the image'
            ]);
        }
    }

    /**
     * Update request status (for cancellation, etc.)
     */
    public function updateStatus($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();
        $status = $this->request->getPost('status');
        $workNotes = $this->request->getPost('work_notes');

        if ($status === 'cancelled') {
            return $this->cancelRequest($requestId, $workNotes);
        }

        // Handle other status updates as needed
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Status update not supported'
        ]);
    }

    /**
     * Cancel an approved/in-progress request
     */
    private function cancelRequest($requestId, $notes = '')
    {
        $staffId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Verify request is assigned to this staff and can be cancelled
            $request = $db->table('maintenance_requests')
                ->where('id', $requestId)
                ->where('assigned_staff_id', $staffId)
                ->whereIn('status', ['approved', 'in_progress'])
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found, not assigned to you, or cannot be cancelled'
                ]);
            }

            $db->transStart();

            // Reset request to pending status
            $updateData = [
                'status' => 'pending',
                'assigned_staff_id' => null,
                'assigned_date' => null,
                'approved_date' => null,
                'approved_by_staff_id' => null,
                'approved_by_name' => null,
                'approved_cost' => null,
                'work_notes' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('maintenance_requests')->where('id', $requestId)->update($updateData);

            // Add cancellation record
            $cancellationData = [
                'request_id' => $requestId,
                'staff_id' => $staffId,
                'notes' => $notes ?: 'Request cancelled by maintenance staff',
                'cancelled_at' => date('Y-m-d H:i:s')
            ];

            $db->table('maintenance_cancellations')->insert($cancellationData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to cancel request'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Request cancelled and returned to pending queue'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Cancel request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while cancelling the request'
            ]);
        }
    }

    /**
     * Schedule management
     */
    public function schedule()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        $currentMonth = $this->request->getGet('month') ?? date('Y-m');
        $db = \Config\Database::connect();

        try {
            // Get availability for the month
            $availability = $db->table('staff_availability')
                ->where('staff_id', $staffId)
                ->where('date >=', $currentMonth . '-01')
                ->where('date <=', date('Y-m-t', strtotime($currentMonth . '-01')))
                ->get()
                ->getResultArray();

            // Get monthly requests
            $monthlyRequests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name')
                ->join('properties p', 'p.id = mr.property_id', 'left')
                ->where('mr.assigned_staff_id', $staffId)
                ->where('DATE_FORMAT(mr.assigned_date, "%Y-%m")', $currentMonth)
                ->orderBy('mr.assigned_date', 'ASC')
                ->get()
                ->getResultArray();

            $data = [
                'current_month' => $currentMonth,
                'availability' => $availability,
                'monthly_requests' => $monthlyRequests
            ];

            return view('maintenance/schedule', $data);

        } catch (\Exception $e) {
            log_message('error', 'Schedule error: ' . $e->getMessage());
            return view('maintenance/schedule', [
                'current_month' => $currentMonth,
                'availability' => [],
                'monthly_requests' => []
            ]);
        }
    }

    /**
     * Update availability
     */
    public function updateAvailability()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $staffId = $this->getCurrentUserId();

        try {
            $date = $this->request->getPost('date');
            $isAvailable = $this->request->getPost('is_available') ? 1 : 0;
            $notes = $this->request->getPost('notes') ?? '';

            if (!$date) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Date is required'
                ]);
            }

            $db = \Config\Database::connect();

            // Check if record exists
            $existing = $db->table('staff_availability')
                ->where(['staff_id' => $staffId, 'date' => $date])
                ->get()
                ->getRowArray();

            $data = [
                'staff_id' => $staffId,
                'date' => $date,
                'is_available' => $isAvailable,
                'notes' => $notes
            ];

            if ($existing) {
                $result = $db->table('staff_availability')
                    ->where('id', $existing['id'])
                    ->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $result = $db->table('staff_availability')->insert($data);
            }

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Availability updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update availability'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Update availability error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating availability'
            ]);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($staffId, $db)
    {
        $totalAssigned = $db->table('maintenance_requests')
            ->where('assigned_staff_id', $staffId)
            ->countAllResults();

        $completed = $db->table('maintenance_requests')
            ->where('assigned_staff_id', $staffId)
            ->where('status', 'completed')
            ->countAllResults();

        $todayWork = $db->table('maintenance_requests')
            ->where('assigned_staff_id', $staffId)
            ->where('DATE(assigned_date)', date('Y-m-d'))
            ->whereIn('status', ['approved', 'in_progress'])
            ->countAllResults();

        $inProgress = $db->table('maintenance_requests')
            ->where('assigned_staff_id', $staffId)
            ->where('status', 'in_progress')
            ->countAllResults();

        $urgent = $db->table('maintenance_requests')
            ->where('assigned_staff_id', $staffId)
            ->where('priority', 'urgent')
            ->whereIn('status', ['approved', 'in_progress'])
            ->countAllResults();

        // Calculate average completion time
        $avgQuery = $db->query("
            SELECT AVG(DATEDIFF(completed_date, assigned_date)) as avg_days 
            FROM maintenance_requests 
            WHERE assigned_staff_id = ? AND status = 'completed' AND completed_date IS NOT NULL AND assigned_date IS NOT NULL
        ", [$staffId]);
        
        $avgResult = $avgQuery->getRowArray();
        $avgCompletionTime = $avgResult ? round($avgResult['avg_days'], 1) : 0;

        return [
            'total_assigned' => $totalAssigned,
            'completed' => $completed,
            'today_work' => $todayWork,
            'in_progress' => $inProgress,
            'urgent' => $urgent,
            'avg_completion_time' => $avgCompletionTime
        ];
    }

    /**
     * Send help message to admin
     */
    public function sendHelpMessage()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $rules = [
            'subject' => 'required',
            'priority' => 'permit_empty|in_list[normal,high,urgent]',
            'message' => 'required|min_length[10]|max_length[2000]',
            'custom_subject' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $subject = $this->request->getPost('subject');
            if ($subject === 'Other') {
                $subject = trim((string) $this->request->getPost('custom_subject'));
                if ($subject === '') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Custom subject is required for "Other"'
                    ]);
                }
            }

            $userId = $this->getCurrentUserId();
            $user = $this->userModel->find($userId);

            // Use admin_messages table with placeholder landlord_id for maintenance messages
            $messageData = [
                'landlord_id' => 0, // placeholder for maintenance messages
                'landlord_name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'landlord_email' => $user['email'] ?? '',
                'subject' => $subject,
                'message' => $this->request->getPost('message'),
                'priority' => $this->request->getPost('priority') ?: 'normal',
                'status' => 'unread',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $db = \Config\Database::connect();
            $result = $db->table('admin_messages')->insert($messageData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Your message has been sent. We will get back to you shortly.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Could not send your message. Please try again.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Send help message error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while sending your message'
            ]);
        }
    }

    /**
     * Auto-reject stale requests (CLI/Admin only)
     */
    public function autoRejectStaleRequests()
    {
        // Allow CLI and admin users only
        if (!$this->request->isCLI() && !$this->isCurrentUserAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        try {
            $db = \Config\Database::connect();
            $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

            // Find pending requests older than 7 days
            $staleRequests = $db->table('maintenance_requests')
                ->where('status', 'pending')
                ->where('requested_date <', $sevenDaysAgo)
                ->get()
                ->getResultArray();

            $rejectedCount = 0;
            foreach ($staleRequests as $request) {
                $updated = $db->table('maintenance_requests')
                    ->where('id', $request['id'])
                    ->update([
                        'status' => 'rejected',
                        'rejection_reason' => 'Automatically rejected - No maintenance staff response after 7 days',
                        'rejected_date' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                if ($updated) {
                    $rejectedCount++;
                }
            }

            if ($this->request->isCLI()) {
                echo "Auto-rejected {$rejectedCount} stale maintenance requests\n";
            } else {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Auto-rejected {$rejectedCount} stale requests"
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Auto-reject stale requests error: ' . $e->getMessage());
            if ($this->request->isCLI()) {
                echo "Error: " . $e->getMessage() . "\n";
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error occurred during auto-rejection'
                ]);
            }
        }
    }

    /**
     * Profile management
     */
    public function profile()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);

        return view('maintenance/profile', ['user' => $user]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        $redirect = $this->requireMaintenance();
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
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        try {
            $updateData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->userModel->update($userId, $updateData)) {
                // Update session data
                session()->set([
                    'full_name' => trim($updateData['first_name'] . ' ' . $updateData['last_name']),
                    'email' => $updateData['email']
                ]);

                return redirect()->to('maintenance/profile')->with('success', 'Profile updated successfully');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update profile');
            }
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating profile');
        }
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();

        if ($this->request->isAJAX()) {
            try {
                $rules = [
                    'current_password' => 'required',
                    'new_password' => 'required|min_length[6]',
                    'confirm_password' => 'required|matches[new_password]'
                ];

                if (!$this->validate($rules)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $this->validator->getErrors()
                    ]);
                }

                // Verify current password
                $user = $this->userModel->find($userId);
                if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ]);
                }

                $updateData = [
                    'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->userModel->update($userId, $updateData)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Password changed successfully'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to change password'
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Change password error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while changing password'
                ]);
            }
        }

        // Non-AJAX fallback
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('validation', $this->validator);
        }

        // Verify current password
        $user = $this->userModel->find($userId);
        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }

        $updateData = [
            'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->update($userId, $updateData)) {
            return redirect()->to('maintenance/profile')->with('success', 'Password changed successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to change password');
        }
    }

    /**
     * Set availability (alternative endpoint)
     */
    public function setAvailability()
    {
        return $this->updateAvailability();
    }

    /**
     * Help endpoint (alias for sendHelpMessage)
     */
    public function help()
    {
        return $this->sendHelpMessage();
    }

    // Helper methods

    /**
     * Get current user ID from session
     */
    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    /**
     * Require maintenance staff authentication
     */
    protected function requireMaintenance()
    {
        // Check if user is logged in
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page');
        }

        // Check if user has maintenance role
        if (session()->get('role') !== 'maintenance') {
            $userRole = session()->get('role');
            $dashboardUrl = $this->getDashboardUrl($userRole);
            return redirect()->to($dashboardUrl)->with('error', 'Access denied. Maintenance privileges required.');
        }

        return null;
    }

    /**
     * Get dashboard URL based on role
     */
    protected function getDashboardUrl($role)
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'landlord':
                return '/landlord/dashboard';
            case 'maintenance':
                return '/maintenance/dashboard';
            default:
                return '/auth/login';
        }
    }

    /**
     * Check if current user is admin
     */
    private function isCurrentUserAdmin()
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return false;
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        return $user && $user['role'] === 'admin';
    }

    /**
     * Get valid status transitions for a given current status
     */
    private function getValidStatusTransitions($currentStatus)
    {
        $transitions = [
            'pending' => ['approved'], // Maintenance staff can approve pending requests
            'approved' => ['in_progress', 'cancelled'], // Can start work or cancel
            'in_progress' => ['completed'], // Can only complete once started
            'completed' => [], // Final status
            'rejected' => [], // Final status
            'cancelled' => [] // Returns to pending automatically
        ];

        return $transitions[$currentStatus] ?? [];
    }
}