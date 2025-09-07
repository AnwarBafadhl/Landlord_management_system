<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\CLI\CLI;

class Landlord extends BaseController
{
    protected $userModel;
    protected $propertyModel;
    protected $paymentModel;
    protected $maintenanceModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->propertyModel = new PropertyModel();
        $this->paymentModel = new PaymentModel();
        $this->maintenanceModel = new MaintenanceRequestModel();
    }

    //DASHBOARD PAGE METHODS
    public function dashboard()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $this->ensureRemainingBalanceColumn();
            $db = \Config\Database::connect();

            $properties = $this->getLandlordProperties($landlordId);
            $properties_count = count($properties);
            $maintenance_stats = $this->getMaintenanceStatsForDashboard($landlordId);

            // FIXED: Use database values for total remaining balance
            $total_remaining_balance = 0;
            foreach ($properties as $property) {
                $total_remaining_balance += (float) ($property['remaining_balance'] ?? 0);
            }

            $data = [
                'title' => 'Landlord Dashboard',
                'properties_count' => $properties_count,
                'total_remaining_balance' => $total_remaining_balance,
                'properties' => $properties,
                'maintenance_stats' => $maintenance_stats,
            ];

            return view('landlord/dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());

            return view('landlord/dashboard', [
                'title' => 'Dashboard',
                'properties_count' => 0,
                'total_remaining_balance' => 0,
                'properties' => [],
                'maintenance_stats' => [
                    'completed_count' => 0,
                    'approved_count' => 0,
                    'pending_count' => 0,
                    'in_progress_count' => 0
                ]
            ]);
        }
    }

    private function getMaintenanceStatsForDashboard($landlordId)
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('maintenance_requests')) {
                return [
                    'completed_count' => 0,
                    'approved_count' => 0,
                    'pending_count' => 0,
                    'in_progress_count' => 0
                ];
            }

            $stats = $db->table('maintenance_requests mr')
                ->select('mr.status, COUNT(*) as count')
                ->join('property_shareholders ps', 'ps.property_id = mr.property_id')
                ->where('ps.user_id', $landlordId)
                ->where('ps.status', 'active')
                ->groupBy('mr.status')
                ->get()
                ->getResultArray();

            $result = [
                'completed_count' => 0,
                'approved_count' => 0,
                'pending_count' => 0,
                'in_progress_count' => 0
            ];

            foreach ($stats as $stat) {
                $key = $stat['status'] . '_count';
                if (isset($result[$key])) {
                    $result[$key] = (int) $stat['count'];
                }
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', 'Error getting maintenance stats: ' . $e->getMessage());
            return [
                'completed_count' => 0,
                'approved_count' => 0,
                'pending_count' => 0,
                'in_progress_count' => 0
            ];
        }
    }

    //END DASHBOARD PAGE

    //PROPERTIES PAGE METHODS
    public function properties()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $this->ensureRemainingBalanceColumn();
            $properties = $this->getLandlordProperties($landlordId);
            $total_properties = count($properties);

            // FIXED: Use database values for total remaining balance
            $total_remaining_balance = 0;
            foreach ($properties as $property) {
                $total_remaining_balance += (float) ($property['remaining_balance'] ?? 0);
            }

            $total_units = 0;
            foreach ($properties as &$property) {
                $units_count = $this->getPropertyUnitsCount($property['id']);
                $total_units += $units_count;
                $property['total_units'] = $units_count;
                $property['my_investment'] = ($property['shares'] ?? 0) * ($property['property_value'] ?? 0) / ($property['total_shares'] ?? 1);
                $property['total_owners'] = $this->getPropertyOwnersCount($property['id']);
            }

            return view('landlord/properties', [
                'title' => 'My Properties',
                'properties' => $properties,
                'summary' => [
                    'total_properties' => $total_properties,
                    'total_remaining_balance' => $total_remaining_balance,
                    'total_units' => $total_units
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Properties list error: ' . $e->getMessage());
            return view('landlord/properties', [
                'title' => 'My Properties',
                'properties' => [],
                'summary' => [
                    'total_properties' => 0,
                    'total_remaining_balance' => 0,
                    'total_units' => 0
                ]
            ]);
        }
    }

    public function requestProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $currentUser = $this->userModel->find($this->getCurrentUserId());

        return view('landlord/request_property', [
            'title' => 'Add New Property',
            'validation' => \Config\Services::validation(),
            'currentUser' => $currentUser,
        ]);
    }

    public function viewProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                $this->setError('Property not found or access denied');
                return redirect()->to('/landlord/properties');
            }

            $property = $this->propertyModel->find($propertyId);
            $owners = $this->getPropertyShareholdersWithLabels($propertyId, $landlordId);
            $units = $this->getPropertyUnits($propertyId);
            $unitCount = count($units);
            $totalAllocatedShares = array_sum(array_column($owners, 'shares'));

            return view('landlord/property_details', [
                'title' => 'Property Details',
                'property' => $property,
                'owners' => $owners,
                'units' => $units,
                'unitCount' => $unitCount,
                'totalAllocatedShares' => $totalAllocatedShares,
                'isCreator' => $this->isPropertyCreator($propertyId, $landlordId),
                'currentUserId' => $landlordId
            ]);

        } catch (\Exception $e) {
            log_message('error', 'View property error: ' . $e->getMessage());
            $this->setError('Failed to load property details');
            return redirect()->to('/landlord/properties');
        }
    }

    public function editProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                $this->setError('Property not found or access denied');
                return redirect()->to('/landlord/properties');
            }

            $property = $this->propertyModel->find($propertyId);
            $owners = $this->getPropertyShareholdersForEdit($propertyId, $landlordId);
            $units = $this->getPropertyUnitsForEdit($propertyId);
            $totalAllocatedShares = array_sum(array_column($owners, 'shares'));

            return view('landlord/edit_property', [
                'title' => 'Edit Property - ' . $property['property_name'],
                'property' => $property,
                'owners' => $owners,
                'totalAllocatedShares' => $totalAllocatedShares,
                'units' => $units,
                'validation' => \Config\Services::validation()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Edit property error: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            $this->setError('Failed to load property for editing: ' . $e->getMessage());
            return redirect()->to('/landlord/properties');
        }
    }

    public function updateProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        log_message('debug', "Update Property called - PropertyID: {$propertyId}, LandlordID: {$landlordId}");

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to edit it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'property_value' => 'required|decimal|greater_than[0]',
            'property_address' => 'required|min_length[10]',
            'total_shares' => 'required|integer|greater_than[0]|less_than_equal_to[10000]',
            'contribution_duration' => 'required|integer|greater_than[0]|less_than_equal_to[360]',
            'management_company' => 'permit_empty|min_length[3]|max_length[100]',
            'management_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[50]'
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'Validation failed: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $propertyValue = (float) $this->request->getPost('property_value');
            $totalShares = (int) $this->request->getPost('total_shares');
            $shareValue = $propertyValue / $totalShares;

            if ($db->tableExists('property_shareholders')) {
                $allocatedShares = $db->table('property_shareholders')
                    ->where('property_id', $propertyId)
                    ->selectSum('shares')
                    ->get()
                    ->getRow()
                    ->shares ?? 0;

                if ($totalShares < $allocatedShares) {
                    throw new \Exception("Cannot reduce total shares below allocated shares ({$allocatedShares})");
                }
            }

            $propertyData = [
                'property_name' => $this->request->getPost('property_name'),
                'property_value' => $propertyValue,
                'address' => $this->request->getPost('property_address'),
                'total_shares' => $totalShares,
                'share_value' => $shareValue,
                'contribution_duration' => $this->request->getPost('contribution_duration'),
                'management_company' => $this->request->getPost('management_company') ?: null,
                'management_percentage' => $this->request->getPost('management_percentage') ?: 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('debug', "Updating property with data: " . json_encode($propertyData));

            if (!$this->propertyModel->update($propertyId, $propertyData)) {
                throw new \Exception('Failed to update property in database');
            }

            // Recalculate and update balance after property update
            $this->updatePropertyBalanceDirectly($propertyId, $db);

            if ($db->tableExists('property_shareholders')) {
                $shareholders = $db->table('property_shareholders')
                    ->where('property_id', $propertyId)
                    ->get()
                    ->getResultArray();

                foreach ($shareholders as $shareholder) {
                    $newPercentage = ($shareholder['shares'] / $totalShares) * 100;
                    $db->table('property_shareholders')
                        ->where('id', $shareholder['id'])
                        ->update(['ownership_percentage' => $newPercentage]);
                }

                log_message('debug', "Updated ownership percentages for " . count($shareholders) . " shareholders");
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('debug', "Property update successful");
            $this->setSuccess('Property updated successfully!');
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Update property error: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            $this->setError('Failed to update property: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // FIXED: Dynamic redirection based on referrer
    public function addPropertyOwner($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination based on referrer
        $redirectTo = $this->getRedirectDestination($propertyId);

        // Validation rules
        $rules = [
            'owner_name' => 'required|min_length[3]|max_length[255]',
            'owner_email' => 'required|valid_email|max_length[255]',
            'owner_shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Validation failed: ' . implode(', ', $this->validator->getErrors()));
            return redirect()->to($redirectTo)->withInput()->with('validation', $this->validator);
        }

        try {
            $db = \Config\Database::connect();
            $property = $this->propertyModel->find($propertyId);

            if (!$property) {
                throw new \Exception('Property not found');
            }

            // Check available shares
            $this->createPropertyShareholdersTable($db);
            $currentShares = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->selectSum('shares')
                ->get()
                ->getRow()
                ->shares ?? 0;

            $newShares = (int) $this->request->getPost('owner_shares');
            $availableShares = $property['total_shares'] - $currentShares;

            if ($newShares > $availableShares) {
                $this->setError("Cannot add owner. Only {$availableShares} shares are available.");
                return redirect()->to($redirectTo)->withInput();
            }

            // Check if user exists
            $email = trim($this->request->getPost('owner_email'));
            $existingUser = $this->userModel->where('email', $email)->first();

            $ownerData = [
                'property_id' => $propertyId,
                'user_id' => $existingUser ? $existingUser['id'] : null,
                'owner_name' => trim($this->request->getPost('owner_name')),
                'owner_email' => $email,
                'shares' => $newShares,
                'ownership_percentage' => ($newShares / $property['total_shares']) * 100,
                'is_primary_owner' => 0,
                'status' => $existingUser ? 'active' : 'pending',
                'invited_by' => $landlordId,
                'joined_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d')
            ];

            if ($db->table('property_shareholders')->insert($ownerData)) {
                // Send notification email if user doesn't exist
                if (!$existingUser) {
                    $this->sendShareholderNotificationEmail(
                        $email,
                        $ownerData['owner_name'],
                        $property['property_name']
                    );
                }

                $this->setSuccess('Owner added successfully!');
            } else {
                throw new \Exception('Failed to add owner');
            }

        } catch (\Exception $e) {
            log_message('error', 'Add property owner error: ' . $e->getMessage());
            $this->setError('Failed to add owner: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    // FIXED: Update Property Owner with dynamic redirection
    public function updatePropertyOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination
        $redirectTo = $this->getRedirectDestination($propertyId);

        $rules = [
            'shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Validation failed: ' . implode(', ', $this->validator->getErrors()));
            return redirect()->to($redirectTo);
        }

        try {
            $db = \Config\Database::connect();
            $property = $this->propertyModel->find($propertyId);

            if (!$property) {
                throw new \Exception('Property not found');
            }

            // Get current owner info
            $currentOwner = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->where('property_id', $propertyId)
                ->get()
                ->getRowArray();

            if (!$currentOwner) {
                throw new \Exception('Owner not found');
            }

            // Check available shares (excluding current owner's shares)
            $otherShares = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->where('id !=', $ownerId)
                ->selectSum('shares')
                ->get()
                ->getRow()
                ->shares ?? 0;

            $newShares = (int) $this->request->getPost('shares');
            $maxAllowedShares = $property['total_shares'] - $otherShares;

            if ($newShares > $maxAllowedShares) {
                $this->setError("Cannot update owner. Maximum allowed shares: {$maxAllowedShares}");
                return redirect()->to($redirectTo);
            }

            $ownerData = [
                'shares' => $newShares,
                'ownership_percentage' => ($newShares / $property['total_shares']) * 100,
                'updated_at' => date('Y-m-d')
            ];

            $updated = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->where('property_id', $propertyId)
                ->update($ownerData);

            if ($updated) {
                $this->setSuccess('Owner updated successfully');
            } else {
                throw new \Exception('No changes made or owner not found');
            }

        } catch (\Exception $e) {
            log_message('error', 'Update property owner error: ' . $e->getMessage());
            $this->setError('Failed to update owner: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    // FIXED: Remove Property Owner with dynamic redirection
    public function removePropertyOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination
        $redirectTo = $this->getRedirectDestination($propertyId);

        try {
            $db = \Config\Database::connect();

            // Get owner info
            $owner = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->where('property_id', $propertyId)
                ->get()
                ->getRowArray();

            if (!$owner) {
                throw new \Exception('Owner not found');
            }

            // Cannot remove primary owner
            if ($owner['is_primary_owner'] == 1) {
                $this->setError('Cannot remove primary owner');
                return redirect()->to($redirectTo);
            }

            // Cannot remove current user if they are primary owner
            if ($owner['user_id'] == $landlordId && $owner['is_primary_owner'] == 1) {
                $this->setError('You cannot remove yourself as primary owner');
                return redirect()->to($redirectTo);
            }

            $deleted = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->where('property_id', $propertyId)
                ->where('is_primary_owner', 0) // Safety check
                ->delete();

            if ($deleted) {
                $this->setSuccess('Owner removed successfully');
            } else {
                throw new \Exception('Owner not found or cannot be removed');
            }

        } catch (\Exception $e) {
            log_message('error', 'Remove property owner error: ' . $e->getMessage());
            $this->setError('Failed to remove owner: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    // FIXED: Property Unit methods with dynamic redirection
    public function addPropertyUnit($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination
        $redirectTo = $this->getRedirectDestination($propertyId);

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Invalid unit name');
            return redirect()->to($redirectTo)->withInput();
        }

        try {
            $db = \Config\Database::connect();
            $this->createPropertyUnitsTable($db);

            $unitData = [
                'property_id' => $propertyId,
                'unit_name' => trim($this->request->getPost('unit_name')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('property_units')->insert($unitData)) {
                $this->setSuccess('Unit added successfully');
            } else {
                throw new \Exception('Failed to add unit');
            }

        } catch (\Exception $e) {
            log_message('error', 'Add property unit error: ' . $e->getMessage());
            $this->setError('Failed to add unit: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    public function updatePropertyUnit($propertyId, $unitId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination
        $redirectTo = $this->getRedirectDestination($propertyId);

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Invalid unit name');
            return redirect()->to($redirectTo);
        }

        try {
            $db = \Config\Database::connect();

            $unitData = [
                'unit_name' => trim($this->request->getPost('unit_name')),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $db->table('property_units')
                ->where('id', $unitId)
                ->where('property_id', $propertyId)
                ->update($unitData);

            if ($updated) {
                $this->setSuccess('Unit updated successfully');
            } else {
                throw new \Exception('Unit not found or no changes made');
            }

        } catch (\Exception $e) {
            log_message('error', 'Update property unit error: ' . $e->getMessage());
            $this->setError('Failed to update unit: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    public function removePropertyUnit($propertyId, $unitId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            $this->setError('Unauthorized access');
            return redirect()->to('/landlord/properties');
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or access denied');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Determine redirect destination
        $redirectTo = $this->getRedirectDestination($propertyId);

        try {
            $db = \Config\Database::connect();

            // Check if unit is being used
            $usageCount = 0;

            if ($db->tableExists('income_expense_payments')) {
                $usageCount += $db->table('income_expense_payments')
                    ->where('unit_id', $unitId)
                    ->countAllResults();
            }

            if ($db->tableExists('maintenance_requests')) {
                $usageCount += $db->table('maintenance_requests')
                    ->where('unit_id', $unitId)
                    ->countAllResults();
            }

            if ($usageCount > 0) {
                $this->setError('Cannot delete unit. It has associated transactions or maintenance records.');
                return redirect()->to($redirectTo);
            }

            $deleted = $db->table('property_units')
                ->where('id', $unitId)
                ->where('property_id', $propertyId)
                ->delete();

            if ($deleted) {
                $this->setSuccess('Unit removed successfully');
            } else {
                throw new \Exception('Unit not found or already deleted');
            }

        } catch (\Exception $e) {
            log_message('error', 'Remove property unit error: ' . $e->getMessage());
            $this->setError('Failed to remove unit: ' . $e->getMessage());
        }

        return redirect()->to($redirectTo);
    }

    // HELPER METHOD: Determine where to redirect based on HTTP referrer
    private function getRedirectDestination($propertyId)
    {
        $referer = $this->request->getServer('HTTP_REFERER');

        // Default fallback
        $defaultRedirect = "/landlord/properties/view/{$propertyId}";

        if (empty($referer)) {
            return $defaultRedirect;
        }

        // Check if the referrer contains 'edit'
        if (strpos($referer, '/edit/') !== false || strpos($referer, 'edit-property') !== false) {
            return "/landlord/properties/edit/{$propertyId}";
        }

        // Check if the referrer contains 'view' 
        if (strpos($referer, '/view/') !== false || strpos($referer, 'property-details') !== false) {
            return "/landlord/properties/view/{$propertyId}";
        }

        // Default to view if we can't determine
        return $defaultRedirect;
    }

    // ALTERNATIVE METHOD: Using explicit redirect parameter in forms
    private function getRedirectDestinationFromForm($propertyId)
    {
        $redirectParam = $this->request->getPost('redirect_to');

        switch ($redirectParam) {
            case 'edit':
                return "/landlord/properties/edit/{$propertyId}";
            case 'view':
                return "/landlord/properties/view/{$propertyId}";
            default:
                return "/landlord/properties/view/{$propertyId}";
        }
    }

    // FIX FOR PROPERTY INSERTION ISSUE - UPDATE YOUR addProperty METHOD

    public function addProperty()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'property_value' => 'required|decimal|greater_than[0]',
            'property_address' => 'required|min_length[3]',
            'total_shares' => 'required|integer|greater_than[0]|less_than_equal_to[10000]',
            'contribution_duration' => 'required|integer|greater_than[0]|less_than_equal_to[360]',
            'management_company' => 'permit_empty|min_length[3]|max_length[100]',
            'management_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[50]',
            'total_units' => 'required|integer|greater_than[0]|less_than_equal_to[500]',
            'primary_owner_name' => 'required|min_length[3]|max_length[100]',
            'primary_owner_shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please fill all required fields correctly.');
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $db = \Config\Database::connect();

        try {
            $db->transBegin();

            $propertyValue = (float) $this->request->getPost('property_value');
            $totalShares = (int) $this->request->getPost('total_shares');
            $primaryOwnerShares = (int) $this->request->getPost('primary_owner_shares');

            // Validate primary owner shares don't exceed total
            if ($primaryOwnerShares > $totalShares) {
                throw new \Exception('Primary owner shares cannot exceed total shares');
            }

            $shareValue = $totalShares > 0 ? $propertyValue / $totalShares : 0;

            $mgmtCompany = trim((string) $this->request->getPost('management_company'));
            $mgmtPct = (float) ($this->request->getPost('management_percentage') ?? 0);

            if (empty($mgmtCompany)) {
                $mgmtCompany = 'Self-Managed';
                $mgmtPct = 0;
            }

            // FIXED: Ensure all required fields are present and properly typed
            $propertyData = [
                'property_name' => trim($this->request->getPost('property_name')),
                'property_value' => $propertyValue,
                'address' => trim($this->request->getPost('property_address')),
                'total_shares' => $totalShares,
                'share_value' => $shareValue,
                'contribution_duration' => (int) $this->request->getPost('contribution_duration'),
                'management_company' => $mgmtCompany,
                'management_percentage' => $mgmtPct,
                'total_units' => (int) $this->request->getPost('total_units'),
                'remaining_balance' => 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // DEBUG: Log the data being inserted
            log_message('debug', 'Property data to insert: ' . json_encode($propertyData));

            // FIXED: Use direct database insert instead of model to avoid validation issues
            $propertyInserted = $db->table('properties')->insert($propertyData);

            if (!$propertyInserted) {
                $error = $db->error();
                log_message('error', 'Property insert failed: ' . json_encode($error));
                throw new \Exception('Failed to insert property: ' . ($error['message'] ?? 'Unknown database error'));
            }

            $propertyId = $db->insertID();
            log_message('debug', 'Property inserted with ID: ' . $propertyId);

            // Continue with shareholders and units...
            $this->createPropertyShareholdersTable($db);

            $primaryOwnerName = trim($this->request->getPost('primary_owner_name'));
            $currentUser = $this->userModel->find($landlordId);

            $primaryShareholderData = [
                'property_id' => $propertyId,
                'user_id' => $landlordId,
                'owner_name' => $primaryOwnerName,
                'owner_email' => $currentUser['email'] ?? '',
                'shares' => $primaryOwnerShares,
                'ownership_percentage' => ($primaryOwnerShares / $totalShares) * 100,
                'is_primary_owner' => 1,
                'status' => 'active',
                'joined_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d')
            ];

            if (!$db->table('property_shareholders')->insert($primaryShareholderData)) {
                throw new \Exception('Failed to add primary shareholder');
            }

            // Handle additional shareholders
            $shareholders = $this->request->getPost('shareholders');
            if (is_array($shareholders)) {
                foreach ($shareholders as $shareholder) {
                    if (empty($shareholder['name']) || empty($shareholder['email']) || empty($shareholder['shares'])) {
                        continue;
                    }

                    $shareholderShares = (int) $shareholder['shares'];
                    $shareholderData = [
                        'property_id' => $propertyId,
                        'user_id' => null,
                        'owner_name' => trim($shareholder['name']),
                        'owner_email' => trim($shareholder['email']),
                        'shares' => $shareholderShares,
                        'ownership_percentage' => ($shareholderShares / $totalShares) * 100,
                        'is_primary_owner' => 0,
                        'status' => 'pending',
                        'invited_by' => $landlordId,
                        'joined_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d')
                    ];

                    $db->table('property_shareholders')->insert($shareholderData);

                    // Send notification email
                    $this->sendShareholderNotificationEmail(
                        $shareholder['email'],
                        $shareholder['name'],
                        $propertyData['property_name']
                    );
                }
            }

            // Handle units
            $this->createPropertyUnitsTable($db);

            $unitNames = $this->request->getPost('unit_names');
            if (is_array($unitNames) && !empty($unitNames)) {
                $unitRows = [];
                foreach ($unitNames as $unitName) {
                    $unitName = trim((string) $unitName);
                    if (!empty($unitName)) {
                        $unitRows[] = [
                            'property_id' => $propertyId,
                            'unit_name' => $unitName,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }

                if (!empty($unitRows)) {
                    $db->table('property_units')->insertBatch($unitRows);
                }
            }

            $db->transCommit();
            log_message('debug', 'Property creation transaction completed successfully');

            $this->setSuccess('Property added successfully!');
            return redirect()->to('/landlord/properties');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Add property error: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            $this->setError('Failed to add property: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    //END PROPERTIES PAGE METHODS

    //PAYMENT PAGE METHODS - SIMPLIFIED
    public function payments()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');

        try {
            $filters = [
                'payment_type' => $this->request->getGet('payment_type') ?? 'all',
                'property_id' => $this->request->getGet('property_id') ?? '',
                'date_from' => $this->request->getGet('date_from') ?? '',
                'date_to' => $this->request->getGet('date_to') ?? ''
            ];

            $allPayments = $this->getIncomeExpensePayments($landlordId, $filters);
            $properties = $this->getLandlordProperties($landlordId);

            // FIXED: Use database remaining_balance values directly
            $property_balances = $this->getSimplePropertyBalances($landlordId);
            $totals = $this->calculateTotals($landlordId);

            // FIXED: Calculate total remaining balance from database
            $total_remaining_balance = 0;
            foreach ($properties as $property) {
                $total_remaining_balance += (float) ($property['remaining_balance'] ?? 0);
            }

            $data = [
                'title' => 'Payment Management',
                'payments' => $allPayments,
                'property_balances' => $property_balances,
                'totals' => $totals,
                'total_remaining_balance' => $total_remaining_balance,
                'properties' => $properties,
                'filters' => $filters
            ];

            return view('landlord/payments', $data);

        } catch (\Exception $e) {
            log_message('error', 'Payment page error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Error loading payments: ' . $e->getMessage());

            return view('landlord/payments', [
                'title' => 'Payment Management',
                'payments' => [],
                'property_balances' => [],
                'totals' => ['net_income' => 0, 'total_expenses' => 0, 'monthly_net' => 0],
                'total_remaining_balance' => 0,
                'properties' => [],
                'filters' => $filters ?? []
            ]);
        }
    }

    // SIMPLIFIED: Get simple property balances using database values
    private function getSimplePropertyBalances($landlordId)
    {
        try {
            $db = \Config\Database::connect();

            $balances = $db->query("
                SELECT 
                    p.id as property_id,
                    p.property_name,
                    p.address,
                    p.remaining_balance,
                    (SELECT MAX(tr.transfer_date) 
                     FROM transfer_receipts tr 
                     WHERE tr.property_id = p.id AND tr.landlord_id = ?
                    ) as last_transfer_date,
                    (SELECT tr.transfer_amount 
                     FROM transfer_receipts tr 
                     WHERE tr.property_id = p.id AND tr.landlord_id = ? 
                     ORDER BY tr.transfer_date DESC, tr.created_at DESC 
                     LIMIT 1
                    ) as last_transfer_amount,
                    (SELECT tr.notes 
                     FROM transfer_receipts tr 
                     WHERE tr.property_id = p.id AND tr.landlord_id = ? 
                     ORDER BY tr.transfer_date DESC, tr.created_at DESC 
                     LIMIT 1
                    ) as last_transfer_notes,
                    (SELECT COUNT(*) 
                     FROM transfer_receipts tr 
                     WHERE tr.property_id = p.id AND tr.landlord_id = ?
                    ) as transfer_count
                FROM properties p
                INNER JOIN property_shareholders ps ON ps.property_id = p.id
                WHERE ps.user_id = ? AND ps.status = 'active'
                ORDER BY p.property_name ASC
            ", [$landlordId, $landlordId, $landlordId, $landlordId, $landlordId])->getResultArray();

            return $balances;

        } catch (\Exception $e) {
            log_message('error', 'Error getting simple property balances: ' . $e->getMessage());
            return [];
        }
    }

    private function calculateTotals($landlordId)
    {
        try {
            $db = \Config\Database::connect();

            $result = $db->query("
                SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
                FROM income_expense_payments
                WHERE landlord_id = ?
            ", [$landlordId])->getRowArray();

            $income = (float) ($result['total_income'] ?? 0);
            $expenses = (float) ($result['total_expenses'] ?? 0);

            // Example inside calculateTotals()
            $totalIncome = $db->table('income_expense_payments')
                ->selectSum('amount')
                ->where('landlord_id', $landlordId)
                ->where('type', 'income') // only income rows
                ->get()
                ->getRow()
                ->amount ?? 0;

            return [
                'total_income' => (float) $totalIncome,
                'total_expenses' => (float) $expenses,
                'net_income' => (float) $totalIncome - (float) $expenses,
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error calculating totals: ' . $e->getMessage());
            return ['net_income' => 0, 'total_expenses' => 0, 'monthly_net' => 0];
        }
    }

    private function getIncomeExpensePayments($landlordId, $filters)
    {
        try {
            $db = \Config\Database::connect();

            $sql = "
                SELECT iep.*, p.property_name, p.address as property_address, pu.unit_name
                FROM income_expense_payments iep
                LEFT JOIN properties p ON p.id = iep.property_id
                LEFT JOIN property_units pu ON pu.id = iep.unit_id
                WHERE iep.landlord_id = ?
            ";

            $params = [$landlordId];

            if ($filters['payment_type'] !== 'all') {
                $sql .= " AND iep.type = ?";
                $params[] = $filters['payment_type'];
            }

            if ($filters['property_id']) {
                $sql .= " AND iep.property_id = ?";
                $params[] = $filters['property_id'];
            }

            if ($filters['date_from']) {
                $sql .= " AND iep.date >= ?";
                $params[] = $filters['date_from'];
            }

            if ($filters['date_to']) {
                $sql .= " AND iep.date <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY iep.date DESC, iep.created_at DESC";

            return $db->query($sql, $params)->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error getting payments: ' . $e->getMessage());
            return [];
        }
    }

    public function storeIncomePayment()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');

        $rules = [
            'date' => 'required|valid_date',
            'property_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'amount' => 'required|decimal|greater_than[0]',
            'source' => 'required|min_length[2]|max_length[100]',
            'description' => 'required|min_length[5]',
            'method' => 'permit_empty|in_list[cash,bank_transfer,check,card,online]'
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Please fill all required fields correctly.');
            return redirect()->back()->withInput();
        }

        try {
            $db = \Config\Database::connect();
            $propertyId = $this->request->getPost('property_id');
            $unit = $this->verifyUnitOwnership($this->request->getPost('unit_id'), $landlordId);

            if (!$unit) {
                session()->setFlashdata('error', 'Unit not found or access denied.');
                return redirect()->back()->withInput();
            }

            $db->transStart();

            $receiptFile = null;
            $receipt = $this->request->getFile('receipt_file');
            if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
                if ($receipt->getMimeType() !== 'application/pdf') {
                    session()->setFlashdata('error', 'Only PDF files are allowed for receipts.');
                    return redirect()->back()->withInput();
                }

                $uploadPath = WRITEPATH . 'uploads/receipts/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $receipt->getRandomName();
                $receipt->move($uploadPath, $newName);
                $receiptFile = $newName;
            }

            $this->createIncomePaymentsTable($db);

            $paymentData = [
                'landlord_id' => $landlordId,
                'property_id' => $propertyId,
                'unit_id' => $this->request->getPost('unit_id'),
                'date' => $this->request->getPost('date'),
                'amount' => $this->request->getPost('amount'),
                'source' => trim($this->request->getPost('source')),
                'description' => $this->request->getPost('description'),
                'method' => $this->request->getPost('method'),
                'receipt_file' => $receiptFile,
                'type' => 'income',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $paymentInserted = $db->table('income_expense_payments')->insert($paymentData);

            if ($paymentInserted) {
                $this->updatePropertyBalanceDirectly($propertyId, $db);
                $db->transComplete();

                if ($db->transStatus() !== false) {
                    session()->setFlashdata('success', 'Income payment added successfully and property balance updated.');
                } else {
                    throw new \Exception('Transaction failed');
                }
            } else {
                throw new \Exception('Failed to insert payment record');
            }

        } catch (\Exception $e) {
            if (isset($db)) {
                $db->transRollback();
            }
            log_message('error', 'Error storing income payment: ' . $e->getMessage());
            session()->setFlashdata('error', 'Error adding income payment: ' . $e->getMessage());
        }

        return redirect()->to('landlord/payments');
    }

    public function storeExpensePayment()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');

        $rules = [
            'date' => 'required|valid_date',
            'property_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'amount' => 'required|decimal|greater_than[0]',
            'expense_type' => 'required|in_list[maintenance,utilities,insurance,property_tax,cleaning,advertising,legal,management,other]',
            'description' => 'required|min_length[5]',
            'method' => 'permit_empty|in_list[cash,bank_transfer,check,card,online]'
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Please fill all required fields correctly.');
            return redirect()->back()->withInput();
        }

        try {
            $db = \Config\Database::connect();
            $propertyId = $this->request->getPost('property_id');
            $unit = $this->verifyUnitOwnership($this->request->getPost('unit_id'), $landlordId);

            if (!$unit) {
                session()->setFlashdata('error', 'Unit not found or access denied.');
                return redirect()->back()->withInput();
            }

            $db->transStart();

            $receiptFile = null;
            $receipt = $this->request->getFile('receipt_file');
            if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
                if ($receipt->getMimeType() !== 'application/pdf') {
                    session()->setFlashdata('error', 'Only PDF files are allowed for receipts.');
                    return redirect()->back()->withInput();
                }

                $uploadPath = WRITEPATH . 'uploads/receipts/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $receipt->getRandomName();
                $receipt->move($uploadPath, $newName);
                $receiptFile = $newName;
            }

            $this->createIncomePaymentsTable($db);

            $paymentData = [
                'landlord_id' => $landlordId,
                'property_id' => $propertyId,
                'unit_id' => $this->request->getPost('unit_id'),
                'date' => $this->request->getPost('date'),
                'amount' => $this->request->getPost('amount'),
                'source' => $this->request->getPost('expense_type'),
                'description' => $this->request->getPost('description'),
                'method' => $this->request->getPost('method'),
                'receipt_file' => $receiptFile,
                'type' => 'expense',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $paymentInserted = $db->table('income_expense_payments')->insert($paymentData);

            if ($paymentInserted) {
                $this->updatePropertyBalanceDirectly($propertyId, $db);
                $db->transComplete();

                if ($db->transStatus() !== false) {
                    session()->setFlashdata('success', 'Expense payment added successfully and property balance updated.');
                } else {
                    throw new \Exception('Transaction failed');
                }
            } else {
                throw new \Exception('Failed to insert payment record');
            }

        } catch (\Exception $e) {
            if (isset($db)) {
                $db->transRollback();
            }
            log_message('error', 'Error storing expense payment: ' . $e->getMessage());
            session()->setFlashdata('error', 'Error adding expense payment: ' . $e->getMessage());
        }

        return redirect()->to('landlord/payments');
    }

    // CRITICAL: Method to update property balance directly in database
    private function updatePropertyBalanceDirectly($propertyId, $db)
    {
        try {
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                return false;
            }

            // Get income
            $incomeResult = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total_income 
                FROM income_expense_payments 
                WHERE property_id = ? AND type = 'income'
            ", [$propertyId])->getRowArray();

            // Get expenses
            $expenseResult = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total_expenses 
                FROM income_expense_payments 
                WHERE property_id = ? AND type = 'expense'
            ", [$propertyId])->getRowArray();

            // Get transfers
            $transferResult = $db->query("
                SELECT COALESCE(SUM(transfer_amount), 0) as total_transfers 
                FROM transfer_receipts 
                WHERE property_id = ?
            ", [$propertyId])->getRowArray();

            $totalIncome = (float) $incomeResult['total_income'];
            $totalExpenses = (float) $expenseResult['total_expenses'];
            $totalTransfers = (float) $transferResult['total_transfers'];

            // Calculate management fee
            $managementPercentage = (float) ($property['management_percentage'] ?? 0);
            if ($managementPercentage > 1) {
                $managementPercentage = $managementPercentage / 100;
            }
            $managementFee = $totalIncome * $managementPercentage;

            // Final calculation: Net Profit - Transfers
            $netProfit = $totalIncome - $managementFee - $totalExpenses;
            $remainingBalance = max(0, $netProfit - $totalTransfers);

            // Update database
            $updateResult = $db->query("
                UPDATE properties 
                SET remaining_balance = ?, updated_at = NOW() 
                WHERE id = ?
            ", [$remainingBalance, $propertyId]);

            log_message('info', "Updated property {$propertyId} balance: Income={$totalIncome}, Mgmt Fee={$managementFee}, Expenses={$totalExpenses}, Transfers={$totalTransfers}, Final Balance={$remainingBalance}");

            return $updateResult;

        } catch (\Exception $e) {
            log_message('error', 'Error updating property balance directly: ' . $e->getMessage());
            return false;
        }
    }

    public function processTransferReceipt()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $landlordId = session()->get('user_id');

        $rules = [
            'property_id' => 'required|integer',
            'transfer_amount' => 'required|decimal|greater_than[0]',
            'transfer_date' => 'required|valid_date',
            'transfer_notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $propertyId = $this->request->getPost('property_id');
            $totalTransferAmount = (float) $this->request->getPost('transfer_amount');
            $transferDate = $this->request->getPost('transfer_date');
            $transferNotes = $this->request->getPost('transfer_notes') ?? '';

            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Property not found or access denied'
                ]);
            }

            $db->transStart();

            // Update balance first
            $this->updatePropertyBalanceDirectly($propertyId, $db);

            // Get current balance
            $property = $this->propertyModel->find($propertyId);
            $currentBalance = (float) ($property['remaining_balance'] ?? 0);

            if ($totalTransferAmount > $currentBalance) {
                throw new \Exception("Transfer amount (SAR " . number_format($totalTransferAmount, 2) . ") exceeds available balance (SAR " . number_format($currentBalance, 2) . ")");
            }

            $this->createTransferReceiptsTable($db);

            $receiptFile = null;
            $receipt = $this->request->getFile('transfer_receipt_file');
            if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
                if ($receipt->getMimeType() !== 'application/pdf') {
                    throw new \Exception('Only PDF files are allowed for transfer receipts.');
                }

                $uploadPath = WRITEPATH . 'uploads/transfer_receipts/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $receipt->getRandomName();
                $receipt->move($uploadPath, $newName);
                $receiptFile = $newName;
            }

            $transferData = [
                'landlord_id' => $landlordId,
                'property_id' => $propertyId,
                'transfer_date' => $transferDate,
                'transfer_amount' => $totalTransferAmount,
                'notes' => $transferNotes,
                'receipt_file' => $receiptFile,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $transferInserted = $db->table('transfer_receipts')->insert($transferData);

            if ($transferInserted) {
                // Recalculate balance after transfer
                $this->updatePropertyBalanceDirectly($propertyId, $db);

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
                }

                // Get updated balance
                $updatedProperty = $this->propertyModel->find($propertyId);
                $newBalance = (float) ($updatedProperty['remaining_balance'] ?? 0);

                log_message('info', "Transfer receipt processed - Property: {$propertyId}, Transfer: {$totalTransferAmount}, New Balance: {$newBalance}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Transfer receipt processed successfully! Remaining balance updated to SAR ' . number_format($newBalance, 2),
                    'new_balance' => $newBalance,
                    'previous_balance' => $currentBalance
                ]);
            } else {
                throw new \Exception('Failed to insert transfer receipt');
            }

        } catch (\Exception $e) {
            if (isset($db)) {
                $db->transRollback();
            }
            log_message('error', 'Process transfer receipt error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getPropertyRemainingBalance($propertyId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        $landlordId = session()->get('user_id');

        try {
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Property not found or access denied'
                ]);
            }

            $db = \Config\Database::connect();
            $this->updatePropertyBalanceDirectly($propertyId, $db);

            $propertyResult = $db->query("
                SELECT remaining_balance, property_name 
                FROM properties 
                WHERE id = ?
            ", [$propertyId])->getRowArray();

            $remainingBalance = (float) ($propertyResult['remaining_balance'] ?? 0);

            $shareholdersQuery = $db->query("
                SELECT COUNT(*) as shareholders_count 
                FROM property_shareholders 
                WHERE property_id = ? AND status = 'active'
            ", [$propertyId])->getRowArray();

            $shareholdersCount = (int) ($shareholdersQuery['shareholders_count'] ?? 1);

            return $this->response->setJSON([
                'success' => true,
                'remaining_balance' => $remainingBalance,
                'formatted_balance' => number_format($remainingBalance, 2),
                'shareholders_count' => $shareholdersCount,
                'property_name' => $propertyResult['property_name']
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get property remaining balance error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTransferHistory($propertyId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        $landlordId = session()->get('user_id');

        try {
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Property not found'
                ]);
            }

            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied to this property'
                ]);
            }

            $db = \Config\Database::connect();
            $this->ensureTransferReceiptsTableExists($db);

            $transfers = $this->getPropertyTransfersSafely($propertyId, $landlordId, $db);
            $netProfit = $this->calculatePropertyNetProfit($propertyId);

            $runningBalance = $netProfit;
            $totalTransferred = 0;

            usort($transfers, function ($a, $b) {
                $dateA = strtotime($a['transfer_date']);
                $dateB = strtotime($b['transfer_date']);
                if ($dateA === $dateB) {
                    return intval($a['id']) - intval($b['id']);
                }
                return $dateA - $dateB;
            });

            foreach ($transfers as &$transfer) {
                $transfer['balance_before_transfer'] = $runningBalance;
                $transferAmount = (float) $transfer['transfer_amount'];
                $runningBalance = max(0, $runningBalance - $transferAmount);
                $transfer['balance_after_transfer'] = $runningBalance;
                $totalTransferred += $transferAmount;
            }

            $transfers = array_reverse($transfers);
            $currentBalance = max(0, $netProfit - $totalTransferred);

            return $this->response->setJSON([
                'success' => true,
                'transfers' => $transfers,
                'property_info' => [
                    'property_name' => $property['property_name'],
                    'address' => $property['address'] ?? '',
                    'current_balance' => $currentBalance,
                    'total_transfers' => count($transfers),
                    'total_transferred_amount' => $totalTransferred,
                    'net_profit' => $netProfit
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Transfer history error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred while loading transfer history'
            ]);
        }
    }

/**
 * Download Transfer History PDF - ACTUAL PDF GENERATION
 */
public function downloadTransferHistoryPdf()
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    $landlordId = $this->getCurrentUserId();
    $propertyId = $this->request->getPost('property_id');

    if (!$propertyId) {
        session()->setFlashdata('error', 'Property ID is required.');
        return redirect()->back();
    }

    try {
        $db = \Config\Database::connect();

        // Verify property ownership
        $propertyCheck = $db->table('properties p')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->select('p.id, p.property_name, p.address')
            ->where('p.id', $propertyId)
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getRowArray();

        if (!$propertyCheck) {
            session()->setFlashdata('error', 'Property not found or access denied.');
            return redirect()->back();
        }

        // Get transfer history from transfer_receipts table
        $transfers = $db->table('transfer_receipts tr')
            ->select('tr.*, u.first_name, u.last_name, u.email')
            ->join('users u', 'u.id = tr.landlord_id', 'left')
            ->where('tr.property_id', $propertyId)
            ->orderBy('tr.transfer_date', 'DESC')
            ->get()
            ->getResultArray();

        // Generate PDF using the same method as your other reports
        $html = $this->generateTransferHistoryPdfHtml($propertyCheck, $transfers);
        $pdfContent = $this->generatePdfFromHtml($html);
        
        $filename = 'transfer_history_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $propertyCheck['property_name']) . '_' . date('Y-m-d') . '.pdf';
        
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($pdfContent);

    } catch (\Exception $e) {
        log_message('error', 'Transfer history PDF generation error: ' . $e->getMessage());
        session()->setFlashdata('error', 'Error generating transfer history PDF: ' . $e->getMessage());
        return redirect()->back();
    }
}

/**
 * Generate Transfer History HTML optimized for PDF generation
 */
private function generateTransferHistoryPdfHtml($property, $transfers)
{
    $landlordName = session()->get('first_name') . ' ' . session()->get('last_name');
    $totalTransferred = array_sum(array_column($transfers, 'transfer_amount'));
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transfer History Report</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body { 
            font-family: "DejaVu Sans", Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            color: #333; 
            font-size: 12px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #007bff; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .header h1 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .property-info { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
            border: 1px solid #e9ecef;
        }
        .property-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .summary { 
            background-color: #e3f2fd; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
            border: 1px solid #bbdefb;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 16px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            font-size: 11px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #007bff; 
            color: white; 
            font-weight: bold;
        }
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .amount { 
            text-align: right; 
            font-weight: bold; 
            color: #dc3545;
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #666; 
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .no-data { 
            text-align: center; 
            padding: 40px; 
            color: #666; 
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transfer History Report</h1>
        <h2>' . htmlspecialchars($property['property_name']) . '</h2>
        <p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>
    </div>
    
    <div class="property-info">
        <h3>Property Information</h3>
        <div class="info-row">
            <span class="info-label">Property Name:</span>
            ' . htmlspecialchars($property['property_name']) . '
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            ' . htmlspecialchars($property['address'] ?? 'N/A') . '
        </div>
        <div class="info-row">
            <span class="info-label">Generated By:</span>
            ' . htmlspecialchars($landlordName) . '
        </div>
        <div class="info-row">
            <span class="info-label">Property ID:</span>
            #' . $property['id'] . '
        </div>
    </div>
    
    <div class="summary">
        <h3>Transfer Summary</h3>
        <div class="info-row">
            <span class="info-label">Total Transfers:</span>
            ' . count($transfers) . ' record' . (count($transfers) != 1 ? 's' : '') . '
        </div>
        <div class="info-row">
            <span class="info-label">Total Amount:</span>
            <strong style="color: #dc3545;">SAR ' . number_format($totalTransferred, 2) . '</strong>
        </div>
        <div class="info-row">
            <span class="info-label">Date Range:</span>
            ' . (count($transfers) > 0 ? 
                date('M j, Y', strtotime($transfers[count($transfers)-1]['transfer_date'])) . ' to ' . date('M j, Y', strtotime($transfers[0]['transfer_date']))
                : 'N/A') . '
        </div>
    </div>';

    if (!empty($transfers)) {
        $html .= '
    <h3 style="color: #007bff; margin-bottom: 15px;">Transfer History Details</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 18%;">Amount (SAR)</th>
                <th style="width: 15%;">Created By</th>
                <th style="width: 30%;">Notes</th>
                <th style="width: 14%;">Receipt</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($transfers as $index => $transfer) {
            $createdBy = 'Unknown';
            if (!empty($transfer['first_name']) || !empty($transfer['last_name'])) {
                $createdBy = trim($transfer['first_name'] . ' ' . $transfer['last_name']);
            } elseif (!empty($transfer['email'])) {
                $createdBy = $transfer['email'];
            }

            $receiptStatus = !empty($transfer['receipt_file']) ? 'Available' : 'No Receipt';
            $notes = !empty($transfer['notes']) ? htmlspecialchars($transfer['notes']) : 'No notes';
            
            // Truncate long notes for PDF
            if (strlen($notes) > 50) {
                $notes = substr($notes, 0, 47) . '...';
            }

            $html .= '
            <tr>
                <td style="text-align: center;">' . ($index + 1) . '</td>
                <td>' . date('M j, Y', strtotime($transfer['transfer_date'])) . '</td>
                <td class="amount">- SAR ' . number_format($transfer['transfer_amount'], 2) . '</td>
                <td>' . htmlspecialchars($createdBy) . '</td>
                <td>' . $notes . '</td>
                <td style="text-align: center;">' . $receiptStatus . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>';
        
        // Add notes about receipts if any exist
        $hasReceipts = array_filter($transfers, function($t) { return !empty($t['receipt_file']); });
        if (!empty($hasReceipts)) {
            $html .= '
            <div style="margin-top: 20px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
                <p style="margin: 0; font-size: 11px;"><strong>Note:</strong> Transfer receipts are available for download in the digital system. Receipt files are not included in this PDF report.</p>
            </div>';
        }
        
    } else {
        $html .= '
    <div class="no-data">
        <h3>No Transfer History</h3>
        <p>No transfer receipts have been recorded for this property.</p>
        <p>Transfer receipts can be added through the Payments page using the "Add Transfer Receipt" feature.</p>
    </div>';
    }

    $html .= '
    <div class="footer">
        <p><strong>Property Management System - Transfer History Report</strong></p>
        <p>Report ID: TH-' . date('Ymd-His') . ' | Generated: ' . date('Y-m-d H:i:s') . ' | Property ID: ' . $property['id'] . '</p>
        <p style="font-style: italic;">This document contains confidential financial information.</p>
        <p style="margin-top: 10px;">For questions about this report, please contact the property management system administrator.</p>
    </div>
</body>
</html>';

    return $html;
}

/**
 * Generate PDF from HTML using DomPDF (same as your existing reports)
 */
private function generatePdfFromHtml($html)
{
    try {
        // Check if DomPDF is available
        if (!class_exists('Dompdf\Dompdf')) {
            throw new \Exception('DomPDF library is not installed. Please install it via Composer: composer require dompdf/dompdf');
        }

        // Configure DomPDF options
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'portrait');
        $options->set('chroot', realpath(ROOTPATH));

        // Create DomPDF instance
        $dompdf = new \Dompdf\Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        return $dompdf->output();

    } catch (\Exception $e) {
        log_message('error', 'PDF generation failed: ' . $e->getMessage());
        throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
    }
}    //END PAYMENT PAGE METHODS

    //MAINTENANCE PAGE METHODS

    /**
     * Enhanced Maintenance Page - FIXED for your DB structure
     */
    public function maintenance()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Auto-reject stale pending requests (7+ days old)
            $this->autoRejectStaleRequests($db);

            // Get filters
            $statusFilter = $this->request->getGet('status') ?? '';
            $priorityFilter = $this->request->getGet('priority') ?? '';
            $propertyFilter = $this->request->getGet('property') ?? '';

            // Get properties for dropdown
            $properties = $this->getMaintenanceProperties($landlordId, $db);

            // Build maintenance requests query using your actual column names
            $builder = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_name,
                         ms.first_name as staff_first_name, ms.last_name as staff_last_name,
                         COUNT(mc.id) as cancel_count')
                ->join('properties p', 'p.id = mr.property_id')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->join('users ms', 'ms.id = mr.assigned_staff_id', 'left')
                ->join('maintenance_cancellations mc', 'mc.request_id = mr.id', 'left')
                ->where('ps.user_id', $landlordId)
                ->where('ps.status', 'active')
                ->groupBy('mr.id');

            // Apply filters
            if (!empty($statusFilter)) {
                $builder->where('mr.status', $statusFilter);
            }
            if (!empty($priorityFilter)) {
                $builder->where('mr.priority', $priorityFilter);
            }
            if (!empty($propertyFilter)) {
                $builder->where('p.id', $propertyFilter);
            }

            $maintenanceRequests = $builder->orderBy('mr.requested_date', 'DESC')->get()->getResultArray();

            // Enhanced stats calculation
            $stats = $this->calculateEnhancedMaintenanceStats($maintenanceRequests);

            // Check for completion images
            foreach ($maintenanceRequests as &$request) {
                if ($request['status'] === 'completed') {
                    $images = $db->table('maintenance_images')
                        ->where('maintenance_request_id', $request['id'])
                        ->get()
                        ->getResultArray();
                    $request['completion_images'] = $images;
                }

                // Add formatted dates using your actual column names
                $request['created_at'] = $request['requested_date']; // Map to expected field name
            }

            return view('landlord/maintenance', [
                'title' => 'Maintenance Management',
                'maintenance_requests' => $maintenanceRequests,
                'properties' => $properties,
                'stats' => $stats,
                'current_status' => $statusFilter,
                'current_priority' => $priorityFilter,
                'current_property' => $propertyFilter
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Maintenance page error: ' . $e->getMessage());
            return view('landlord/maintenance', [
                'title' => 'Maintenance Management',
                'maintenance_requests' => [],
                'properties' => [],
                'stats' => ['pending_count' => 0, 'approved_count' => 0, 'in_progress_count' => 0, 'completed_count' => 0],
                'current_status' => '',
                'current_priority' => '',
                'current_property' => ''
            ]);
        }
    }

    /**
     * Enhanced Add Maintenance Request - FIXED for your DB structure
     */
    public function addMaintenanceRequest()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $landlordId = $this->getCurrentUserId();

        try {
            // Validation
            $rules = [
                'property_id' => 'required|integer',
                'unit_id' => 'required|integer',
                'title' => 'required|min_length[5]|max_length[200]',
                'description' => 'required|min_length[10]|max_length[2000]',
                'priority' => 'required|in_list[low,normal,high,urgent]',
                'estimated_cost' => 'permit_empty|decimal|greater_than_equal_to[0]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
                ]);
            }

            // Verify property ownership
            if (!$this->verifyPropertyOwnership($this->request->getPost('property_id'), $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied to this property'
                ]);
            }

            $db = \Config\Database::connect();

            // Insert using your actual column names
            $requestData = [
                'property_id' => $this->request->getPost('property_id'),
                'unit_id' => $this->request->getPost('unit_id'),
                'title' => trim($this->request->getPost('title')),
                'description' => trim($this->request->getPost('description')),
                'priority' => $this->request->getPost('priority'),
                'status' => 'pending',
                'estimated_cost' => $this->request->getPost('estimated_cost') ?: null,
                'created_by_landlord' => 1, // Your actual column
                'requested_date' => date('Y-m-d H:i:s'), // Your actual column name
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('maintenance_requests')->insert($requestData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Maintenance request created successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create maintenance request'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Add maintenance request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while creating the request'
            ]);
        }
    }

    /**
     * View maintenance request details with images - FIXED for your DB structure
     */
    public function viewMaintenanceRequest($requestId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get request details using your actual column names
            $request = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_name,
                         ms.first_name as staff_first_name, ms.last_name as staff_last_name,
                         ms.phone as staff_phone, ms.email as staff_email,
                         COUNT(mc.id) as cancel_count')
                ->join('properties p', 'p.id = mr.property_id')
                ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->join('users ms', 'ms.id = mr.assigned_staff_id', 'left')
                ->join('maintenance_cancellations mc', 'mc.request_id = mr.id', 'left')
                ->where('mr.id', $requestId)
                ->where('ps.user_id', $landlordId)
                ->where('ps.status', 'active')
                ->groupBy('mr.id')
                ->get()
                ->getRowArray();

            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Request not found or access denied'
                ]);
            }

            // Map column names for frontend compatibility
            $request['created_at'] = $request['requested_date'];
            $request['assigned_date'] = $request['assigned_date'];
            $request['completed_date'] = $request['completed_date'];

            // Get completion images if status is completed
            $images = [];
            if ($request['status'] === 'completed') {
                $images = $db->table('maintenance_images')
                    ->where('maintenance_request_id', $requestId)
                    ->get()
                    ->getResultArray();
            }

            // Get cancellation history
            $cancellations = $db->table('maintenance_cancellations mc')
                ->select('mc.*, u.first_name, u.last_name')
                ->join('users u', 'u.id = mc.staff_id', 'left')
                ->where('mc.request_id', $requestId)
                ->orderBy('mc.cancelled_at', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'request' => $request,
                'images' => $images,
                'cancellations' => $cancellations
            ]);

        } catch (\Exception $e) {
            log_message('error', 'View maintenance request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load request details'
            ]);
        }
    }

    /**
 * Delete maintenance request - DEBUG VERSION
 */
