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
     * Maintenance Dashboard
     */
    public function dashboard()
    {
        // Check maintenance access
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();

        // Get assigned requests
        $assignedRequests = $this->maintenanceModel->getRequestsByStaff($staffId);
        
        // Get today's work
        $todayRequests = array_filter($assignedRequests, function($request) {
            return in_array($request['status'], ['assigned', 'in_progress']) && 
                   date('Y-m-d') >= date('Y-m-d', strtotime($request['assigned_date']));
        });

        // Get urgent requests
        $urgentRequests = array_filter($assignedRequests, function($request) {
            return $request['priority'] === 'urgent' && $request['status'] !== 'completed';
        });

        // Calculate statistics
        $stats = $this->getMaintenanceStats($staffId);

        $data = [
            'title' => 'Maintenance Dashboard',
            'assigned_requests' => $assignedRequests,
            'today_requests' => array_values($todayRequests),
            'urgent_requests' => array_values($urgentRequests),
            'stats' => $stats
        ];

        return view('maintenance/dashboard', $data);
    }

    /**
     * View All Requests
     */
    public function requests()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        $status = $this->request->getGet('status');
        $priority = $this->request->getGet('priority');

        $requests = $this->maintenanceModel->getRequestsByStaff($staffId, $status);

        // Filter by priority if requested
        if ($priority) {
            $requests = array_filter($requests, function($request) use ($priority) {
                return $request['priority'] === $priority;
            });
        }

        $data = [
            'title' => 'Work Orders',
            'requests' => array_values($requests),
            'current_status' => $status,
            'current_priority' => $priority
        ];

        return view('maintenance/requests', $data);
    }

    /**
     * View Single Request
     */
    public function viewRequest($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        // Get request details and verify assignment
        $requests = $this->maintenanceModel->getRequestsByStaff($staffId);
        $request = null;
        
        foreach ($requests as $r) {
            if ($r['id'] == $requestId) {
                $request = $r;
                break;
            }
        }

        if (!$request) {
            $this->setError('Request not found or not assigned to you');
            return redirect()->to('/maintenance/requests');
        }

        // Get images for this request
        $db = \Config\Database::connect();
        $images = $db->table('maintenance_images')
                    ->where('maintenance_request_id', $requestId)
                    ->get()
                    ->getResultArray();

        $data = [
            'title' => 'Work Order Details',
            'request' => $request,
            'images' => $images
        ];

        return view('maintenance/request_view', $data);
    }

    /**
     * Update Request Status
     */
    public function updateStatus($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        // Verify request is assigned to this staff
        $request = $this->verifyRequestAssignment($requestId, $staffId);
        if (!$request) {
            return $this->respondWithError('Request not found or not assigned to you');
        }

        $rules = [
            'status' => 'required|in_list[in_progress,completed,cancelled]',
            'work_notes' => 'max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError('Validation failed', 400);
        }

        $status = $this->request->getPost('status');
        $workNotes = $this->request->getPost('work_notes');

        $updateData = [
            'status' => $status,
            'work_notes' => $workNotes
        ];

        // Set completion date if completing
        if ($status === 'completed') {
            $updateData['completed_date'] = date('Y-m-d H:i:s');
            
            // Add actual cost if provided
            $actualCost = $this->request->getPost('actual_cost');
            if ($actualCost && is_numeric($actualCost)) {
                $updateData['actual_cost'] = $actualCost;
            }

            // Add materials used if provided
            $materialsUsed = $this->request->getPost('materials_used');
            if ($materialsUsed) {
                $updateData['materials_used'] = $materialsUsed;
            }
        }

        if ($this->maintenanceModel->update($requestId, $updateData)) {
            return $this->respondWithSuccess([], 'Status updated successfully');
        } else {
            return $this->respondWithError('Failed to update status');
        }
    }

    /**
     * Complete Request
     */
    public function completeRequest($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        // Verify request is assigned to this staff
        $request = $this->verifyRequestAssignment($requestId, $staffId);
        if (!$request) {
            return $this->respondWithError('Request not found or not assigned to you');
        }

        $rules = [
            'actual_cost' => 'decimal|greater_than_equal_to[0]',
            'materials_used' => 'max_length[500]',
            'completion_notes' => 'required|min_length[10]|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError('Validation failed', 400);
        }

        $updateData = [
            'status' => 'completed',
            'completed_date' => date('Y-m-d H:i:s'),
            'actual_cost' => $this->request->getPost('actual_cost') ?? 0,
            'materials_used' => $this->request->getPost('materials_used'),
            'work_notes' => $this->request->getPost('completion_notes')
        ];

        if ($this->maintenanceModel->update($requestId, $updateData)) {
            return $this->respondWithSuccess([], 'Request marked as completed');
        } else {
            return $this->respondWithError('Failed to complete request');
        }
    }

    /**
     * Upload Image
     */
    public function uploadImage($requestId)
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        // Verify request is assigned to this staff
        $request = $this->verifyRequestAssignment($requestId, $staffId);
        if (!$request) {
            return $this->respondWithError('Request not found or not assigned to you');
        }

        $imageFile = $this->request->getFile('image');
        if (!$imageFile || !$imageFile->isValid()) {
            return $this->respondWithError('No valid image file provided');
        }

        try {
            $imagePath = $this->handleFileUpload('image', 'maintenance/', ['jpg', 'jpeg', 'png']);
            
            if ($imagePath) {
                $db = \Config\Database::connect();
                $imageData = [
                    'maintenance_request_id' => $requestId,
                    'image_path' => $imagePath,
                    'image_type' => $this->request->getPost('image_type') ?? 'after',
                    'description' => $this->request->getPost('description')
                ];

                if ($db->table('maintenance_images')->insert($imageData)) {
                    return $this->respondWithSuccess(['image_path' => $imagePath], 'Image uploaded successfully');
                } else {
                    return $this->respondWithError('Failed to save image record');
                }
            } else {
                return $this->respondWithError('Failed to upload image');
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Upload error: ' . $e->getMessage());
        }
    }

    /**
     * View Schedule
     */
    public function schedule()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        // Get current month's availability
        $currentMonth = date('Y-m');
        $db = \Config\Database::connect();
        
        $availability = $db->table('staff_availability')
                          ->where('staff_id', $staffId)
                          ->where('date >=', $currentMonth . '-01')
                          ->where('date <=', $currentMonth . '-31')
                          ->get()
                          ->getResultArray();

        // Get scheduled requests for this month
        $scheduledRequests = $this->maintenanceModel->getRequestsByStaff($staffId);
        $monthlyRequests = array_filter($scheduledRequests, function($request) use ($currentMonth) {
            return $request['assigned_date'] && 
                   strpos($request['assigned_date'], $currentMonth) === 0;
        });

        $data = [
            'title' => 'My Schedule',
            'availability' => $availability,
            'monthly_requests' => array_values($monthlyRequests),
            'current_month' => $currentMonth
        ];

        return view('maintenance/schedule', $data);
    }

    /**
     * Update Schedule
     */
    public function updateSchedule()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $staffId = $this->getCurrentUserId();
        
        $rules = [
            'date' => 'required|valid_date',
            'is_available' => 'required|in_list[0,1]',
            'notes' => 'max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError('Validation failed', 400);
        }

        $date = $this->request->getPost('date');
        $isAvailable = $this->request->getPost('is_available');
        $notes = $this->request->getPost('notes');

        $db = \Config\Database::connect();
        
        // Check if availability record exists
        $existing = $db->table('staff_availability')
                      ->where('staff_id', $staffId)
                      ->where('date', $date)
                      ->get()
                      ->getRowArray();

        $availabilityData = [
            'staff_id' => $staffId,
            'date' => $date,
            'is_available' => $isAvailable,
            'notes' => $notes
        ];

        if ($existing) {
            $result = $db->table('staff_availability')
                        ->where('staff_id', $staffId)
                        ->where('date', $date)
                        ->update($availabilityData);
        } else {
            $result = $db->table('staff_availability')->insert($availabilityData);
        }

        if ($result) {
            return $this->respondWithSuccess([], 'Schedule updated successfully');
        } else {
            return $this->respondWithError('Failed to update schedule');
        }
    }

    /**
     * View Profile
     */
    public function profile()
    {
        $redirect = $this->requireMaintenance();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($this->getCurrentUserId());

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('maintenance/profile', $data);
    }

    /**
     * Update Profile
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

        // Add password validation only if password is provided
        if (!empty($this->request->getPost('password'))) {
            $rules['password'] = 'min_length[6]';
            $rules['confirm_password'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address')
        ];

        // Add password only if provided
        if (!empty($this->request->getPost('password'))) {
            $updateData['password'] = $this->request->getPost('password');
        }

        if ($this->userModel->update($userId, $updateData)) {
            // Update session data
            session()->set('full_name', $updateData['first_name'] . ' ' . $updateData['last_name']);
            session()->set('email', $updateData['email']);
            
            $this->setSuccess('Profile updated successfully');
        } else {
            $this->setError('Failed to update profile');
        }

        return redirect()->to('/maintenance/profile');
    }

    /**
     * Get Maintenance Statistics
     */
    private function getMaintenanceStats($staffId)
    {
        $requests = $this->maintenanceModel->getRequestsByStaff($staffId);

        $stats = [
            'total_assigned' => count($requests),
            'pending' => count(array_filter($requests, function($r) { return $r['status'] === 'pending'; })),
            'in_progress' => count(array_filter($requests, function($r) { return $r['status'] === 'in_progress'; })),
            'completed' => count(array_filter($requests, function($r) { return $r['status'] === 'completed'; })),
            'urgent' => count(array_filter($requests, function($r) { return $r['priority'] === 'urgent' && $r['status'] !== 'completed'; })),
            'today_work' => 0,
            'avg_completion_time' => 0
        ];

        // Count today's work
        $today = date('Y-m-d');
        $stats['today_work'] = count(array_filter($requests, function($request) use ($today) {
            return in_array($request['status'], ['assigned', 'in_progress']) && 
                   $request['assigned_date'] && 
                   date('Y-m-d', strtotime($request['assigned_date'])) <= $today;
        }));

        // Calculate average completion time for completed requests
        $completedRequests = array_filter($requests, function($r) {
            return $r['status'] === 'completed' && $r['completed_date'] && $r['requested_date'];
        });

        if (!empty($completedRequests)) {
            $totalDays = 0;
            foreach ($completedRequests as $request) {
                $requestedDate = strtotime($request['requested_date']);
                $completedDate = strtotime($request['completed_date']);
                $days = ($completedDate - $requestedDate) / (60 * 60 * 24);
                $totalDays += $days;
            }
            $stats['avg_completion_time'] = round($totalDays / count($completedRequests), 1);
        }

        return $stats;
    }

    /**
     * Verify Request Assignment
     */
    private function verifyRequestAssignment($requestId, $staffId)
    {
        $db = \Config\Database::connect();
        $request = $db->table('maintenance_requests')
                     ->where('id', $requestId)
                     ->where('assigned_staff_id', $staffId)
                     ->get()
                     ->getRowArray();

        return $request;
    }
}