public function deleteMaintenanceRequest($requestId)
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    if ($this->request->getMethod() !== 'DELETE') {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request method: ' . $this->request->getMethod()
        ]);
    }

    $landlordId = $this->getCurrentUserId();

    try {
        $db = \Config\Database::connect();

        // DEBUG: First get the raw request data
        $rawRequest = $db->table('maintenance_requests')
            ->where('id', $requestId)
            ->get()
            ->getRowArray();

        if (!$rawRequest) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request not found in database'
            ]);
        }

        // DEBUG: Log the status
        log_message('debug', 'Request ID: ' . $requestId . ', Status: "' . $rawRequest['status'] . '"');

        // Verify landlord owns this request's property and get status
        $requestCheck = $db->table('maintenance_requests mr')
            ->join('properties p', 'p.id = mr.property_id')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->select('mr.id, mr.status, mr.property_id, ps.user_id')
            ->where('mr.id', $requestId)
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getRowArray();

        if (!$requestCheck) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request not found or access denied. Landlord ID: ' . $landlordId
            ]);
        }

        // DEBUG: Check status more carefully
        $status = trim($requestCheck['status']);
        log_message('debug', 'Trimmed status: "' . $status . '", Length: ' . strlen($status));

        // Only allow deletion of pending requests
        if ($status !== 'pending') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Only pending requests can be deleted. Current status: "' . $status . '"'
            ]);
        }

        // Delete the maintenance request
        $result = $db->table('maintenance_requests')
            ->where('id', $requestId)
            ->delete();

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Maintenance request deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete maintenance request'
            ]);
        }

    } catch (\Exception $e) {
        log_message('error', 'Delete maintenance request error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'An error occurred while deleting the request: ' . $e->getMessage()
        ]);
    }
}

    /**
     * Auto-reject maintenance requests that have been pending for 7+ days - FIXED
     */
    private function autoRejectStaleRequests($db)
    {
        try {
            $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

            // Find pending requests older than 7 days using your actual column names
            $staleRequests = $db->table('maintenance_requests')
                ->where('status', 'pending')
                ->where('requested_date <', $sevenDaysAgo) // Your actual column name
                ->where('rejected_date', null) // Don't re-reject already rejected items
                ->get()
                ->getResultArray();

            if (!empty($staleRequests)) {
                foreach ($staleRequests as $request) {
                    $db->table('maintenance_requests')
                        ->where('id', $request['id'])
                        ->update([
                            'status' => 'rejected',
                            'rejection_reason' => 'Automatically rejected - No maintenance staff response after 7 days',
                            'rejected_date' => date('Y-m-d H:i:s'), // Your actual column
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                }
                log_message('info', 'Auto-rejected ' . count($staleRequests) . ' stale maintenance requests');
            }

        } catch (\Exception $e) {
            log_message('error', 'Auto-reject stale requests error: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced stats calculation - FIXED for your DB structure
     */
    private function calculateEnhancedMaintenanceStats($requests)
    {
        $stats = [
            'pending_count' => 0,
            'approved_count' => 0,
            'in_progress_count' => 0,
            'completed_count' => 0,
            'rejected_count' => 0,
            'total_requests' => count($requests),
            'total_estimated_cost' => 0,
            'total_actual_cost' => 0,
            'total_cancelled' => 0
        ];

        foreach ($requests as $request) {
            $status = strtolower($request['status'] ?? 'pending');
            if (isset($stats[$status . '_count'])) {
                $stats[$status . '_count']++;
            }

            $stats['total_estimated_cost'] += (float) ($request['estimated_cost'] ?? 0);
            $stats['total_actual_cost'] += (float) ($request['actual_cost'] ?? 0);

            // Count cancellations
            if (($request['cancel_count'] ?? 0) > 0) {
                $stats['total_cancelled']++;
            }
        }

        return $stats;
    }

    /**
     * Get properties for maintenance dropdown - FIXED
     */
    private function getMaintenanceProperties($landlordId, $db)
    {
        try {
            return $db->table('properties p')
                ->select('p.id, p.property_name')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->where('ps.status', 'active')
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Get maintenance properties error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get units for property (AJAX endpoint) - FIXED
     */
    public function getUnits($propertyId)
    {
        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Verify property ownership
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied to this property'
                ]);
            }

            // Get units for this property
            $units = $db->table('property_units')
                ->select('id, unit_name')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'units' => $units
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get units error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load units'
            ]);
        }
    }

    /**
 * Serve maintenance images from writable directory
 */
public function serveMaintenanceImage($filename)
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    try {
        // Security: validate filename
        if (!$filename || strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            throw new \Exception('Invalid filename');
        }

        // Construct file path
        $filePath = WRITEPATH . 'uploads/maintenance/' . $filename;
        
        // Check if file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new \Exception('Image not found');
        }

        // Get file info
        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);
        
        // Validate it's an image
        if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
            throw new \Exception('Invalid image type');
        }

        // Set headers
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Length', $fileSize);
        $this->response->setHeader('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
        $this->response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
        
        // Output file
        return $this->response->setBody(file_get_contents($filePath));

    } catch (\Exception $e) {
        log_message('error', 'Error serving maintenance image: ' . $e->getMessage());
        
        // Return a 1x1 transparent pixel as fallback
        $transparentPixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        
        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Length', strlen($transparentPixel))
            ->setStatusCode(404)
            ->setBody($transparentPixel);
    }
}

    //END MAINTENANCE PAGE METHODS

    //REPORTS PAGE METHODS
    public function reports()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        try {
            // Create reports_log table if it doesn't exist
            $this->ensureReportsLogTable($db);

            // Get recent generated reports with PDF download links
            $recent_reports = [];
            try {
                $recent_reports = $db->table('reports_log')
                    ->select('id, report_kind, report_name, property_name, generated_date, pdf_filename')
                    ->where('landlord_id', $this->getCurrentUserId())
                    ->orderBy('generated_date', 'DESC')
                    ->limit(25)
                    ->get()
                    ->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching recent reports: ' . $e->getMessage());
            }

            // Get monthly summary reports (if table exists) - FIXED DATA
            $monthly_reports_summary = [];
            try {
                if ($db->tableExists('monthly_reports')) {
                    $monthly_reports_summary = $db->table('monthly_reports')
                        ->select('report_month, property_name, total_income, total_expenses, management_fee, net_profit, remaining_balance, is_automatic, email_sent, generated_at')
                        ->where('landlord_id', $this->getCurrentUserId()) // Filter by current user
                        ->orderBy('generated_at', 'DESC')
                        ->limit(50)
                        ->get()
                        ->getResultArray();
                }
            } catch (\Exception $e) {
                log_message('error', 'Error fetching monthly reports: ' . $e->getMessage());
            }

            // Get properties list for form dropdowns
            $properties = [];
            try {
                $landlordId = $this->getCurrentUserId();
                $properties = $db->table('properties p')
                    ->select('p.id, p.property_name, p.address')
                    ->join('property_shareholders ps', 'ps.property_id = p.id')
                    ->where('ps.user_id', $landlordId)
                    ->where('ps.status', 'active')
                    ->orderBy('p.property_name', 'ASC')
                    ->get()
                    ->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching properties: ' . $e->getMessage());
            }

            return view('landlord/reports', [
                'title' => 'Reports & Analytics',
                'recent_reports' => $recent_reports,
                'monthly_reports_summary' => $monthly_reports_summary,
                'properties' => $properties,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Reports page error: ' . $e->getMessage());
            return view('landlord/reports', [
                'title' => 'Reports & Analytics',
                'recent_reports' => [],
                'monthly_reports_summary' => [],
                'properties' => [],
            ]);
        }
    }

    /**
     * Download generated report PDF
     */
    public function downloadReport($reportId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        try {
            $db = \Config\Database::connect();
            $landlordId = $this->getCurrentUserId();

            // Get report record
            $report = $db->table('reports_log')
                ->where('id', $reportId)
                ->where('landlord_id', $landlordId)
                ->get()
                ->getRowArray();

            if (!$report) {
                throw new \Exception('Report not found');
            }

            $filename = $report['pdf_filename'];
            if (!$filename) {
                throw new \Exception('PDF file not available');
            }

            $filePath = WRITEPATH . 'uploads/reports/' . $filename;

            if (!file_exists($filePath)) {
                throw new \Exception('PDF file not found on server');
            }

            return $this->response->download($filePath, null)
                ->setFileName($report['report_name'] . '.pdf');

        } catch (\Exception $e) {
            log_message('error', 'Download report error: ' . $e->getMessage());
            $this->setError('Failed to download report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Ownership Report - FIXED
     */
    public function generateOwnershipReport()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'property_id' => 'required'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please select a property.');
            return redirect()->back()->withInput();
        }

        try {
            $propertyId = $this->request->getPost('property_id');
            $reportName = $this->request->getPost('report_name') ?: 'Ownership Report';
            $includeUnits = $this->request->getPost('include_units') ? true : false;
            $includeFinancials = $this->request->getPost('include_financials') ? true : false;

            $db = \Config\Database::connect();

            // Get property data
            if ($propertyId === 'all') {
                $properties = $this->getAllPropertiesOwnershipData();
                $reportTitle = 'Portfolio Ownership Report';
                $propertyName = 'All Properties';
            } else {
                $property = $this->getSinglePropertyOwnershipData($propertyId, $includeUnits, $includeFinancials);
                $properties = [$property];
                $reportTitle = 'Ownership Report - ' . ($property['property_name'] ?? 'Property');
                $propertyName = $property['property_name'] ?? 'Property';
            }

            // Generate PDF with proper Arabic support
            $html = $this->generateOwnershipReportHTML($properties, $reportTitle, $includeUnits, $includeFinancials);
            $filename = 'Ownership_Report_' . date('Y-m-d_H-i-s') . '.pdf';

            // Save PDF to file system and create download
            $savedFilename = $this->savePDFToFile($html, $filename);

            // Log the report generation with PDF filename
            $reportId = $this->logReportGeneration('ownership', $reportName, $propertyName, $savedFilename);

            $this->setSuccess('Ownership report generated successfully!');

            // Redirect to download
            return redirect()->to("landlord/download-report/{$reportId}");

        } catch (\Exception $e) {
            log_message('error', 'Ownership report generation error: ' . $e->getMessage());
            $this->setError('Failed to generate ownership report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Income & Expense Report - FIXED
     */
    public function generateIncomeExpenseReport()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'property_id' => 'required'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please select a property.');
            return redirect()->back()->withInput();
        }

        try {
            $propertyId = $this->request->getPost('property_id');
            $reportName = $this->request->getPost('report_name') ?: 'Income & Expense Report';
            $dateFrom = $this->request->getPost('date_from');
            $dateTo = $this->request->getPost('date_to');
            $includeDistribution = $this->request->getPost('include_distribution') ? true : false;

            // Set default date range if not provided
            if (empty($dateFrom)) {
                $dateFrom = date('Y-m-01'); // First day of current month
            }
            if (empty($dateTo)) {
                $dateTo = date('Y-m-t'); // Last day of current month
            }

            $db = \Config\Database::connect();

            // Get financial data
            if ($propertyId === 'all') {
                $data = $this->getAllPropertiesFinancialData($dateFrom, $dateTo, $includeDistribution);
                $reportTitle = 'Portfolio Financial Report';
                $propertyName = 'All Properties';
            } else {
                $data = $this->getSinglePropertyFinancialData($propertyId, $dateFrom, $dateTo, $includeDistribution);
                $reportTitle = 'Financial Report - ' . ($data['property']['property_name'] ?? 'Property');
                $propertyName = $data['property']['property_name'] ?? 'Property';
            }

            // Generate PDF
            $html = $this->generateFinancialReportHTML($data, $reportTitle, $dateFrom, $dateTo, $includeDistribution);
            $filename = 'Financial_Report_' . date('Y-m-d_H-i-s') . '.pdf';

            // Save PDF to file system
            $savedFilename = $this->savePDFToFile($html, $filename);

            // Log the report generation
            $reportId = $this->logReportGeneration('income_expense', $reportName, $propertyName, $savedFilename);

            $this->setSuccess('Financial report generated successfully!');

            // Redirect to download
            return redirect()->to("landlord/download-report/{$reportId}");

        } catch (\Exception $e) {
            log_message('error', 'Financial report generation error: ' . $e->getMessage());
            $this->setError('Failed to generate financial report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Maintenance Report - FIXED
     */
    public function generateMaintenanceReport()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'property_id' => 'required'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please select a property.');
            return redirect()->back()->withInput();
        }

        try {
            $propertyId = $this->request->getPost('property_id');
            $reportName = $this->request->getPost('report_name') ?: 'Maintenance Report';
            $dateFrom = $this->request->getPost('date_from');
            $dateTo = $this->request->getPost('date_to');
            $status = $this->request->getPost('status') ?: 'all';

            // Set default date range if not provided
            if (empty($dateFrom)) {
                $dateFrom = date('Y-m-01'); // First day of current month
            }
            if (empty($dateTo)) {
                $dateTo = date('Y-m-t'); // Last day of current month
            }

            $db = \Config\Database::connect();

            // Get maintenance data
            if ($propertyId === 'all') {
                $data = $this->getAllPropertiesMaintenanceData($dateFrom, $dateTo, $status);
                $reportTitle = 'Portfolio Maintenance Report';
                $propertyName = 'All Properties';
            } else {
                $data = $this->getSinglePropertyMaintenanceData($propertyId, $dateFrom, $dateTo, $status);
                $reportTitle = 'Maintenance Report - ' . ($data['property']['property_name'] ?? 'Property');
                $propertyName = $data['property']['property_name'] ?? 'Property';
            }

            // Generate PDF
            $html = $this->generateMaintenanceReportHTML($data, $reportTitle, $dateFrom, $dateTo, $status);
            $filename = 'Maintenance_Report_' . date('Y-m-d_H-i-s') . '.pdf';

            // Save PDF to file system
            $savedFilename = $this->savePDFToFile($html, $filename);

            // Log the report generation
            $reportId = $this->logReportGeneration('maintenance', $reportName, $propertyName, $savedFilename);

            $this->setSuccess('Maintenance report generated successfully!');

            // Redirect to download
            return redirect()->to("landlord/download-report/{$reportId}");

        } catch (\Exception $e) {
            log_message('error', 'Maintenance report generation error: ' . $e->getMessage());
            $this->setError('Failed to generate maintenance report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Monthly Report - FIXED DATA
     */
    public function generateMonthlyReport()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'property_id' => 'required',
            'month' => 'required'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please select a property and month.');
            return redirect()->back()->withInput();
        }

        try {
            $propertyId = $this->request->getPost('property_id');
            $month = $this->request->getPost('month'); // Format: Y-m
            $reportName = $this->request->getPost('report_name') ?: 'Monthly Report - ' . $month;

            // Calculate date range for the month
            $dateFrom = $month . '-01';
            $dateTo = date('Y-m-t', strtotime($dateFrom)); // Last day of the month

            $db = \Config\Database::connect();

            // Get monthly data - FIXED
            if ($propertyId === 'all') {
                $data = $this->getAllPropertiesMonthlyData($dateFrom, $dateTo);
                $reportTitle = 'Portfolio Monthly Report - ' . date('F Y', strtotime($dateFrom));
                $propertyName = 'All Properties';
            } else {
                $data = $this->getSinglePropertyMonthlyData($propertyId, $dateFrom, $dateTo);
                $reportTitle = 'Monthly Report - ' . ($data['property_name'] ?? 'Property') . ' - ' . date('F Y', strtotime($dateFrom));
                $propertyName = $data['property_name'] ?? 'Property';
            }

            // Store in monthly_reports table for tracking
            $this->storeMonthlyReportRecord($data, $month, $propertyId);

            // Generate PDF
            $html = $this->generateMonthlyReportHTML($data, $reportTitle, $month);
            $filename = 'Monthly_Report_' . $month . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Save PDF to file system
            $savedFilename = $this->savePDFToFile($html, $filename);

            // Log the report generation
            $reportId = $this->logReportGeneration('monthly', $reportName, $propertyName, $savedFilename);

            $this->setSuccess('Monthly report generated successfully!');

            // Redirect to download
            return redirect()->to("landlord/download-report/{$reportId}");

        } catch (\Exception $e) {
            log_message('error', 'Monthly report generation error: ' . $e->getMessage());
            $this->setError('Failed to generate monthly report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    // FIXED MONTHLY DATA METHODS

    private function getAllPropertiesMonthlyData($dateFrom, $dateTo)
    {
        $landlordId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        $properties = $db->table('properties p')
            ->select('p.*')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getResultArray();

        $portfolioTotals = [
            'total_income' => 0,
            'total_expenses' => 0,
            'total_management_fees' => 0,
            'total_transfers' => 0,
            'net_profit' => 0,
            'remaining_balance' => 0
        ];

        foreach ($properties as &$property) {
            $financial = $this->getPropertyFinancialDataForPeriod($property['id'], $dateFrom, $dateTo);
            $transfers = $this->getPropertyTransfersForPeriod($property['id'], $dateFrom, $dateTo);

            $transferAmount = 0;
            foreach ($transfers as $transfer) {
                $transferAmount += (float) $transfer['transfer_amount'];
            }

            $property['monthly_data'] = [
                'total_income' => $financial['total_income'],
                'total_expenses' => $financial['total_expenses'],
                'management_fee' => $financial['management_fee'],
                'net_profit' => $financial['net_profit'],
                'total_transfers' => $transferAmount,
                'remaining_balance' => max(0, $financial['net_profit'] - $transferAmount)
            ];

            // Add to portfolio totals
            $portfolioTotals['total_income'] += $property['monthly_data']['total_income'];
            $portfolioTotals['total_expenses'] += $property['monthly_data']['total_expenses'];
            $portfolioTotals['total_management_fees'] += $property['monthly_data']['management_fee'];
            $portfolioTotals['total_transfers'] += $property['monthly_data']['total_transfers'];
            $portfolioTotals['net_profit'] += $property['monthly_data']['net_profit'];
            $portfolioTotals['remaining_balance'] += $property['monthly_data']['remaining_balance'];
        }

        return [
            'properties' => $properties,
            'portfolio_totals' => $portfolioTotals
        ];
    }

    private function getSinglePropertyMonthlyData($propertyId, $dateFrom, $dateTo)
    {
        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            throw new \Exception('Access denied to this property');
        }

        $property = $this->propertyModel->find($propertyId);
        if (!$property) {
            throw new \Exception('Property not found');
        }

        $financial = $this->getPropertyFinancialDataForPeriod($propertyId, $dateFrom, $dateTo);
        $transfers = $this->getPropertyTransfersForPeriod($propertyId, $dateFrom, $dateTo);

        $transferAmount = 0;
        foreach ($transfers as $transfer) {
            $transferAmount += (float) $transfer['transfer_amount'];
        }

        $property['monthly_data'] = [
            'total_income' => $financial['total_income'],
            'total_expenses' => $financial['total_expenses'],
            'management_fee' => $financial['management_fee'],
            'net_profit' => $financial['net_profit'],
            'total_transfers' => $transferAmount,
            'remaining_balance' => max(0, $financial['net_profit'] - $transferAmount),
            'transactions' => $financial['transactions'],
            'transfers' => $transfers
        ];

        return $property;
    }

    private function getReportHTMLTemplate($title)
    {
        $baseUrl = base_url();

        return '
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc($title) . '</title>
<style>
/* Local Arabic Font (Noto Naskh Arabic) */
@font-face {
  font-family: "Noto Naskh Arabic";
  src: url("' . $baseUrl . 'assets/fonts/NotoNaskhArabic-Regular.ttf") format("truetype");
  font-weight: normal;
  font-style: normal;
}
@font-face {
  font-family: "Noto Naskh Arabic";
  src: url("' . $baseUrl . 'assets/fonts/NotoNaskhArabic-Bold.ttf") format("truetype");
  font-weight: bold;
  font-style: normal;
}

/* Use ONLY local font (no remote @import for PDF) */
* {
  font-family: "Noto Naskh Arabic", "DejaVu Sans", Arial, sans-serif;
  box-sizing: border-box;
}
body {
  margin: 20px;
  color: #333;
  direction: rtl;
  text-align: right;
  unicode-bidi: embed;
  line-height: 1.6;
  font-size: 14px;
}
.header { 
  text-align: center; 
  border-bottom: 2px solid #007bff; 
  padding-bottom: 20px; 
  margin-bottom: 30px; 
}
.header h1 { 
  margin: 0; 
  color: #007bff; 
  font-weight: bold;
  font-size: 24px;
}
.header p { margin: 5px 0; color: #666; font-size: 12px; }
.property-section { margin-bottom: 40px; page-break-inside: avoid; }
.property-section h2 { color: #007bff; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: right; font-size: 20px; margin: 20px 0 15px 0; }
.property-section h3 { color: #28a745; margin: 25px 0 15px; text-align: right; font-size: 18px; }
.property-section h4 { color: #17a2b8; margin: 20px 0 10px; text-align: right; font-size: 16px; }

.property-details { margin: 15px 0; background: #f8f9fa; padding: 15px; border-radius: 5px; }
.property-details p { margin: 5px 0; text-align: right; }
.property-address { margin: 10px 0; padding: 10px; background: #e9ecef; border-radius: 3px; font-size: 12px; }

table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
th, td { border: 1px solid #ddd; padding: 10px 8px; text-align: right; vertical-align: top; }
th { background-color: #f8f9fa; font-weight: bold; color: #495057; }
tbody tr:nth-child(even) { background-color: #f9f9f9; }
tbody tr:hover { background-color: #e8f4f8; }

.financial-summary, .monthly-summary { 
  background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; 
  border-left: 4px solid #007bff;
}
.transactions-section, .transfers-section {
  margin: 25px 0; padding: 15px; background: #ffffff; border: 1px solid #e9ecef; border-radius: 5px;
}
.units-grid { display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0; }
.unit-badge { background: #e9ecef; padding: 5px 12px; border-radius: 15px; font-size: 12px; border: 1px solid #dee2e6; }

.type-income { color: #28a745; font-weight: bold; background: #d4edda; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
.type-expense { color: #dc3545; font-weight: bold; background: #f8d7da; padding: 2px 6px; border-radius: 3px; font-size: 12px; }

.priority-high { color: #dc3545; font-weight: bold; }
.priority-medium { color: #ffc107; font-weight: bold; }
.priority-low { color: #28a745; font-weight: bold; }
.priority-normal { color: #6c757d; font-weight: bold; }

.status-pending { color: #6c757d; }
.status-approved { color: #007bff; }
.status-in_progress { color: #ffc107; }
.status-completed { color: #28a745; }
.status-rejected { color: #dc3545; }

.report-period { 
  background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); 
  padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #007bff;
}
.portfolio-summary { 
  background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e8 100%); 
  padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #28a745;
}
.maintenance-summary { 
  background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%); 
  padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #ffc107;
}

.empty-state { text-align: center; padding: 30px; color: #6c757d; font-style: italic; background: #f8f9fa; border-radius: 5px; margin: 20px 0; }
.report-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 11px; }
.text-muted { color: #6c757d !important; }

/* Print */
@media print {
  body { margin: 15px; font-size: 12px; }
  .property-section { page-break-inside: avoid; margin-bottom: 30px; }
  .header { margin-bottom: 20px; padding-bottom: 15px; }
  .report-period, .portfolio-summary, .maintenance-summary, .financial-summary, .monthly-summary { page-break-inside: avoid; }
  table { font-size: 11px; }
  th, td { padding: 6px 4px; }
}

/* Keep numbers LTR so they’re readable inside RTL */
.number { direction: ltr; text-align: left; display: inline-block; }
</style>
</head>
<body>
  <div class="header">
    <h1>' . esc($title) . '</h1>
    <p>تم إنشاؤه في ' . date('Y-m-d H:i:s') . '</p>
  </div>
  {{CONTENT}}
</body>
</html>';
    }


    // FIXED PDF SAVING AND DOWNLOAD
    private function savePDFToFile($html, $filename)
    {
        if (!class_exists('\Dompdf\Dompdf')) {
            throw new \Exception('PDF generation library not available.');
        }

        // Ensure upload dir
        $uploadPath = WRITEPATH . 'uploads/reports/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Dompdf options tuned for Arabic & local assets
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Noto Naskh Arabic');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        // Allow loading /assets/* via absolute path
        $options->setChroot(FCPATH); // public/ as the root

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save
        $uniqueFilename = date('Y-m-d_H-i-s') . '_' . uniqid() . '_' . $filename;
        $filePath = $uploadPath . $uniqueFilename;
        file_put_contents($filePath, $dompdf->output());

        return $uniqueFilename;
    }


    // FIXED REPORT LOGGING WITH PDF FILENAME

    private function logReportGeneration($kind, $reportName, $propertyName, $pdfFilename)
    {
        try {
            $db = \Config\Database::connect();
            $this->ensureReportsLogTable($db);

            $insertData = [
                'landlord_id' => $this->getCurrentUserId(),
                'report_kind' => $kind,
                'report_name' => $reportName,
                'property_name' => $propertyName,
                'property_id' => null,
                'generated_date' => date('Y-m-d H:i:s'),
                'pdf_filename' => $pdfFilename,
            ];

            $db->table('reports_log')->insert($insertData);
            return $db->insertID();

        } catch (\Exception $e) {
            log_message('error', 'Error logging report generation: ' . $e->getMessage());
            return null;
        }
    }

    // FIXED MONTHLY REPORT STORAGE

    private function storeMonthlyReportRecord($data, $month, $propertyId)
    {
        try {
            $db = \Config\Database::connect();
            $this->ensureMonthlyReportsTable($db);

            if ($propertyId === 'all' && isset($data['properties'])) {
                // Store record for each property
                foreach ($data['properties'] as $property) {
                    $insertData = [
                        'landlord_id' => $this->getCurrentUserId(),
                        'report_month' => $month,
                        'property_id' => $property['id'],
                        'property_name' => $property['property_name'],
                        'total_income' => $property['monthly_data']['total_income'],
                        'total_expenses' => $property['monthly_data']['total_expenses'],
                        'management_fee' => $property['monthly_data']['management_fee'],
                        'net_profit' => $property['monthly_data']['net_profit'],
                        'remaining_balance' => $property['monthly_data']['remaining_balance'],
                        'is_automatic' => 0,
                        'email_sent' => 0,
                        'generated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Check if record already exists
                    $existing = $db->table('monthly_reports')
                        ->where('landlord_id', $this->getCurrentUserId())
                        ->where('report_month', $month)
                        ->where('property_id', $property['id'])
                        ->get()
                        ->getRowArray();

                    if (!$existing) {
                        $db->table('monthly_reports')->insert($insertData);
                    }
                }
            } else {
                // Single property
                $insertData = [
                    'landlord_id' => $this->getCurrentUserId(),
                    'report_month' => $month,
                    'property_id' => $propertyId,
                    'property_name' => $data['property_name'],
                    'total_income' => $data['monthly_data']['total_income'],
                    'total_expenses' => $data['monthly_data']['total_expenses'],
                    'management_fee' => $data['monthly_data']['management_fee'],
                    'net_profit' => $data['monthly_data']['net_profit'],
                    'remaining_balance' => $data['monthly_data']['remaining_balance'],
                    'is_automatic' => 0,
                    'email_sent' => 0,
                    'generated_at' => date('Y-m-d H:i:s'),
                ];

                // Check if record already exists
                $existing = $db->table('monthly_reports')
                    ->where('landlord_id', $this->getCurrentUserId())
                    ->where('report_month', $month)
                    ->where('property_id', $propertyId)
                    ->get()
                    ->getRowArray();

                if (!$existing) {
                    $db->table('monthly_reports')->insert($insertData);
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Error storing monthly report record: ' . $e->getMessage());
        }
    }

    // UPDATED TABLE CREATION METHODS
    private function ensureReportsLogTable($db)
    {
        try {
            if (!$db->tableExists('reports_log')) {
                $forge = \Config\Database::forge();

                $fields = [
                    'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'landlord_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                    'report_kind' => ['type' => 'VARCHAR', 'constraint' => 100],
                    'report_name' => ['type' => 'VARCHAR', 'constraint' => 255],
                    'property_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                    'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                    'generated_date' => ['type' => 'DATETIME'],
                    'generated_by' => ['type' => 'VARCHAR', 'constraint' => 100],
                    'pdf_filename' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                    'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
                ];

                $forge->addField($fields);
                $forge->addKey('id', true);
                $forge->addKey('landlord_id');
                $forge->addKey('generated_date');
                $forge->createTable('reports_log');
            } else {
                // Check if pdf_filename column exists, if not add it
                $fields = $db->getFieldData('reports_log');
                $hasColumn = false;

                foreach ($fields as $field) {
                    if ($field->name === 'pdf_filename') {
                        $hasColumn = true;
                        break;
                    }
                }

                if (!$hasColumn) {
                    $forge = \Config\Database::forge();
                    $forge->addColumn('reports_log', [
                        'pdf_filename' => [
                            'type' => 'VARCHAR',
                            'constraint' => 255,
                            'null' => true,
                            'after' => 'generated_by'
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating/updating reports_log table: ' . $e->getMessage());
        }
    }

    private function ensureMonthlyReportsTable($db)
    {
        try {
            if (!$db->tableExists('monthly_reports')) {
                $forge = \Config\Database::forge();

                $fields = [
                    'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'landlord_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                    'report_month' => ['type' => 'VARCHAR', 'constraint' => 7], // YYYY-MM
                    'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                    'property_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                    'total_income' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
                    'total_expenses' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
                    'management_fee' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
                    'net_profit' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
                    'remaining_balance' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
                    'is_automatic' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                    'email_sent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                    'generated_at' => ['type' => 'DATETIME'],
                    'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
                ];

                $forge->addField($fields);
                $forge->addKey('id', true);
                $forge->addKey('landlord_id');
                $forge->addKey('report_month');
                $forge->createTable('monthly_reports');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating monthly_reports table: ' . $e->getMessage());
        }
    }

    private function getAllPropertiesOwnershipData()
    {
        $landlordId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        $properties = $db->table('properties p')
            ->select('p.*, ps.shares as my_shares, ps.ownership_percentage as my_percentage')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getResultArray();

        foreach ($properties as &$property) {
            $property['shareholders'] = $this->getPropertyShareholders($property['id']);
            $property['units'] = $this->getPropertyUnits($property['id']);
        }

        return $properties;
    }

    private function getSinglePropertyOwnershipData($propertyId, $includeUnits = false, $includeFinancials = false)
    {
        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            throw new \Exception('Access denied to this property');
        }

        $db = \Config\Database::connect();

        $property = $db->table('properties p')
            ->select('p.*, ps.shares as my_shares, ps.ownership_percentage as my_percentage')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->where('p.id', $propertyId)
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getRowArray();

        if (!$property) {
            throw new \Exception('Property not found');
        }

        $property['shareholders'] = $this->getPropertyShareholders($propertyId);

        if ($includeUnits) {
            $property['units'] = $this->getPropertyUnits($propertyId);
        }

        if ($includeFinancials) {
            $property['financial_summary'] = $this->getPropertyFinancialSummary($propertyId);
        }

        return $property;
    }

    private function getAllPropertiesFinancialData($dateFrom, $dateTo, $includeDistribution = false)
    {
        $landlordId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        $properties = $db->table('properties p')
            ->select('p.*')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->get()
            ->getResultArray();

        $totalIncome = 0;
        $totalExpenses = 0;
        $totalManagementFees = 0;

        foreach ($properties as &$property) {
            $financial = $this->getPropertyFinancialDataForPeriod($property['id'], $dateFrom, $dateTo);
            $property['financial'] = $financial;

            $totalIncome += $financial['total_income'];
            $totalExpenses += $financial['total_expenses'];
            $totalManagementFees += $financial['management_fee'];

            if ($includeDistribution) {
                $property['profit_distribution'] = $this->getPropertyProfitDistribution($property['id'], $financial['net_profit']);
            }
        }

        return [
            'properties' => $properties,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_management_fees' => $totalManagementFees,
                'net_profit' => $totalIncome - $totalExpenses - $totalManagementFees
            ],
            'include_distribution' => $includeDistribution
        ];
    }

    private function getSinglePropertyFinancialData($propertyId, $dateFrom, $dateTo, $includeDistribution = false)
    {
        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            throw new \Exception('Access denied to this property');
        }

        $property = $this->propertyModel->find($propertyId);
        if (!$property) {
            throw new \Exception('Property not found');
        }

        $financial = $this->getPropertyFinancialDataForPeriod($propertyId, $dateFrom, $dateTo);

        $data = [
            'property' => $property,
            'financial' => $financial,
            'include_distribution' => $includeDistribution
        ];

        if ($includeDistribution) {
            $data['profit_distribution'] = $this->getPropertyProfitDistribution($propertyId, $financial['net_profit']);
        }

        return $data;
    }

    private function getPropertyFinancialDataForPeriod($propertyId, $dateFrom, $dateTo)
    {
        $db = \Config\Database::connect();

        // 1) Income/expense transactions table
        $transactions = [];
        if ($db->tableExists('income_expense_payments')) {
            $transactions = $db->table('income_expense_payments iep')
                ->select('iep.*, p.property_name, pu.unit_name')
                ->join('properties p', 'p.id = iep.property_id', 'left')
                ->join('property_units pu', 'pu.id = iep.unit_id', 'left')
                ->where('iep.property_id', $propertyId)
                ->where('iep.date >=', $dateFrom)
                ->where('iep.date <=', $dateTo)
                ->orderBy('iep.date', 'DESC')
                ->get()
                ->getResultArray();
        }

        // 2) Tenant payments (rent etc.) → count as income
        //    Join via unit -> property, and use payment_date for range
        $rentRows = [];
        if ($db->tableExists('payments')) {
            $builder = $db->table('payments pay')
                ->select('pay.amount, pay.payment_date as date, pay.payment_type, pay.description, pu.unit_name')
                ->join('property_units pu', 'pu.id = pay.unit_id', 'left')
                ->where('pu.property_id', $propertyId)
                ->where('pay.payment_date >=', $dateFrom)
                ->where('pay.payment_date <=', $dateTo);

            // If you want *only* rent, uncomment next line:
            // $builder->where('pay.payment_type', 'rent');

            $rentRows = $builder->get()->getResultArray();
        }

        // 3) Sum totals
        $totalIncome = 0.0;
        $totalExpenses = 0.0;

        foreach ($transactions as $t) {
            if (strtolower((string) $t['type']) === 'income') {
                $totalIncome += (float) $t['amount'];
            } else {
                $totalExpenses += (float) $t['amount'];
            }
        }

        $rentIncome = 0.0;
        foreach ($rentRows as $r) {
            $rentIncome += (float) $r['amount'];

            // Also add rent rows to the visible transactions (helps single-property report details)
            $transactions[] = [
                'date' => $r['date'],
                'type' => 'income',
                'description' => $r['description'] ?: ('Tenant Payment' . (!empty($r['payment_type']) ? ' (' . $r['payment_type'] . ')' : '')),
                'unit_name' => $r['unit_name'] ?? null,
                'amount' => (float) $r['amount'],
            ];
        }

        $totalIncome += $rentIncome;

        // Keep transactions sorted (DESC by date)
        usort($transactions, static function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        // 4) Management fee from property settings
        $property = $this->propertyModel->find($propertyId);
        $managementPercentage = (float) ($property['management_percentage'] ?? 0);
        if ($managementPercentage > 1) { // allow "5" meaning 5%
            $managementPercentage = $managementPercentage / 100;
        }
        $managementFee = $totalIncome * $managementPercentage;

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'management_fee' => $managementFee,
            'net_profit' => $totalIncome - $totalExpenses - $managementFee,
            'transactions' => $transactions,                    // used by single-property monthly report
            'property_name' => $property['property_name'] ?? 'Property',
        ];
    }


    private function getPropertyProfitDistribution($propertyId, $netProfit)
    {
        if ($netProfit <= 0) {
            return [];
        }

        $shareholders = $this->getPropertyShareholders($propertyId);
        $distribution = [];

        foreach ($shareholders as $shareholder) {
            $percentage = (float) $shareholder['ownership_percentage'];
            $amount = ($netProfit * $percentage) / 100;

            $distribution[] = [
                'owner_name' => $shareholder['owner_name'],
                'percentage' => $percentage,
                'amount' => $amount
            ];
        }

        return $distribution;
    }

    private function getAllPropertiesMaintenanceData($dateFrom, $dateTo, $status = 'all')
    {
        $landlordId = $this->getCurrentUserId();
        $db = \Config\Database::connect();

        if (!$db->tableExists('maintenance_requests')) {
            return ['requests' => [], 'summary' => ['total_requests' => 0, 'total_cost' => 0]];
        }

        $builder = $db->table('maintenance_requests mr')
            ->select('mr.*, p.property_name, pu.unit_name')
            ->join('properties p', 'p.id = mr.property_id')
            ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
            ->join('property_shareholders ps', 'ps.property_id = p.id')
            ->where('ps.user_id', $landlordId)
            ->where('ps.status', 'active')
            ->where('mr.created_at >=', $dateFrom)
            ->where('mr.created_at <=', $dateTo . ' 23:59:59');

        if ($status !== 'all') {
            $builder->where('mr.status', $status);
        }

        $requests = $builder->orderBy('mr.created_at', 'DESC')->get()->getResultArray();

        $totalCost = 0;
        $statusCounts = [];

        foreach ($requests as $request) {
            $totalCost += (float) ($request['actual_cost'] ?? $request['estimated_cost'] ?? 0);
            $requestStatus = $request['status'];
            $statusCounts[$requestStatus] = ($statusCounts[$requestStatus] ?? 0) + 1;
        }

        return [
            'requests' => $requests,
            'summary' => [
                'total_requests' => count($requests),
                'total_cost' => $totalCost,
                'status_counts' => $statusCounts
            ]
        ];
    }

    private function getSinglePropertyMaintenanceData($propertyId, $dateFrom, $dateTo, $status = 'all')
    {
        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            throw new \Exception('Access denied to this property');
        }

        $db = \Config\Database::connect();

        if (!$db->tableExists('maintenance_requests')) {
            return ['property' => $this->propertyModel->find($propertyId), 'requests' => [], 'summary' => ['total_requests' => 0, 'total_cost' => 0]];
        }

        $property = $this->propertyModel->find($propertyId);

        $builder = $db->table('maintenance_requests mr')
            ->select('mr.*, pu.unit_name')
            ->join('property_units pu', 'pu.id = mr.unit_id', 'left')
            ->where('mr.property_id', $propertyId)
            ->where('mr.created_at >=', $dateFrom)
            ->where('mr.created_at <=', $dateTo . ' 23:59:59');

        if ($status !== 'all') {
            $builder->where('mr.status', $status);
        }

        $requests = $builder->orderBy('mr.created_at', 'DESC')->get()->getResultArray();

        $totalCost = 0;
        $statusCounts = [];

        foreach ($requests as $request) {
            $totalCost += (float) ($request['actual_cost'] ?? $request['estimated_cost'] ?? 0);
            $requestStatus = $request['status'];
            $statusCounts[$requestStatus] = ($statusCounts[$requestStatus] ?? 0) + 1;
        }

        return [
            'property' => $property,
            'requests' => $requests,
            'summary' => [
                'total_requests' => count($requests),
                'total_cost' => $totalCost,
                'status_counts' => $statusCounts
            ]
        ];
    }

    private function getPropertyTransfersForPeriod($propertyId, $dateFrom, $dateTo)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('transfer_receipts')) {
            return [];
        }

        return $db->table('transfer_receipts')
            ->where('property_id', $propertyId)
            ->where('transfer_date >=', $dateFrom)
            ->where('transfer_date <=', $dateTo)
            ->orderBy('transfer_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    // HTML GENERATION METHODS

    private function generateOwnershipReportHTML($properties, $title, $includeUnits = false, $includeFinancials = false)
    {
        $html = $this->getReportHTMLTemplate($title);

        $content = '';
        foreach ($properties as $property) {
            $content .= '<div class="property-section">';
            $content .= '<h2>' . esc($property['property_name']) . '</h2>';
            $content .= '<div class="property-details">';
            $content .= '<p><strong>العنوان:</strong> ' . esc($property['address'] ?? '') . '</p>';
            $content .= '<p><strong>القيمة الإجمالية:</strong> ' . number_format($property['property_value'], 2) . ' ريال</p>';
            $content .= '<p><strong>إجمالي الأسهم:</strong> ' . number_format($property['total_shares']) . '</p>';
            $content .= '</div>';

            // Shareholders table
            if (!empty($property['shareholders'])) {
                $content .= '<h3>المساهمون</h3>';
                $content .= '<table>';
                $content .= '<tr><th>المالك</th><th>الأسهم</th><th>النسبة</th><th>القيمة (ريال)</th></tr>';

                foreach ($property['shareholders'] as $shareholder) {
                    $value = ($shareholder['ownership_percentage'] / 100) * $property['property_value'];
                    $content .= '<tr>';
                    $content .= '<td>' . esc($shareholder['owner_name']) . '</td>';
                    $content .= '<td>' . number_format($shareholder['shares']) . '</td>';
                    $content .= '<td>' . number_format($shareholder['ownership_percentage'], 2) . '%</td>';
                    $content .= '<td>' . number_format($value, 2) . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
            }

            // Units (if requested)
            if ($includeUnits && !empty($property['units'])) {
                $content .= '<h3>الوحدات</h3>';
                $content .= '<div class="units-grid">';
                foreach ($property['units'] as $unit) {
                    $content .= '<span class="unit-badge">' . esc($unit['unit_name']) . '</span>';
                }
                $content .= '</div>';
            }

            // Financials (if requested)
            if ($includeFinancials && !empty($property['financial_summary'])) {
                $fs = $property['financial_summary'];
                $content .= '<h3>الملخص المالي</h3>';
                $content .= '<div class="financial-summary">';
                $content .= '<p><strong>إجمالي الدخل:</strong> ' . number_format($fs['total_income'], 2) . ' ريال</p>';
                $content .= '<p><strong>إجمالي المصروفات:</strong> ' . number_format($fs['total_expenses'], 2) . ' ريال</p>';
                $content .= '<p><strong>رسوم الإدارة:</strong> ' . number_format($fs['management_fee'], 2) . ' ريال</p>';
                $content .= '<p><strong>صافي الربح:</strong> ' . number_format($fs['net_profit'], 2) . ' ريال</p>';
                $content .= '<p><strong>الرصيد المتبقي:</strong> ' . number_format($fs['remaining_balance'], 2) . ' ريال</p>';
                $content .= '</div>';
            }

            $content .= '</div><br><br>';
        }

        return str_replace('{{CONTENT}}', $content, $html);
    }

    private function generateFinancialReportHTML($data, $title, $dateFrom, $dateTo, $includeDistribution = false)
    {
        $html = $this->getReportHTMLTemplate($title);

        $content = '<div class="report-period">';
        $content .= '<p><strong>فترة التقرير:</strong> ' . date('Y-m-d', strtotime($dateFrom)) . ' - ' . date('Y-m-d', strtotime($dateTo)) . '</p>';
        $content .= '</div>';

        // === Portfolio (multiple properties) ===
        if (isset($data['properties']) && isset($data['summary'])) {
            $sum = $data['summary'];

            $content .= '<div class="portfolio-summary">';
            $content .= '<h2>ملخص المحفظة</h2>';
            $content .= '<table>';
            $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
            $content .= '<tr><td>إجمالي الدخل</td><td>' . number_format($sum['total_income'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي المصروفات</td><td>' . number_format($sum['total_expenses'], 2) . '</td></tr>';
            $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($sum['total_management_fees'], 2) . '</td></tr>';
            $content .= '<tr><td><strong>صافي الربح</strong></td><td><strong>' . number_format($sum['net_profit'], 2) . '</strong></td></tr>';
            $content .= '</table>';
            $content .= '</div>';

            // Per-property breakdown
            foreach ($data['properties'] as $property) {
                $fin = $property['financial'];
                $content .= '<div class="property-section">';
                $content .= '<h3>' . esc($property['property_name']) . '</h3>';

                $content .= '<table>';
                $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
                $content .= '<tr><td>الدخل</td><td>' . number_format($fin['total_income'], 2) . '</td></tr>';
                $content .= '<tr><td>المصروفات</td><td>' . number_format($fin['total_expenses'], 2) . '</td></tr>';
                $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($fin['management_fee'], 2) . '</td></tr>';
                $content .= '<tr><td><strong>صافي الربح</strong></td><td><strong>' . number_format($fin['net_profit'], 2) . '</strong></td></tr>';
                $content .= '</table>';

                // Transactions
                if (!empty($fin['transactions'])) {
                    $content .= '<div class="transactions-section">';
                    $content .= '<h4>تفاصيل المعاملات</h4>';
                    $content .= '<table>';
                    $content .= '<tr><th>التاريخ</th><th>النوع</th><th>الوصف</th><th>الوحدة</th><th>المبلغ (ريال)</th></tr>';
                    foreach ($fin['transactions'] as $t) {
                        $content .= '<tr>';
                        $content .= '<td>' . date('Y-m-d', strtotime($t['date'])) . '</td>';
                        $content .= '<td><span class="type-' . $t['type'] . '">' . ($t['type'] === 'income' ? 'دخل' : 'مصروف') . '</span></td>';
                        $content .= '<td>' . esc($t['description']) . '</td>';
                        $content .= '<td>' . esc($t['unit_name'] ?? '') . '</td>';
                        $content .= '<td>' . number_format($t['amount'], 2) . '</td>';
                        $content .= '</tr>';
                    }
                    $content .= '</table>';
                    $content .= '</div>';
                }

                // Profit distribution (optional)
                if (!empty($property['profit_distribution'])) {
                    $content .= '<div class="financial-summary">';
                    $content .= '<h4>توزيع الأرباح</h4>';
                    $content .= '<table>';
                    $content .= '<tr><th>المالك</th><th>النسبة</th><th>المبلغ (ريال)</th></tr>';
                    foreach ($property['profit_distribution'] as $pd) {
                        $content .= '<tr>';
                        $content .= '<td>' . esc($pd['owner_name']) . '</td>';
                        $content .= '<td>' . number_format($pd['percentage'], 2) . '%</td>';
                        $content .= '<td>' . number_format($pd['amount'], 2) . '</td>';
                        $content .= '</tr>';
                    }
                    $content .= '</table>';
                    $content .= '</div>';
                }

                $content .= '</div>'; // property-section
            }

            // === Single property ===
        } elseif (isset($data['property']) && isset($data['financial'])) {
            $property = $data['property'];
            $fin = $data['financial'];

            $content .= '<div class="property-section">';
            $content .= '<h2>' . esc($property['property_name'] ?? 'العقار') . '</h2>';

            $content .= '<div class="financial-summary">';
            $content .= '<h3>الملخص المالي</h3>';
            $content .= '<table>';
            $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
            $content .= '<tr><td>إجمالي الدخل</td><td>' . number_format($fin['total_income'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي المصروفات</td><td>' . number_format($fin['total_expenses'], 2) . '</td></tr>';
            $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($fin['management_fee'], 2) . '</td></tr>';
            $content .= '<tr><td><strong>صافي الربح</strong></td><td><strong>' . number_format($fin['net_profit'], 2) . '</strong></td></tr>';
            $content .= '</table>';
            $content .= '</div>';

            if (!empty($fin['transactions'])) {
                $content .= '<div class="transactions-section">';
                $content .= '<h3>تفاصيل المعاملات</h3>';
                $content .= '<table>';
                $content .= '<tr><th>التاريخ</th><th>النوع</th><th>الوصف</th><th>الوحدة</th><th>المبلغ (ريال)</th></tr>';
                foreach ($fin['transactions'] as $t) {
                    $content .= '<tr>';
                    $content .= '<td>' . date('Y-m-d', strtotime($t['date'])) . '</td>';
                    $content .= '<td><span class="type-' . $t['type'] . '">' . ($t['type'] === 'income' ? 'دخل' : 'مصروف') . '</span></td>';
                    $content .= '<td>' . esc($t['description']) . '</td>';
                    $content .= '<td>' . esc($t['unit_name'] ?? '') . '</td>';
                    $content .= '<td>' . number_format($t['amount'], 2) . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
                $content .= '</div>';
            }

            if (!empty($data['profit_distribution'])) {
                $content .= '<div class="financial-summary">';
                $content .= '<h3>توزيع الأرباح</h3>';
                $content .= '<table>';
                $content .= '<tr><th>المالك</th><th>النسبة</th><th>المبلغ (ريال)</th></tr>';
                foreach ($data['profit_distribution'] as $pd) {
                    $content .= '<tr>';
                    $content .= '<td>' . esc($pd['owner_name']) . '</td>';
                    $content .= '<td>' . number_format($pd['percentage'], 2) . '%</td>';
                    $content .= '<td>' . number_format($pd['amount'], 2) . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
                $content .= '</div>';
            }

            $content .= '</div>'; // property-section
        }

        // Footer
        $content .= '<div class="report-footer"><hr style="margin-top:40px;"><p>تم إنشاء هذا التقرير في ' . date('Y-m-d H:i:s') . ' - نظام إدارة العقارات</p></div>';

        return str_replace('{{CONTENT}}', $content, $html);
    }

    private function generateMaintenanceReportHTML($data, $title, $dateFrom, $dateTo, $status)
    {
        $html = $this->getReportHTMLTemplate($title);

        $content = '<div class="report-period">';
        $content .= '<p><strong>فترة التقرير:</strong> ' . date('Y-m-d', strtotime($dateFrom)) . ' - ' . date('Y-m-d', strtotime($dateTo)) . '</p>';
        $content .= '<p><strong>فلتر الحالة:</strong> ' . ($status === 'all' ? 'جميع الحالات' : $status) . '</p>';
        $content .= '</div>';

        // Summary
        $content .= '<div class="maintenance-summary">';
        $content .= '<h2>الملخص</h2>';
        $content .= '<table>';
        $content .= '<tr><th>المؤشر</th><th>القيمة</th></tr>';
        $content .= '<tr><td>إجمالي الطلبات</td><td>' . $data['summary']['total_requests'] . '</td></tr>';
        $content .= '<tr><td>إجمالي التكلفة</td><td>' . number_format($data['summary']['total_cost'], 2) . ' ريال</td></tr>';

        if (!empty($data['summary']['status_counts'])) {
            foreach ($data['summary']['status_counts'] as $statusName => $count) {
                $arabicStatus = [
                    'pending' => 'معلقة',
                    'approved' => 'موافق عليها',
                    'in_progress' => 'قيد التنفيذ',
                    'completed' => 'مكتملة',
                    'rejected' => 'مرفوضة'
                ];
                $content .= '<tr><td>' . ($arabicStatus[$statusName] ?? $statusName) . '</td><td>' . $count . '</td></tr>';
            }
        }
        $content .= '</table>';
        $content .= '</div>';

        // Requests detail
        if (!empty($data['requests'])) {
            $content .= '<h2>طلبات الصيانة</h2>';
            $content .= '<table>';
            $content .= '<tr><th>التاريخ</th><th>العقار</th><th>الوحدة</th><th>العنوان</th><th>الأولوية</th><th>الحالة</th><th>التكلفة (ريال)</th></tr>';

            foreach ($data['requests'] as $request) {
                $cost = $request['actual_cost'] ?? $request['estimated_cost'] ?? 0;
                $content .= '<tr>';
                $content .= '<td>' . date('Y-m-d', strtotime($request['created_at'])) . '</td>';
                $content .= '<td>' . esc($request['property_name'] ?? '') . '</td>';
                $content .= '<td>' . esc($request['unit_name'] ?? '') . '</td>';
                $content .= '<td>' . esc($request['title']) . '</td>';
                $content .= '<td><span class="priority-' . $request['priority'] . '">' . $request['priority'] . '</span></td>';
                $content .= '<td><span class="status-' . $request['status'] . '">' . $request['status'] . '</span></td>';
                $content .= '<td>' . number_format($cost, 2) . '</td>';
                $content .= '</tr>';
            }
            $content .= '</table>';
        }

        return str_replace('{{CONTENT}}', $content, $html);
    }

    private function generateMonthlyReportHTML($data, $title, $month)
    {
        $html = $this->getReportHTMLTemplate($title);

        $monthName = date('F Y', strtotime($month . '-01'));

        $content = '<div class="report-period">';
        $content .= '<p><strong>شهر التقرير:</strong> ' . $monthName . '</p>';
        $content .= '</div>';

        if (isset($data['properties'])) {
            // Portfolio monthly report - Multiple properties
            $content .= '<div class="portfolio-summary">';
            $content .= '<h2>ملخص المحفظة الشهري</h2>';
            $content .= '<table>';
            $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
            $content .= '<tr><td>إجمالي الدخل</td><td>' . number_format($data['portfolio_totals']['total_income'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي المصروفات</td><td>' . number_format($data['portfolio_totals']['total_expenses'], 2) . '</td></tr>';
            $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($data['portfolio_totals']['total_management_fees'], 2) . '</td></tr>';
            $content .= '<tr><td>صافي الربح</td><td>' . number_format($data['portfolio_totals']['net_profit'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي التحويلات</td><td>' . number_format($data['portfolio_totals']['total_transfers'], 2) . '</td></tr>';
            $content .= '<tr><td><strong>الرصيد المتبقي</strong></td><td><strong>' . number_format($data['portfolio_totals']['remaining_balance'], 2) . '</strong></td></tr>';
            $content .= '</table>';
            $content .= '</div>';

            // Individual property breakdowns
            foreach ($data['properties'] as $property) {
                $content .= '<div class="property-section">';
                $content .= '<h3>' . esc($property['property_name']) . '</h3>';
                $content .= '<div class="property-address">';
                $content .= '<p><small><strong>العنوان:</strong> ' . esc($property['address'] ?? '') . '</small></p>';
                $content .= '</div>';
                $content .= '<table>';
                $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
                $content .= '<tr><td>الدخل</td><td>' . number_format($property['monthly_data']['total_income'], 2) . '</td></tr>';
                $content .= '<tr><td>المصروفات</td><td>' . number_format($property['monthly_data']['total_expenses'], 2) . '</td></tr>';
                $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($property['monthly_data']['management_fee'], 2) . '</td></tr>';
                $content .= '<tr><td>صافي الربح</td><td>' . number_format($property['monthly_data']['net_profit'], 2) . '</td></tr>';
                $content .= '<tr><td>التحويلات</td><td>' . number_format($property['monthly_data']['total_transfers'], 2) . '</td></tr>';
                $content .= '<tr><td><strong>الرصيد المتبقي</strong></td><td><strong>' . number_format($property['monthly_data']['remaining_balance'], 2) . '</strong></td></tr>';
                $content .= '</table>';
                $content .= '</div>';
            }
        } else {
            // Single property monthly report
            $content .= '<div class="property-section">';
            $content .= '<h2>' . esc($data['property_name']) . '</h2>';

            if (!empty($data['address'])) {
                $content .= '<div class="property-address">';
                $content .= '<p><strong>العنوان:</strong> ' . esc($data['address']) . '</p>';
                $content .= '</div>';
            }

            $content .= '<div class="monthly-summary">';
            $content .= '<h3>الملخص المالي الشهري</h3>';
            $content .= '<table>';
            $content .= '<tr><th>المؤشر</th><th>المبلغ (ريال)</th></tr>';
            $content .= '<tr><td>إجمالي الدخل</td><td>' . number_format($data['monthly_data']['total_income'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي المصروفات</td><td>' . number_format($data['monthly_data']['total_expenses'], 2) . '</td></tr>';
            $content .= '<tr><td>رسوم الإدارة</td><td>' . number_format($data['monthly_data']['management_fee'], 2) . '</td></tr>';
            $content .= '<tr><td>صافي الربح</td><td>' . number_format($data['monthly_data']['net_profit'], 2) . '</td></tr>';
            $content .= '<tr><td>إجمالي التحويلات</td><td>' . number_format($data['monthly_data']['total_transfers'], 2) . '</td></tr>';
            $content .= '<tr><td><strong>الرصيد المتبقي</strong></td><td><strong>' . number_format($data['monthly_data']['remaining_balance'], 2) . '</strong></td></tr>';
            $content .= '</table>';
            $content .= '</div>';

            // Transaction details section
            if (!empty($data['monthly_data']['transactions']) && count($data['monthly_data']['transactions']) > 0) {
                $content .= '<div class="transactions-section">';
                $content .= '<h3>تفاصيل المعاملات</h3>';
                $content .= '<table>';
                $content .= '<tr><th>التاريخ</th><th>النوع</th><th>الوصف</th><th>الوحدة</th><th>المبلغ (ريال)</th></tr>';

                foreach ($data['monthly_data']['transactions'] as $transaction) {
                    $typeText = ($transaction['type'] === 'income') ? 'دخل' : 'مصروف';
                    $typeClass = $transaction['type'];

                    $content .= '<tr>';
                    $content .= '<td>' . date('Y-m-d', strtotime($transaction['date'])) . '</td>';
                    $content .= '<td><span class="type-' . $typeClass . '">' . $typeText . '</span></td>';
                    $content .= '<td>' . esc($transaction['description']) . '</td>';
                    $content .= '<td>' . esc($transaction['unit_name'] ?? 'غير محدد') . '</td>';
                    $content .= '<td>' . number_format($transaction['amount'], 2) . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
                $content .= '</div>';
            }

            // Transfer details section
            if (!empty($data['monthly_data']['transfers']) && count($data['monthly_data']['transfers']) > 0) {
                $content .= '<div class="transfers-section">';
                $content .= '<h3>تفاصيل التحويلات</h3>';
                $content .= '<table>';
                $content .= '<tr><th>تاريخ التحويل</th><th>المبلغ (ريال)</th><th>الملاحظات</th></tr>';

                foreach ($data['monthly_data']['transfers'] as $transfer) {
                    $content .= '<tr>';
                    $content .= '<td>' . date('Y-m-d', strtotime($transfer['transfer_date'])) . '</td>';
                    $content .= '<td>' . number_format($transfer['transfer_amount'], 2) . '</td>';
                    $content .= '<td>' . esc($transfer['notes'] ?? 'لا توجد ملاحظات') . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
                $content .= '</div>';
            }

            // Add empty state messages if no transactions or transfers
            if (empty($data['monthly_data']['transactions']) || count($data['monthly_data']['transactions']) == 0) {
                $content .= '<div class="empty-state">';
                $content .= '<p class="text-muted"><em>لا توجد معاملات مسجلة لهذا الشهر</em></p>';
                $content .= '</div>';
            }

            if (empty($data['monthly_data']['transfers']) || count($data['monthly_data']['transfers']) == 0) {
                $content .= '<div class="empty-state">';
                $content .= '<p class="text-muted"><em>لا توجد تحويلات مسجلة لهذا الشهر</em></p>';
                $content .= '</div>';
            }

            $content .= '</div>'; // Close property-section
        }

        // Add footer with report generation info
        $content .= '<div class="report-footer">';
        $content .= '<hr style="margin-top: 40px;">';
        $content .= '<p style="text-align: center; color: #666; font-size: 11px;">';
        $content .= 'تم إنشاء هذا التقرير في ' . date('Y-m-d H:i:s') . ' - نظام إدارة العقارات';
        $content .= '</p>';
        $content .= '</div>';

        return str_replace('{{CONTENT}}', $content, $html);
    }

    //END REPORTS PAGE METHODS - FIXED VERSION

    //HELP PAGE METHODS
    /**
     * Help & Support Page
     */
    public function help()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'title' => 'Help & Support'
        ];

        return view('landlord/help', $data);
    }

    public function sendAdminMessage()
    {
        // Must return JSON — not redirects/views
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
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
                'errors' => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $subject = $this->request->getPost('subject');
        if ($subject === 'Other') {
            $subject = trim((string) $this->request->getPost('custom_subject'));
            if ($subject === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please provide a custom subject'
                ])->setStatusCode(422);
            }
        }

        // (Optional) Persist the message
        $db = \Config\Database::connect();
        if (!$db->tableExists('support_messages')) {
            $db->query("
            CREATE TABLE support_messages (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              user_id INT UNSIGNED NOT NULL,
              subject VARCHAR(150) NOT NULL,
              priority ENUM('normal','high','urgent') DEFAULT 'normal',
              message TEXT NOT NULL,
              created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        }

        $db->table('support_messages')->insert([
            'user_id' => session()->get('user_id'),
            'subject' => $subject,
            'priority' => $this->request->getPost('priority') ?: 'normal',
            'message' => $this->request->getPost('message'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Return new CSRF token in case it regenerated
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Thanks! Your message was sent.',
            'csrf' => [csrf_token() => csrf_hash()],
        ]);
    }

    /**
     * Ensure admin_messages table exists
     */
    private function ensureAdminMessagesTable($db)
    {
        try {
            if (!$db->tableExists('admin_messages')) {
                $forge = \Config\Database::forge();

                $fields = [
                    'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'landlord_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                    'landlord_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                    'landlord_email' => ['type' => 'VARCHAR', 'constraint' => 255],
                    'subject' => ['type' => 'VARCHAR', 'constraint' => 200],
                    'message' => ['type' => 'TEXT'],
                    'priority' => ['type' => 'ENUM', 'constraint' => ['normal', 'high', 'urgent'], 'default' => 'normal'],
                    'status' => ['type' => 'ENUM', 'constraint' => ['unread', 'read', 'replied'], 'default' => 'unread'],
                    'admin_reply' => ['type' => 'TEXT', 'null' => true],
                    'replied_at' => ['type' => 'DATETIME', 'null' => true],
                    'replied_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                    'created_at' => ['type' => 'DATETIME'],
                    'updated_at' => ['type' => 'DATETIME']
                ];

                $forge->addField($fields);
                $forge->addKey('id', true);
                $forge->addKey('landlord_id');
                $forge->addKey('status');
                $forge->addKey('priority');
                $forge->createTable('admin_messages');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating admin_messages table: ' . $e->getMessage());
        }
    }
    //END HELP PAGE METHODS

    //PROFILE PAGE METHODS
    /**
     * Profile Management
     */
    public function profile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);

        try {
            $db = \Config\Database::connect();

            // Get enhanced user statistics
            $propertiesCount = $db->table('property_shareholders ps')
                ->where('ps.user_id', $userId)
                ->countAllResults();

            $totalInvestment = $db->table('properties p')
                ->select('SUM(p.property_value * (ps.ownership_percentage / 100)) as total')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $userId)
                ->get()
                ->getRow()
                ->total ?? 0;

            $totalShares = $db->table('property_shareholders')
                ->selectSum('shares')
                ->where('user_id', $userId)
                ->get()
                ->getRow()
                ->shares ?? 0;

            $stats = [
                'total_properties' => $propertiesCount,
                'total_investment' => $totalInvestment,
                'total_shares' => $totalShares,
                'avg_ownership' => $propertiesCount > 0 ? ($totalShares / $propertiesCount) : 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'Profile stats error: ' . $e->getMessage());
            $stats = [
                'total_properties' => 0,
                'total_investment' => 0,
                'total_shares' => 0,
                'avg_ownership' => 0
            ];
        }

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'stats' => $stats
        ];

        return view('landlord/profile', $data);
    }

    /**
     * Update Profile - Enhanced with better validation
     */
    public function updateProfile()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $userId = $this->getCurrentUserId();

        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
            }
            return redirect()->to('/auth/login');
        }

        // Enhanced validation rules
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]|alpha_space',
            'last_name' => 'required|min_length[2]|max_length[50]|alpha_space',
            'phone' => 'permit_empty|max_length[20]|regex_match[/^[\+\d\s\-\(\)]+$/]',
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

        // Prepare update data
        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $this->userModel->update($userId, $updateData);

            if ($result) {
                // Update session data
                session()->set([
                    'full_name' => $updateData['first_name'] . ' ' . $updateData['last_name']
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Profile updated successfully!'
                    ]);
                }

                $this->setSuccess('Profile updated successfully');
                return redirect()->to('/landlord/profile');
            } else {
                $db = \Config\Database::connect();
                $error = $db->error();

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Database error: ' . ($error['message'] ?? 'Unknown error')
                    ]);
                }

                $this->setError('Failed to update profile: ' . ($error['message'] ?? 'Unknown error'));
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }

            $this->setError('Failed to update profile: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Change Password - Enhanced with better security
     */
    public function changePassword()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $userId = $this->getCurrentUserId();

        if (!$userId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User session not found'
                ]);
            }
            return redirect()->to('/auth/login');
        }

        // Enhanced validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
            }
            return redirect()->back()->with('validation', $this->validator);
        }

        try {
            // Get current user
            $user = $this->userModel->find($userId);

            if (!$user) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'User not found'
                    ]);
                }
                return redirect()->back();
            }

            $currentPasswordInput = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!password_verify($currentPasswordInput, $user['password'])) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ]);
                }
                $this->setError('Current password is incorrect');
                return redirect()->back();
            }

            // Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            if (!$newPasswordHash) {
                throw new \Exception('Failed to hash new password');
            }

            // Update password
            $db = \Config\Database::connect();
            $result = $db->table('users')
                ->where('id', $userId)
                ->update([
                    'password' => $newPasswordHash,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($result) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Password changed successfully!'
                    ]);
                }

                $this->setSuccess('Password changed successfully!');
                return redirect()->to('/landlord/profile');
            } else {
                $error = $db->error();
                throw new \Exception('Database update failed');
            }

        } catch (\Exception $e) {
            log_message('error', 'Password change exception: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }

            $this->setError('Failed to change password: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    //END PROFILE PAGE METHODS

    //HELPER METHODS

    private function verifyPropertyOwnership($propertyId, $userId)
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('property_shareholders')) {
                log_message('warning', 'property_shareholders table does not exist - allowing access');
                return true;
            }

            $builder = $db->table('property_shareholders');
            $builder->where('property_id', $propertyId);
            $builder->where('user_id', $userId);
            $builder->where('status', 'active');

            $shareholder = $builder->get()->getRowArray();
            $hasAccess = !empty($shareholder);

            return $hasAccess;

        } catch (\Exception $e) {
            log_message('error', 'Property ownership verification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getLandlordProperties($landlordId)
    {
        try {
            $db = \Config\Database::connect();

            $properties = $db->table('properties p')
                ->select('p.id, p.property_name, p.address, p.property_value, p.remaining_balance, 
                     p.total_shares, p.created_at, ps.shares, ps.ownership_percentage')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->where('ps.status', 'active')
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($properties as &$property) {
                $property['my_shares'] = $property['shares'];
            }

            return $properties;

        } catch (\Exception $e) {
            log_message('error', 'Error getting landlord properties: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyFinancialSummary($propertyId)
    {
        try {
            $db = \Config\Database::connect();

            $result = $db->query("
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
            FROM income_expense_payments
            WHERE property_id = ?
        ", [$propertyId])->getRowArray();

            $income = (float) ($result['total_income'] ?? 0);
            $expenses = (float) ($result['total_expenses'] ?? 0);

            // Get property for management fee calculation
            $property = $this->propertyModel->find($propertyId);
            $managementFee = 0;
            if ($property) {
                $managementPercentage = (float) ($property['management_percentage'] ?? 0);
                if ($managementPercentage > 1) {
                    $managementPercentage = $managementPercentage / 100;
                }
                $managementFee = $income * $managementPercentage;
            }

            // Get transfers
            $transferResult = $db->query("
            SELECT COALESCE(SUM(transfer_amount), 0) as total_transfers
            FROM transfer_receipts
            WHERE property_id = ?
        ", [$propertyId])->getRowArray();

            $totalTransfers = (float) ($transferResult['total_transfers'] ?? 0);

            return [
                'total_income' => $income,
                'total_expenses' => $expenses,
                'management_fee' => $managementFee,
                'net_profit' => $income - $managementFee - $expenses,
                'total_transfers' => $totalTransfers,
                'remaining_balance' => max(0, ($income - $managementFee - $expenses) - $totalTransfers)
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error getting property financial summary: ' . $e->getMessage());
            return [
                'total_income' => 0,
                'total_expenses' => 0,
                'management_fee' => 0,
                'net_profit' => 0,
                'total_transfers' => 0,
                'remaining_balance' => 0
            ];
        }
    }


    private function createSafeFilename($filename)
    {
        $filename = preg_replace('/[^\w\-_\. ]/', '_', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_.');

        if (empty($filename)) {
            $filename = 'report_' . date('Y-m-d_H-i') . '.pdf';
        }

        $pathinfo = pathinfo($filename);
        $name = $pathinfo['filename'] ?? $filename;
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';

        if (strlen($name) > 200) {
            $name = substr($name, 0, 200);
        }

        return $name . $extension;
    }
    //END HELPER METHODS

    // UTILITY METHODS TO KEEP

    private function ensureRemainingBalanceColumn()
    {
        try {
            $db = \Config\Database::connect();
            $fields = $db->getFieldData('properties');
            $hasRemainingBalance = false;

            foreach ($fields as $field) {
                if ($field->name === 'remaining_balance') {
                    $hasRemainingBalance = true;
                    break;
                }
            }

            if (!$hasRemainingBalance) {
                $forge = \Config\Database::forge();
                $forge->addColumn('properties', [
                    'remaining_balance' => [
                        'type' => 'DECIMAL',
                        'constraint' => '15,2',
                        'default' => 0.00,
                        'null' => true
                    ]
                ]);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', 'Error ensuring remaining_balance column: ' . $e->getMessage());
            return false;
        }
    }

    protected function requireLandlord()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        if ($userRole !== 'landlord') {
            return redirect()->to('/dashboard');
        }

        return null;
    }

    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    protected function setSuccess($message)
    {
        session()->setFlashdata('success', $message);
    }

    protected function setError($message)
    {
        session()->setFlashdata('error', $message);
    }

    private function getCurrentUserName()
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return 'Unknown User';
        }

        $user = $this->userModel->find($userId);

        if ($user) {
            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            return !empty($fullName) ? $fullName : ($user['email'] ?? 'Unknown User');
        }

        return 'Unknown User';
    }
    //END UTILITY METHODS

    // TABLE CREATION METHODS

    private function createPropertyShareholdersTable($db)
    {
        if (!$db->tableExists('property_shareholders')) {
            $forge = \Config\Database::forge();

            $fields = [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'owner_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'owner_email' => ['type' => 'VARCHAR', 'constraint' => 100],
                'shares' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'ownership_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
                'is_primary_owner' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'active', 'inactive'], 'default' => 'active'],
                'invited_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'joined_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'DATE', 'null' => true]
            ];

            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->addKey('property_id');
            $forge->addKey('user_id');

            try {
                $forge->createTable('property_shareholders');
            } catch (\Exception $e) {
                log_message('error', 'Failed to create property_shareholders table: ' . $e->getMessage());
            }
        }
    }

    private function createPropertyUnitsTable($db)
    {
        if (!$db->tableExists('property_units')) {
            $forge = \Config\Database::forge();

            $fields = [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'unit_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ];

            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->addKey('property_id');

            try {
                $forge->createTable('property_units');
            } catch (\Exception $e) {
                log_message('error', 'Failed to create property_units table: ' . $e->getMessage());
            }
        }
    }

    private function createTransferReceiptsTable($db)
    {
        if (!$db->tableExists('transfer_receipts')) {
            $forge = \Config\Database::forge();

            $fields = [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'landlord_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'transfer_date' => ['type' => 'DATE'],
                'transfer_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'receipt_file' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ];

            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->addKey('landlord_id');
            $forge->addKey('property_id');

            try {
                $forge->createTable('transfer_receipts');
            } catch (\Exception $e) {
                log_message('error', 'Failed to create transfer_receipts table: ' . $e->getMessage());
            }
        }
    }

    private function createIncomePaymentsTable($db)
    {
        if (!$db->tableExists('income_expense_payments')) {
            $forge = \Config\Database::forge();

            $fields = [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'landlord_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'property_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'unit_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'date' => ['type' => 'DATE'],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'type' => ['type' => 'ENUM', 'constraint' => ['income', 'expense']],
                'source' => ['type' => 'VARCHAR', 'constraint' => 100],
                'description' => ['type' => 'TEXT'],
                'method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'receipt_file' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ];

            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->addKey(['landlord_id', 'property_id', 'date']);

            try {
                $forge->createTable('income_expense_payments');
            } catch (\Exception $e) {
                log_message('error', 'Failed to create income_expense_payments table: ' . $e->getMessage());
            }
        }
    }
    //END TABLE CREATION METHODS

    // SIMPLIFIED METHODS FOR BASIC FUNCTIONALITY

    private function getPropertyUnitsCount($propertyId)
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('property_units')->where('property_id', $propertyId)->countAllResults();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPropertyOwnersCount($propertyId)
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('property_shareholders')) {
                return 1;
            }
            $count = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->where('status', 'active')
                ->countAllResults();
            return $count > 0 ? $count : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    private function calculatePropertyNetProfit($propertyId)
    {
        try {
            $db = \Config\Database::connect();
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                return 0;
            }

            if (!$db->tableExists('income_expense_payments')) {
                return 0;
            }

            $incomeResult = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM income_expense_payments 
                WHERE property_id = ? AND type = 'income'
            ", [$propertyId])->getRowArray();

            $expenseResult = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM income_expense_payments 
                WHERE property_id = ? AND type = 'expense'
            ", [$propertyId])->getRowArray();

            $totalIncome = (float) ($incomeResult['total'] ?? 0);
            $totalExpenses = (float) ($expenseResult['total'] ?? 0);

            $managementPercentage = (float) ($property['management_percentage'] ?? 0);
            if ($managementPercentage > 1) {
                $managementPercentage = $managementPercentage / 100;
            }
            $managementFees = $totalIncome * $managementPercentage;

            return max(0, $totalIncome - $managementFees - $totalExpenses);

        } catch (\Exception $e) {
            log_message('error', 'Error calculating net profit: ' . $e->getMessage());
            return 0;
        }
    }

    private function verifyUnitOwnership($unitId, $landlordId)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table('property_units pu');
            $builder->select('pu.*, p.property_name');
            $builder->join('properties p', 'p.id = pu.property_id');
            $builder->join('property_shareholders ps', 'ps.property_id = p.id');
            $builder->where('ps.user_id', $landlordId);
            $builder->where('ps.status', 'active');
            $builder->where('pu.id', $unitId);

            return $builder->get()->getRowArray();

        } catch (\Exception $e) {
            log_message('error', 'Error verifying unit ownership: ' . $e->getMessage());
            return null;
        }
    }

    public function getUnitsByProperty($propertyId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access',
                'units' => []
            ]);
        }

        $landlordId = session()->get('user_id');

        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('property_units')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Property units table not found',
                    'units' => []
                ]);
            }

            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have access to this property',
                    'units' => []
                ]);
            }

            $units = $db->table('property_units')
                ->select('id, unit_name')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Units loaded successfully',
                'units' => $units
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get units error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load units: ' . $e->getMessage(),
                'units' => []
            ]);
        }
    }
    //END SIMPILIDIES METHODS

    // File download methods
    /**
 * Download Transfer Receipt File
 */
public function downloadTransferReceipt($filename)
{
    $redirect = $this->requireLandlord();
    if ($redirect) {
        return $redirect;
    }

    try {
        // Security: validate filename
        if (!$filename || strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            throw new \Exception('Invalid filename');
        }

        // Construct file path - assuming receipts are stored in writable/uploads/transfer_receipts/
        $filePath = WRITEPATH . 'uploads/transfer_receipts/' . $filename;
        
        // Check if file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new \Exception('Receipt file not found');
        }

        // Get file info
        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);
        
        // Validate it's a PDF (since your form only accepts PDFs)
        if ($mimeType !== 'application/pdf') {
            throw new \Exception('Invalid file type');
        }

        // Set headers for download
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Length', $fileSize);
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . basename($filename) . '"');
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');
        
        // Output file
        return $this->response->setBody(file_get_contents($filePath));

    } catch (\Exception $e) {
        log_message('error', 'Error downloading transfer receipt: ' . $e->getMessage());
        
        // Show user-friendly error
        session()->setFlashdata('error', 'Receipt file not found or cannot be accessed.');
        return redirect()->back();
    }
}

    public function downloadReceipt($filename)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        $filePath = WRITEPATH . 'uploads/receipts/' . $filename;

        if (!file_exists($filePath)) {
            session()->setFlashdata('error', 'Receipt file not found.');
            return redirect()->back();
        }

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function viewReceiptFile($filename)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        $filePath = WRITEPATH . 'uploads/receipts/' . $filename;

        if (!file_exists($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        $this->response->setContentType('application/pdf');
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
        $this->response->setHeader('Cache-Control', 'public, max-age=3600');

        return $this->response->setBody(file_get_contents($filePath));
    }
    //END FILE DOWNLOAD METHODS

    //HELPER METHODS FOR TRANSFER HISTORY

    private function ensureTransferReceiptsTableExists($db)
    {
        try {
            if (!$db->tableExists('transfer_receipts')) {
                $this->createTransferReceiptsTable($db);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error ensuring transfer_receipts table exists: ' . $e->getMessage());
        }
    }

    private function getPropertyTransfersSafely($propertyId, $landlordId, $db)
    {
        try {
            $builder = $db->table('transfer_receipts');
            $builder->where('property_id', $propertyId);
            $builder->where('landlord_id', $landlordId);
            $builder->orderBy('transfer_date', 'ASC');
            $builder->orderBy('created_at', 'ASC');
            $builder->orderBy('id', 'ASC');

            $query = $builder->get();
            return $query ? $query->getResultArray() : [];

        } catch (\Exception $e) {
            log_message('error', 'Error getting property transfers: ' . $e->getMessage());
            return [];
        }
    }
    //END TRANSFER HISTORY METHODS

    //SIMPLIFIED EMAIL NOTIFICATION
    private function sendShareholderNotificationEmail($email, $name, $propertyName)
    {
        try {
            $emailService = \Config\Services::email();

            $config = [
                'protocol' => 'smtp',
                'SMTPHost' => env('SMTP_HOST', 'localhost'),
                'SMTPUser' => env('SMTP_USER', ''),
                'SMTPPass' => env('SMTP_PASS', ''),
                'SMTPPort' => env('SMTP_PORT', 587),
                'SMTPCrypto' => env('SMTP_CRYPTO', 'tls'),
                'mailType' => 'html',
                'charset' => 'utf-8'
            ];

            $emailService->initialize($config);

            $fromEmail = env('FROM_EMAIL', 'noreply@propertymanagement.com');
            $fromName = env('FROM_NAME', 'Property Management System');

            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($email);
            $emailService->setSubject("You've been added as a shareholder - {$propertyName}");

            $websiteUrl = base_url();
            $loginUrl = base_url('auth/login');

            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #4e73df; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f8f9fc; }
                    .button { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                    .footer { padding: 20px; font-size: 12px; color: #666; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Property Shareholder Notification</h2>
                    </div>
                    <div class='content'>
                        <h3>Hello {$name},</h3>
                        <p>You have been added as a shareholder to the property: <strong>{$propertyName}</strong></p>
                        <p>To view your property details and manage your investment:</p>
                        <p><a href='{$loginUrl}' class='button'>Login to Your Account</a></p>
                        <p>If you don't have an account yet, please register using this email address to access your shareholder dashboard.</p>
                        <p>Website: <a href='{$websiteUrl}'>{$websiteUrl}</a></p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated notification from the Property Management System.</p>
                    </div>
                </div>
            </body>
            </html>";

            $emailService->setMessage($message);

            if ($emailService->send()) {
                log_message('info', "Shareholder notification email sent successfully to {$email}");
                return true;
            } else {
                log_message('error', "Failed to send shareholder notification email to {$email}");
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Email sending exception: " . $e->getMessage());
            return false;
        }
    }

    private function generateMonthlyReportEmailHTML($userName, $property, $monthlyData, $month)
    {
        $monthName = date('F Y', strtotime($month));

        return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 30px 20px; background: #ffffff; }
            .summary-card { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
            .financial-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; }
            .financial-row:last-child { border-bottom: none; font-weight: bold; background: #e8f4f8; padding: 12px; margin: 10px -20px -20px -20px; }
            .amount { font-weight: bold; color: #2c5282; }
            .amount.negative { color: #e53e3e; }
            .amount.positive { color: #38a169; }
            .footer { background: #f7fafc; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
            .attachment-notice { background: #e6fffa; border: 1px solid #81e6d9; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .btn { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Monthly Property Report</h1>
                <h2>{$property['property_name']}</h2>
                <p>Report for {$monthName}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$userName},</p>
                
                <p>This is your automated monthly report for <strong>{$property['property_name']}</strong> covering the period of <strong>{$monthName}</strong>.</p>
                
                <div class='summary-card'>
                    <h3 style='margin-top: 0; color: #667eea;'>Financial Summary</h3>
                    
                    <div class='financial-row'>
                        <span>Total Income:</span>
                        <span class='amount positive'>SAR " . number_format($monthlyData['total_income'], 2) . "</span>
                    </div>
                    
                    <div class='financial-row'>
                        <span>Management Fee:</span>
                        <span class='amount negative'>- SAR " . number_format($monthlyData['management_fee'], 2) . "</span>
                    </div>
                    
                    <div class='financial-row'>
                        <span>Total Expenses:</span>
                        <span class='amount negative'>- SAR " . number_format($monthlyData['total_expenses'], 2) . "</span>
                    </div>
                    
                    <div class='financial-row'>
                        <span>Net Profit:</span>
                        <span class='amount " . ($monthlyData['net_profit'] >= 0 ? 'positive' : 'negative') . "'>SAR " . number_format($monthlyData['net_profit'], 2) . "</span>
                    </div>
                    
                    <div class='financial-row'>
                        <span>Transfers This Month:</span>
                        <span class='amount negative'>- SAR " . number_format($monthlyData['total_transfers'], 2) . "</span>
                    </div>
                    
                    <div class='financial-row'>
                        <span><strong>Current Remaining Balance:</strong></span>
                        <span class='amount " . ($monthlyData['remaining_balance'] >= 0 ? 'positive' : 'negative') . "'><strong>SAR " . number_format($monthlyData['remaining_balance'], 2) . "</strong></span>
                    </div>
                </div>
                
                <div class='attachment-notice'>
                    <h4 style='margin-top: 0;'>📄 Detailed Report Attached</h4>
                    <p>Please find the complete detailed monthly report attached as a PDF file. This includes all income transactions, expenses, transfers, and comprehensive financial analysis for the month.</p>
                </div>
                
                <p>This report was automatically generated on " . date('F j, Y \a\t g:i A') . " as part of our monthly reporting system.</p>
                
                <p>If you have any questions about this report or need additional information, please contact our support team.</p>
                
                <p>Best regards,<br>
                <strong>Property Management System</strong></p>
            </div>
            
            <div class='footer'>
                <p>This is an automated email from the Property Management System.<br>
                Report generated for: {$property['property_name']} | Month: {$monthName}</p>
            </div>
        </div>
    </body>
    </html>";
    }
    //END EMAIL NOTIFICATION METHODS

    // GETTER METHODS FOR VIEW DATA

    private function getPropertyShareholders($propertyId)
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('property_shareholders')) {
                return [];
            }

            return $db->table('property_shareholders ps')
                ->select('ps.*, u.first_name, u.last_name')
                ->join('users u', 'u.id = ps.user_id', 'left')
                ->where('ps.property_id', $propertyId)
                ->orderBy('ps.ownership_percentage', 'DESC')
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error getting property shareholders: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyShareholdersWithLabels($propertyId, $currentUserId)
    {
        try {
            $owners = $this->getPropertyShareholders($propertyId);

            foreach ($owners as &$owner) {
                if (!empty($owner['first_name']) && !empty($owner['last_name'])) {
                    $owner['name'] = trim($owner['first_name'] . ' ' . $owner['last_name']);
                } else {
                    $owner['name'] = $owner['owner_name'] ?? 'Unknown Owner';
                }

                $owner['is_current_user'] = ((int) ($owner['user_id'] ?? 0) === (int) $currentUserId);
                $owner['shares'] = (int) ($owner['shares'] ?? 0);
                $owner['ownership_percentage'] = (float) ($owner['ownership_percentage'] ?? 0);
                $owner['owner_email'] = $owner['owner_email'] ?? '';
                $owner['status'] = $owner['status'] ?? 'active';
                $owner['is_primary_owner'] = (int) ($owner['is_primary_owner'] ?? 0);
            }

            return $owners;

        } catch (\Exception $e) {
            log_message('error', 'Error getting property shareholders with labels: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyShareholdersForEdit($propertyId, $currentUserId)
    {
        try {
            $owners = $this->getPropertyShareholders($propertyId);

            foreach ($owners as &$owner) {
                $owner['is_current_user'] = ((int) ($owner['user_id'] ?? 0) === (int) $currentUserId);

                if (!empty($owner['first_name']) && !empty($owner['last_name'])) {
                    $owner['name'] = trim($owner['first_name'] . ' ' . $owner['last_name']);
                } else {
                    $owner['name'] = $owner['owner_name'] ?? 'Unknown Owner';
                }

                if ($owner['is_current_user']) {
                    $owner['display_name'] = $owner['name'] . ' (You)';
                } else {
                    $owner['display_name'] = $owner['name'];
                }
            }

            return $owners;

        } catch (\Exception $e) {
            log_message('error', 'Error getting property shareholders for edit: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyUnits($propertyId)
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('property_units')) {
                return [];
            }

            $units = $db->table('property_units')
                ->select('id, unit_name, created_at, updated_at')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            return $units;

        } catch (\Exception $e) {
            log_message('error', 'Error getting property units: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyUnitsForEdit($propertyId)
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('property_units')) {
                return [];
            }

            $units = $db->table('property_units')
                ->select('id, property_id, unit_name, created_at, updated_at')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($units as &$unit) {
                $unit['unit_name'] = $unit['unit_name'] ?? 'Unit';
                $unit['id'] = (int) ($unit['id'] ?? 0);
                $unit['property_id'] = (int) ($unit['property_id'] ?? $propertyId);
            }

            return $units;

        } catch (\Exception $e) {
            log_message('error', 'Error getting property units for edit: ' . $e->getMessage());
            return [];
        }
    }

    private function isPropertyCreator($propertyId, $userId)
    {
        try {
            $db = \Config\Database::connect();

            if ($db->tableExists('property_shareholders')) {
                $primaryOwner = $db->table('property_shareholders')
                    ->where([
                        'property_id' => $propertyId,
                        'user_id' => $userId,
                        'is_primary_owner' => 1
                    ])
                    ->get()
                    ->getRowArray();

                return !empty($primaryOwner);
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', 'Error checking property creator: ' . $e->getMessage());
            return false;
        }
    }
    //END METHODS FOR VIEW DATA

}