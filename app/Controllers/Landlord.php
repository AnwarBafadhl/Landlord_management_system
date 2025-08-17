<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Landlord extends BaseController
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
     * Get current user ID
     */
    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    /**
     * Require landlord role
     */
    protected function requireLandlord()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/auth/login');
        }
        return null;
    }

    /**
     * Dashboard - Enhanced with better error handling and statistics
     */
    public function dashboard()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get properties count and summary with better queries
            $propertiesCount = $db->table('property_shareholders ps')
                ->where('ps.user_id', $landlordId)
                ->countAllResults();

            // Get total investment value
            $totalInvestmentQuery = $db->table('properties p')
                ->select('SUM(p.property_value * (ps.ownership_percentage / 100)) as total')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->get();

            $totalInvestment = $totalInvestmentQuery->getRow()->total ?? 0;

            // Get total shares owned
            $totalSharesQuery = $db->table('property_shareholders')
                ->selectSum('shares')
                ->where('user_id', $landlordId)
                ->get();

            $totalShares = $totalSharesQuery->getRow()->shares ?? 0;

            // Get recent properties for quick access
            $recentProperties = $db->table('properties p')
                ->select('p.id, p.property_name, p.property_value, ps.ownership_percentage')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Calculate monthly expected income
            $monthlyIncomeQuery = $db->table('property_units pu')
                ->select('SUM(pu.rent_amount * (ps.ownership_percentage / 100)) as monthly_income')
                ->join('property_shareholders ps', 'ps.property_id = pu.property_id')
                ->where('ps.user_id', $landlordId)
                ->where('pu.status', 'occupied')
                ->get();

            $monthlyIncome = $monthlyIncomeQuery->getRow()->monthly_income ?? 0;

            $data = [
                'title' => 'Dashboard',
                'properties_count' => $propertiesCount,
                'total_investment' => $totalInvestment,
                'total_shares' => $totalShares,
                'monthly_income' => $monthlyIncome,
                'recent_properties' => $recentProperties
            ];

            return view('landlord/dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());
            return view('landlord/dashboard', [
                'title' => 'Dashboard',
                'properties_count' => 0,
                'total_investment' => 0,
                'total_shares' => 0,
                'monthly_income' => 0,
                'recent_properties' => []
            ]);
        }
    }

    /**
     * Request Property Form
     */
    public function requestProperty()
    {
        if ($redirect = $this->requireLandlord())
            return $redirect;

        $currentUser = null;
        $uid = $this->getCurrentUserId();
        if ($uid)
            $currentUser = $this->userModel->find($uid);

        return view('landlord/request_property', [
            'title' => 'Add New Property',
            'validation' => \Config\Services::validation(),
            'currentUser' => $currentUser, // <-- so your view can fill $fullName
        ]);
    }

    /**
     * Add Property - Enhanced for shares-based system with units
     */
    public function addProperty()
    {
        $landlordId = $this->getCurrentUserId();

        // validation
        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'property_value' => 'required|decimal|greater_than[0]',
            'property_address' => 'required|min_length[10]',
            'total_shares' => 'required|integer|greater_than[0]|less_than_equal_to[10000]',
            'contribution_duration' => 'required|integer|greater_than[0]|less_than_equal_to[360]',
            'management_company' => 'permit_empty|min_length[3]|max_length[100]',
            'management_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[50]',
            'owners.0.shares' => 'required|integer|greater_than[0]',
            'total_units' => 'required|integer|greater_than[0]|less_than_equal_to[500]',
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Derivations
            $propertyValue = (float) $this->request->getPost('property_value');
            $totalShares = (int) $this->request->getPost('total_shares');
            $shareValue = $totalShares > 0 ? $propertyValue / $totalShares : 0;

            // Self-managed default if left empty
            $mgmtCompany = trim((string) $this->request->getPost('management_company'));
            $mgmtPctRaw = $this->request->getPost('management_percentage');
            $mgmtPct = ($mgmtCompany === '' || $mgmtPctRaw === null || $mgmtPctRaw === '') ? 0 : (float) $mgmtPctRaw;
            if ($mgmtCompany === '') {
                $mgmtCompany = 'Self-Managed';
            }

            // Insert property
            // in addProperty(), when building $propertyData
            $managementCompany = trim((string) $this->request->getPost('management_company'));
            $managementPercentage = $this->request->getPost('management_percentage');

            $propertyData = [
                'property_name' => $this->request->getPost('property_name'),
                'property_value' => (float) $this->request->getPost('property_value'),
                'address' => $this->request->getPost('property_address'), // <-- now exists in DB
                'total_shares' => (int) $this->request->getPost('total_shares'),
                'share_value' => ((float) $this->request->getPost('property_value')) / max(1, (int) $this->request->getPost('total_shares')),
                'contribution_duration' => (int) $this->request->getPost('contribution_duration'),
                'management_company' => $managementCompany !== '' ? $managementCompany : null,
                'management_percentage' => ($managementPercentage !== '' && $managementPercentage !== null) ? (float) $managementPercentage : null,
                'total_units' => (int) $this->request->getPost('total_units'),
                'status' => 'vacant', // default; also enforced by DB & model hook
            ];

            if (!$this->propertyModel->insert($propertyData)) {
                $modelErr = $this->propertyModel->errors();
                $dbErr = $db->error();
                throw new \Exception(
                    'Failed to create property. '
                    . ($modelErr ? json_encode($modelErr) . ' ' : '')
                    . ($dbErr['message'] ?? '')
                );
            }
            $propertyId = $this->propertyModel->getInsertID();

            // Insert owners (including Owner #1)
            $owners = $this->request->getPost('owners') ?? [];
            if (!is_array($owners) || empty($owners)) {
                throw new \Exception('Owners payload missing.');
            }

            $sumShares = 0;
            foreach ($owners as $o)
                $sumShares += (int) ($o['shares'] ?? 0);
            if ($sumShares > $totalShares) {
                throw new \Exception("Owners' total shares ({$sumShares}) exceed total_shares ({$totalShares}).");
            }

            $ownerRows = [];
            foreach ($owners as $idx => $o) {
                $name = trim((string) ($o['name'] ?? ''));
                $email = trim((string) ($o['email'] ?? ''));
                $shares = (int) ($o['shares'] ?? 0);

                if ($shares <= 0 || ($name === '' && $email === ''))
                    continue;

                $userId = null;
                $existingUser = null;
                if ($email !== '') {
                    $existingUser = $this->userModel->where('email', $email)->first();
                    if ($existingUser) {
                        $userId = $existingUser['id'];
                        if ($name === '') {
                            $name = trim(($existingUser['first_name'] ?? '') . ' ' . ($existingUser['last_name'] ?? ''))
                                ?: ($existingUser['username'] ?? $email);
                        }
                    }
                }
                if ($name === '')
                    $name = $email ?: 'Owner';

                $ownerRows[] = [
                    'property_id' => $propertyId,
                    'user_id' => $userId,
                    'owner_name' => $name,
                    'owner_email' => $email,
                    'shares' => $shares,
                    'ownership_percentage' => ($shares / $totalShares) * 100,
                    'is_primary_owner' => $idx === 0 ? 1 : 0,
                    'status' => $userId ? 'active' : 'pending',
                    // 🔧 removed 'invited_by' (column likely doesn't exist)
                    'joined_at' => date('Y-m-d H:i:s'),
                ];

                if (!$userId && $email) {
                    $this->sendOwnerInvitation($email, $propertyData['property_name']);
                }
            }

            if (!empty($ownerRows)) {
                $db->table('property_shareholders')->insertBatch($ownerRows);
            } else {
                throw new \Exception('No valid owners to insert.');
            }

            // Insert units
            $unitNames = $this->request->getPost('unit_names');
            if (is_array($unitNames)) {
                $unitRows = [];
                foreach ($unitNames as $unitName) {
                    $unitName = trim((string) $unitName);
                    if ($unitName === '')
                        continue;

                    $unitRows[] = [
                        'property_id' => $propertyId,
                        'unit_name' => $unitName,
                        'status' => 'vacant',
                        'rent_amount' => 0,
                        'description' => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        // 🔧 removed 'updated_at' (column often not present on this table)
                    ];
                }
                if (!empty($unitRows)) {
                    $db->table('property_units')->insertBatch($unitRows);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                $err = $db->error();
                throw new \Exception('Transaction failed: ' . ($err['message'] ?? 'unknown DB error'));
            }

            $this->setSuccess('Property added successfully!');
            return redirect()->to('/landlord/properties');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Add property error: ' . $e->getMessage());
            $this->setError('Failed to add property: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Get Properties for Landlord - Enhanced for shares system
     */
    public function properties()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Enhanced query with more details
            $properties = $db->table('properties p')
                ->select('p.*, 
                         ps.shares as my_shares, 
                         ps.ownership_percentage, 
                         (SELECT COUNT(*) FROM property_shareholders ps2 WHERE ps2.property_id = p.id) as total_owners,
                         (SELECT COUNT(*) FROM property_units pu WHERE pu.property_id = p.id) as total_units,
                         (SELECT COUNT(*) FROM property_units pu2 WHERE pu2.property_id = p.id AND pu2.status = "occupied") as occupied_units')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.created_at', 'DESC')
                ->get()
                ->getResultArray();

            // Calculate additional metrics for each property
            foreach ($properties as &$property) {
                $property['occupancy_rate'] = $property['total_units'] > 0
                    ? ($property['occupied_units'] / $property['total_units']) * 100
                    : 0;

                $property['my_investment'] = ($property['property_value'] * $property['ownership_percentage']) / 100;
            }

            $data = [
                'title' => 'My Properties',
                'properties' => $properties
            ];

            return view('landlord/properties', $data);

        } catch (\Exception $e) {
            log_message('error', 'Properties list error: ' . $e->getMessage());
            $this->setError('Failed to load properties');
            return view('landlord/properties', ['title' => 'My Properties', 'properties' => []]);
        }
    }

    /**
     * View Property Details - Enhanced for shares system with units
     */
    public function viewProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get property details
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                $this->setError('Property not found');
                return redirect()->to('/landlord/properties');
            }

            // Verify user has access to this property
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                $this->setError('You do not have access to this property');
                return redirect()->to('/landlord/properties');
            }

            // Get all owners/shareholders
            $owners = $db->table('property_shareholders ps')
                ->select('ps.*, u.first_name, u.last_name')
                ->join('users u', 'u.id = ps.user_id', 'left')
                ->where('ps.property_id', $propertyId)
                ->orderBy('ps.ownership_percentage', 'DESC')
                ->get()
                ->getResultArray();

            // Get property units
            $units = $db->table('property_units')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            // Mark current user and format owner data
            foreach ($owners as &$owner) {
                $owner['is_current_user'] = ($owner['user_id'] == $landlordId);
                if ($owner['first_name'] && $owner['last_name']) {
                    $owner['name'] = $owner['first_name'] . ' ' . $owner['last_name'];
                } else {
                    $owner['name'] = $owner['owner_name'];
                }
            }

            $data = [
                'title' => 'Property Details',
                'property' => $property,
                'owners' => $owners,
                'units' => $units,
                'totalAllocatedShares' => array_sum(array_column($owners, 'shares'))
            ];

            return view('landlord/property_details', $data);

        } catch (\Exception $e) {
            log_message('error', 'View property error: ' . $e->getMessage());
            $this->setError('Failed to load property details');
            return redirect()->to('/landlord/properties');
        }
    }

    /**
     * Edit Property Form - Enhanced for shares system
     */
    public function editProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to edit it.');
            return redirect()->to('/landlord/properties');
        }

        try {
            $db = \Config\Database::connect();

            // Get property details
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                $this->setError('Property not found');
                return redirect()->to('/landlord/properties');
            }

            // Get current owners
            $owners = $db->table('property_shareholders ps')
                ->select('ps.*, u.first_name, u.last_name')
                ->join('users u', 'u.id = ps.user_id', 'left')
                ->where('ps.property_id', $propertyId)
                ->orderBy('ps.ownership_percentage', 'DESC')
                ->get()
                ->getResultArray();

            // Mark current user and format owner data
            foreach ($owners as &$owner) {
                $owner['is_current_user'] = ($owner['user_id'] == $landlordId);
                if ($owner['first_name'] && $owner['last_name']) {
                    $owner['name'] = $owner['first_name'] . ' ' . $owner['last_name'];
                } else {
                    $owner['name'] = $owner['owner_name'];
                }
            }

            $data = [
                'title' => 'Edit Property',
                'property' => $property,
                'owners' => $owners,
                'totalAllocatedShares' => array_sum(array_column($owners, 'shares')),
                'validation' => \Config\Services::validation()
            ];

            return view('landlord/edit_property', $data);

        } catch (\Exception $e) {
            log_message('error', 'Edit property form error: ' . $e->getMessage());
            $this->setError('Failed to load property for editing');
            return redirect()->to('/landlord/properties');
        }
    }

    /**
     * Update Property - Enhanced for shares-based system
     */
    public function updateProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
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
            'management_company' => 'required|min_length[3]|max_length[100]',
            'management_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $propertyValue = $this->request->getPost('property_value');
            $totalShares = $this->request->getPost('total_shares');
            $shareValue = $propertyValue / $totalShares;

            // Update property data
            $propertyData = [
                'property_name' => $this->request->getPost('property_name'),
                'property_value' => $propertyValue,
                'address' => $this->request->getPost('property_address'),
                'total_shares' => $totalShares,
                'share_value' => $shareValue,
                'contribution_duration' => $this->request->getPost('contribution_duration'),
                'management_company' => $this->request->getPost('management_company'),
                'management_percentage' => $this->request->getPost('management_percentage'),
                'status' => $this->request->getPost('status'),
                'description' => $this->request->getPost('description'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!$this->propertyModel->update($propertyId, $propertyData)) {
                throw new \Exception('Failed to update property');
            }

            // Recalculate ownership percentages for all shareholders
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

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $this->setSuccess('Property updated successfully!');
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Update property error: ' . $e->getMessage());
            $this->setError('Failed to update property: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Add Owner to Property
     */
    public function addOwner($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'owner_name' => 'required|min_length[3]|max_length[100]',
            'owner_email' => 'required|valid_email',
            'owner_shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get property details
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                throw new \Exception('Property not found');
            }

            // Check available shares
            $allocatedShares = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->selectSum('shares')
                ->get()
                ->getRow()
                ->shares ?? 0;

            $requestedShares = $this->request->getPost('owner_shares');
            $availableShares = $property['total_shares'] - $allocatedShares;

            if ($requestedShares > $availableShares) {
                throw new \Exception("Only {$availableShares} shares are available");
            }

            // Check if user already exists in the system
            $existingUser = $this->userModel->where('email', $this->request->getPost('owner_email'))->first();
            $userId = $existingUser ? $existingUser['id'] : null;

            // Calculate ownership percentage
            $ownershipPercentage = ($requestedShares / $property['total_shares']) * 100;

            // Add shareholder
            $shareholderData = [
                'property_id' => $propertyId,
                'user_id' => $userId,
                'owner_name' => $this->request->getPost('owner_name'),
                'owner_email' => $this->request->getPost('owner_email'),
                'shares' => $requestedShares,
                'ownership_percentage' => $ownershipPercentage,
                'is_primary_owner' => 0,
                'status' => $userId ? 'active' : 'pending',
                'invited_by' => $landlordId,
                'joined_at' => date('Y-m-d H:i:s')
            ];

            $db->table('property_shareholders')->insert($shareholderData);

            // If user doesn't exist, send invitation email
            if (!$userId) {
                $this->sendOwnerInvitation($this->request->getPost('owner_email'), $property['property_name']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $this->setSuccess('Owner added successfully!');
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Add owner error: ' . $e->getMessage());
            $this->setError('Failed to add owner: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update Owner Shares
     */
    public function updateOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('validation', $this->validator);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get property and current owner details
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                throw new \Exception('Property not found');
            }

            $currentOwner = $db->table('property_shareholders')
                ->where(['id' => $ownerId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$currentOwner) {
                throw new \Exception('Owner not found');
            }

            // Check available shares (excluding current owner's shares)
            $allocatedShares = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->where('id !=', $ownerId)
                ->selectSum('shares')
                ->get()
                ->getRow()
                ->shares ?? 0;

            $requestedShares = $this->request->getPost('shares');
            $availableShares = $property['total_shares'] - $allocatedShares;

            if ($requestedShares > $availableShares) {
                throw new \Exception("Only {$availableShares} shares are available");
            }

            // Calculate new ownership percentage
            $newOwnershipPercentage = ($requestedShares / $property['total_shares']) * 100;

            // Update shareholder
            $updateData = [
                'shares' => $requestedShares,
                'ownership_percentage' => $newOwnershipPercentage,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->update($updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $this->setSuccess('Owner shares updated successfully!');
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Update owner error: ' . $e->getMessage());
            $this->setError('Failed to update owner: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove Owner from Property
     */
    public function removeOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $db = \Config\Database::connect();

        try {
            // Check if owner exists and is not the primary owner
            $owner = $db->table('property_shareholders')
                ->where(['id' => $ownerId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$owner) {
                throw new \Exception('Owner not found');
            }

            if ($owner['is_primary_owner']) {
                throw new \Exception('Cannot remove the primary owner');
            }

            // Remove the owner
            $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->delete();

            $this->setSuccess('Owner removed successfully!');
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Remove owner error: ' . $e->getMessage());
            $this->setError('Failed to remove owner: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Add Unit to Property
     */
    public function addUnit($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]',
            'unit_status' => 'permit_empty|in_list[vacant,occupied,maintenance]',
            'rent_amount' => 'permit_empty|decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        try {
            $db = \Config\Database::connect();

            $unitData = [
                'property_id' => $propertyId,
                'unit_name' => trim($this->request->getPost('unit_name')),
                'status' => $this->request->getPost('unit_status') ?? 'vacant',
                'rent_amount' => $this->request->getPost('rent_amount') ?? 0,
                'description' => trim($this->request->getPost('unit_description') ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('property_units')->insert($unitData)) {
                $this->setSuccess('Unit added successfully!');
            } else {
                $this->setError('Failed to add unit');
            }

            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Add unit error: ' . $e->getMessage());
            $this->setError('Failed to add unit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update Unit
     */
    public function updateUnit($propertyId, $unitId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[100]',
            'unit_status' => 'permit_empty|in_list[vacant,occupied,maintenance]',
            'rent_amount' => 'permit_empty|decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('validation', $this->validator);
        }

        try {
            $db = \Config\Database::connect();

            // Verify unit belongs to this property
            $unit = $db->table('property_units')
                ->where(['id' => $unitId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$unit) {
                throw new \Exception('Unit not found');
            }

            $updateData = [
                'unit_name' => trim($this->request->getPost('unit_name')),
                'status' => $this->request->getPost('unit_status') ?? 'vacant',
                'rent_amount' => $this->request->getPost('rent_amount') ?? 0,
                'description' => trim($this->request->getPost('unit_description') ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('property_units')->where('id', $unitId)->update($updateData)) {
                $this->setSuccess('Unit updated successfully!');
            } else {
                $this->setError('Failed to update unit');
            }

            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Update unit error: ' . $e->getMessage());
            $this->setError('Failed to update unit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove Unit from Property
     */
    public function removeUnit($propertyId, $unitId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        try {
            $db = \Config\Database::connect();

            // Verify unit belongs to this property
            $unit = $db->table('property_units')
                ->where(['id' => $unitId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$unit) {
                throw new \Exception('Unit not found');
            }

            // Check if unit is occupied (optional safety check)
            if ($unit['status'] === 'occupied') {
                $this->setError('Cannot remove an occupied unit. Please vacant the unit first.');
                return redirect()->back();
            }

            if ($db->table('property_units')->where('id', $unitId)->delete()) {
                $this->setSuccess('Unit removed successfully!');
            } else {
                $this->setError('Failed to remove unit');
            }

            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Remove unit error: ' . $e->getMessage());
            $this->setError('Failed to remove unit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Reports Page - Enhanced for shares system
     */
    public function reports()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get user's properties
            $properties = $db->table('properties p')
                ->select('p.id, p.property_name, p.address, p.property_value, p.total_shares')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

            // Get recent reports
            $reports = $this->getRecentGeneratedReports($landlordId, 10);

            $data = [
                'title' => 'Reports & Analytics',
                'properties' => $properties,
                'generated_reports' => $reports
            ];

            return view('landlord/reports', $data);

        } catch (\Exception $e) {
            log_message('error', 'Reports page error: ' . $e->getMessage());
            $this->setError('Failed to load reports page');
            return view('landlord/reports', [
                'title' => 'Reports & Analytics',
                'properties' => [],
                'generated_reports' => []
            ]);
        }
    }

    /**
     * Generate Ownership PDF Report - Enhanced for shares system
     */
    public function generateOwnershipPdf()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();
            $propertyId = $this->request->getPost('property_id');

            // Get property and shareholders data
            if ($propertyId) {
                // Single property report
                $properties = [$this->propertyModel->find($propertyId)];
            } else {
                // All properties report
                $properties = $db->table('properties p')
                    ->join('property_shareholders ps', 'ps.property_id = p.id')
                    ->where('ps.user_id', $landlordId)
                    ->select('p.*')
                    ->groupBy('p.id')
                    ->get()
                    ->getResultArray();
            }

            if (empty($properties)) {
                $this->setError('No properties found for report generation');
                return redirect()->back();
            }

            // Generate report content
            $reportData = [];
            foreach ($properties as $property) {
                $shareholders = $db->table('property_shareholders ps')
                    ->select('ps.*, u.first_name, u.last_name')
                    ->join('users u', 'u.id = ps.user_id', 'left')
                    ->where('ps.property_id', $property['id'])
                    ->orderBy('ps.ownership_percentage', 'DESC')
                    ->get()
                    ->getResultArray();

                $reportData[] = [
                    'property' => $property,
                    'shareholders' => $shareholders
                ];
            }

            // Create PDF content
            $pdfContent = $this->generateOwnershipPdfContent($reportData);

            // Log report generation
            $propertyName = $propertyId ? $properties[0]['property_name'] : 'All Properties';
            $this->logReportGeneration($landlordId, 'ownership', 'Ownership Report - ' . date('Y-m-d H:i'), $propertyName, $propertyId);

            $this->setSuccess('Ownership report generated successfully!');

            // Directly serve the PDF for download
            $filename = sanitize_filename('Ownership_Report_' . date('Y-m-d') . '.pdf');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0')
                ->setBody($pdfContent);

        } catch (\Exception $e) {
            log_message('error', 'Generate ownership PDF error: ' . $e->getMessage());
            $this->setError('Failed to generate report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Income PDF Report - Enhanced version
     */
    /*public function generateIncomePdf()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $propertyId = $this->request->getPost('property_id');
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');
            $totalIncome = $this->request->getPost('total_income') ?? 0;
            $totalExpenses = $this->request->getPost('total_expenses') ?? 0;

            if (!$propertyId) {
                throw new \Exception('Property selection is required');
            }

            // Get property and shareholders
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                throw new \Exception('Property not found');
            }

            $db = \Config\Database::connect();
            $shareholders = $db->table('property_shareholders ps')
                              ->select('ps.*, u.first_name, u.last_name')
                              ->join('users u', 'u.id = ps.user_id', 'left')
                              ->where('ps.property_id', $propertyId)
                              ->orderBy('ps.ownership_percentage', 'DESC')
                              ->get()
                              ->getResultArray();

            // Calculate distributions
            $netIncome = $totalIncome - $totalExpenses;
            $managementFee = ($netIncome * ($property['management_percentage'] / 100));
            $distributableIncome = $netIncome - $managementFee;

            foreach ($shareholders as &$shareholder) {
                $shareholder['distribution'] = $distributableIncome * ($shareholder['ownership_percentage'] / 100);
            }

            // Get report options
            $includeIncomeBreakdown = $this->request->getPost('include_income_breakdown') ? true : false;
            $includeExpenseBreakdown = $this->request->getPost('include_expense_breakdown') ? true : false;
            $includeManagementFees = $this->request->getPost('include_management_fees') ? true : false;
            $includeShareholderDistributions = $this->request->getPost('include_shareholder_distributions') ? true : false;

            // Prepare report data
            $reportData = [
                'property' => $property,
                'shareholders' => $shareholders,
                'period' => ['start' => $startDate, 'end' => $endDate],
                'financial' => [
                    'total_income' => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                    'management_fee' => $managementFee,
                    'distributable_income' => $distributableIncome,
                    'generated_by' => $this->getCurrentUserName()
                ],
                'options' => [
                    'include_income_breakdown' => $includeIncomeBreakdown,
                    'include_expense_breakdown' => $includeExpenseBreakdown,
                    'include_management_fees' => $includeManagementFees,
                    'include_shareholder_distributions' => $includeShareholderDistributions
                ]
            ];

            // Generate PDF content
            $pdfContent = $this->generateIncomePdfContent($reportData);

            // Log report generation
            $this->logReportGeneration($landlordId, 'income', 'Income Report - ' . $property['property_name'] . ' - ' . date('M Y'), $property['property_name'], $propertyId);

            $this->setSuccess('Income report generated successfully!');

            // Directly serve the PDF for download
            $filename = sanitize_filename($property['property_name'] . '_Income_Report_' . date('Y-m-d') . '.pdf');

            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->setHeader('Pragma', 'no-cache')
                        ->setHeader('Expires', '0')
                        ->setBody($pdfContent);

        } catch (\Exception $e) {
            log_message('error', 'Generate income PDF error: ' . $e->getMessage());
            $this->setError('Failed to generate report: ' . $e->getMessage());
            return redirect()->back();
        }
    }*/

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

    // ===============================
    // PRIVATE HELPER METHODS
    // ===============================

    /**
     * Verify Property Ownership - Enhanced method
     */
    private function verifyPropertyOwnership($propertyId, $userId)
    {
        try {
            $db = \Config\Database::connect();

            $access = $db->table('property_shareholders')
                ->where(['property_id' => $propertyId, 'user_id' => $userId])
                ->get()
                ->getRowArray();

            return !empty($access);
        } catch (\Exception $e) {
            log_message('error', 'Error verifying property ownership: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Owner Invitation Email - Enhanced method
     */
    private function sendOwnerInvitation($email, $propertyName)
    {
        try {
            // Initialize email service
            $emailService = \Config\Services::email();

            // Email configuration
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

            // Set email details
            $emailService->setFrom(env('FROM_EMAIL', 'noreply@propertymanagement.com'), 'Property Management System');
            $emailService->setTo($email);
            $emailService->setSubject("Property Investment Invitation - {$propertyName}");

            // Email template
            $message = "
            <h2>Property Investment Invitation</h2>
            <p>You have been invited to join as a shareholder of <strong>{$propertyName}</strong>.</p>
            <p>To accept this invitation and access your shareholding details, please:</p>
            <ol>
                <li>Click the link below to create your account</li>
                <li>Use this email address ({$email}) during registration</li>
                <li>Complete your profile setup</li>
            </ol>
            <p><a href='" . site_url('auth/register?email=' . urlencode($email)) . "' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Accept Invitation</a></p>
            <p>Best regards,<br>Property Management Team</p>
            ";

            $emailService->setMessage($message);

            if ($emailService->send()) {
                log_message('info', "Invitation email sent successfully to {$email} for property {$propertyName}");
                return true;
            } else {
                log_message('error', "Failed to send invitation email to {$email}: " . $emailService->printDebugger());
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Email sending exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate PDF Content for Ownership Report - Enhanced method
     */
    private function generateOwnershipPdfContent($reportData)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px;
                    color: #333;
                    line-height: 1.4;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    padding-bottom: 20px;
                    border-bottom: 2px solid #4e73df;
                }
                .company-info {
                    text-align: right;
                    font-size: 12px;
                    color: #666;
                    margin-bottom: 20px;
                }
                .property-section { 
                    margin-bottom: 40px; 
                    page-break-inside: avoid; 
                }
                .property-title { 
                    font-size: 18px; 
                    font-weight: bold; 
                    color: #4e73df; 
                    margin-bottom: 15px; 
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 20px; 
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 8px; 
                    text-align: left; 
                }
                th { 
                    background-color: #f8f9fc; 
                    font-weight: bold; 
                }
                .conditions { 
                    background-color: #fff3cd; 
                    padding: 15px; 
                    border-radius: 5px; 
                    margin-top: 30px;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #e3e6f0;
                    font-size: 10px;
                    color: #666;
                    text-align: center;
                }
            </style>
        </head>
        <body>';

        $html .= '<div class="company-info">';
        $html .= 'Generated on: ' . date('l, F j, Y \a\t g:i A') . '<br>';
        $html .= 'Report ID: OWN-' . date('Ymd-His') . '<br>';
        $html .= 'Generated by: ' . esc($this->getCurrentUserName());
        $html .= '</div>';

        $html .= '<div class="header">';
        $html .= '<h1>Property Ownership Report</h1>';
        $html .= '<p>Comprehensive Shareholding Analysis</p>';
        $html .= '</div>';

        foreach ($reportData as $data) {
            $property = $data['property'];
            $shareholders = $data['shareholders'];

            $html .= '<div class="property-section">';
            $html .= '<div class="property-title">' . htmlspecialchars($property['property_name']) . '</div>';

            $html .= '<h3>Property Information</h3>';
            $html .= '<table>';
            $html .= '<tr><th width="30%">Property Value</th><td>SAR ' . number_format($property['property_value'], 2) . '</td></tr>';
            $html .= '<tr><th>Address</th><td>' . htmlspecialchars($property['address']) . '</td></tr>';
            $html .= '<tr><th>Total Shares</th><td>' . number_format($property['total_shares']) . '</td></tr>';
            $html .= '<tr><th>Share Value</th><td>SAR ' . number_format($property['share_value'], 2) . '</td></tr>';
            $html .= '<tr><th>Contribution Duration</th><td>' . $property['contribution_duration'] . ' months</td></tr>';
            $html .= '<tr><th>Management Company</th><td>' . htmlspecialchars($property['management_company']) . '</td></tr>';
            $html .= '<tr><th>Management Fee</th><td>' . $property['management_percentage'] . '%</td></tr>';
            $html .= '</table>';

            $html .= '<h3>Shareholders Information</h3>';
            $html .= '<table>';
            $html .= '<tr><th>Name</th><th>Email</th><th>Shares</th><th>Ownership %</th><th>Investment Value</th></tr>';

            foreach ($shareholders as $shareholder) {
                $name = $shareholder['first_name'] && $shareholder['last_name']
                    ? $shareholder['first_name'] . ' ' . $shareholder['last_name']
                    : $shareholder['owner_name'];

                $investmentValue = ($shareholder['shares'] * $property['share_value']);

                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($name) . '</td>';
                $html .= '<td>' . htmlspecialchars($shareholder['owner_email']) . '</td>';
                $html .= '<td>' . number_format($shareholder['shares']) . '</td>';
                $html .= '<td>' . number_format($shareholder['ownership_percentage'], 2) . '%</td>';
                $html .= '<td>SAR ' . number_format($investmentValue, 2) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= '</div>';
        }

        // Add conditions section
        $html .= '<div class="conditions">';
        $html .= '<h2>Shareholders Agreement Conditions</h2>';
        $html .= '<ol>';
        $html .= '<li>Shareholders have no involvement in the property\'s operation at all.</li>';
        $html .= '<li>Any financial income from the property will be distributed to shareholders after deducting expenses.</li>';
        $html .= '<li>In case of any violation, the shareholder\'s contribution amount will be refunded.</li>';
        $html .= '<li>Shareholders are not allowed to sell their shares to anyone outside the current shareholders.</li>';
        $html .= '</ol>';
        $html .= '</div>';

        $html .= '<div class="footer">';
        $html .= '<p><strong>Property Investment Management System</strong><br>';
        $html .= 'This report is automatically generated and contains confidential information.<br>';
        $html .= 'Report generated on ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Get current user's name - Enhanced method
     */
    private function getCurrentUserName()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return 'Unknown User';
            }

            $db = \Config\Database::connect();
            $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

            if ($user) {
                $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                $lastName = $user['last_name'] ?? $user['lastname'] ?? '';
                $fullName = trim($firstName . ' ' . $lastName);

                if (empty($fullName)) {
                    $fullName = $user['username'] ?? $user['email'] ?? 'Unknown User';
                }

                return $fullName;
            }

            return session()->get('username') ?? session()->get('email') ?? 'Unknown User';

        } catch (\Exception $e) {
            log_message('error', 'Error getting current user name: ' . $e->getMessage());
            return 'Unknown User';
        }
    }

    /**
     * Get recent generated reports - Enhanced method
     */
    private function getRecentGeneratedReports($landlordId, $limit = 10)
    {
        try {
            $db = \Config\Database::connect();

            // Check if table exists
            if (!$db->tableExists('reports_log')) {
                $this->createReportsLogTable();
                return [];
            }

            $builder = $db->table('reports_log');
            $builder->where('landlord_id', $landlordId);
            $builder->orderBy('generated_date', 'DESC');
            $builder->limit($limit);

            return $builder->get()->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error getting recent reports: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Log report generation - Enhanced method
     */
    private function logReportGeneration($landlordId, $reportKind, $reportName, $propertyName, $propertyId = null)
    {
        try {
            $db = \Config\Database::connect();

            // Create reports_log table if it doesn't exist
            $this->createReportsLogTable();

            $data = [
                'landlord_id' => $landlordId,
                'report_kind' => $reportKind,
                'report_name' => $reportName,
                'property_name' => $propertyName,
                'property_id' => $propertyId,
                'generated_date' => date('Y-m-d H:i:s'),
                'generated_by' => $this->getCurrentUserName()
            ];

            $result = $db->table('reports_log')->insert($data);

            if ($result) {
                log_message('info', 'Report logged successfully: ' . $reportName);
            } else {
                log_message('error', 'Failed to log report: ' . $reportName);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to log report generation: ' . $e->getMessage());
            // Don't stop report generation if logging fails
        }
    }

    /**
     * Create reports log table if it doesn't exist - Enhanced method
     */
    private function createReportsLogTable()
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('reports_log')) {
                $forge = \Config\Database::forge();

                $fields = [
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'landlord_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true
                    ],
                    'report_kind' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100
                    ],
                    'report_name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255
                    ],
                    'property_name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255
                    ],
                    'property_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true
                    ],
                    'generated_date' => [
                        'type' => 'DATETIME'
                    ],
                    'generated_by' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100
                    ]
                ];

                $forge->addField($fields);
                $forge->addKey('id', true);
                $forge->addKey('landlord_id');
                $forge->addKey('generated_date');
                $forge->createTable('reports_log');

                log_message('info', 'Created reports_log table successfully');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating reports_log table: ' . $e->getMessage());
        }
    }

    // ===============================
    // EXISTING METHODS (Enhanced)
    // ===============================

    /**
     * View Tenants - Enhanced method
     */
    public function tenants()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get tenants from property units and leases
            $tenants = $db->table('property_units pu')
                ->select('pu.*, p.property_name, p.address as property_address, ps.ownership_percentage,
                         u.first_name, u.last_name, u.email, u.phone')
                ->join('properties p', 'p.id = pu.property_id')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->join('users u', 'u.id = pu.tenant_id', 'left')
                ->where('ps.user_id', $landlordId)
                ->where('pu.status', 'occupied')
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

            // Get properties for dropdown
            $properties = $db->table('properties p')
                ->select('p.id, p.property_name')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Tenants page error: ' . $e->getMessage());
            $tenants = [];
            $properties = [];
        }

        $data = [
            'title' => 'My Tenants',
            'tenants' => $tenants,
            'properties' => $properties
        ];

        return view('landlord/tenants', $data);
    }

    /**
     * View Maintenance Requests - Enhanced method
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

            // Get maintenance requests for landlord's properties
            $maintenance_requests = $db->table('maintenance_requests mr')
                ->select('mr.*, p.property_name, pu.unit_name,
                         u.first_name, u.last_name, u.email as tenant_email')
                ->join('property_units pu', 'pu.id = mr.unit_id')
                ->join('properties p', 'p.id = pu.property_id')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->join('users u', 'u.id = mr.tenant_id', 'left')
                ->where('ps.user_id', $landlordId)
                ->orderBy('mr.created_at', 'DESC')
                ->get()
                ->getResultArray();

            // Get properties for filtering
            $properties = $db->table('properties p')
                ->select('p.id, p.property_name')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Maintenance page error: ' . $e->getMessage());
            $maintenance_requests = [];
            $properties = [];
        }

        $data = [
            'title' => 'Maintenance Requests',
            'maintenance_requests' => $maintenance_requests,
            'properties' => $properties
        ];

        return view('landlord/maintenance', $data);
    }

    /**
     * View Payments - Enhanced method
     */
    public function payments()
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get payments for landlord's properties with ownership calculation
            $payments = $db->table('payments pay')
                ->select('pay.*, p.property_name, pu.unit_name,
                         u.first_name, u.last_name, u.email as tenant_email,
                         ps.ownership_percentage,
                         (pay.amount * ps.ownership_percentage / 100) as my_share')
                ->join('property_units pu', 'pu.id = pay.unit_id')
                ->join('properties p', 'p.id = pu.property_id')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->join('users u', 'u.id = pay.tenant_id', 'left')
                ->where('ps.user_id', $landlordId)
                ->orderBy('pay.payment_date', 'DESC')
                ->get()
                ->getResultArray();

            // Get properties for filtering
            $properties = $db->table('properties p')
                ->select('p.id, p.property_name')
                ->join('property_shareholders ps', 'ps.property_id = p.id')
                ->where('ps.user_id', $landlordId)
                ->orderBy('p.property_name', 'ASC')
                ->get()
                ->getResultArray();

            // Apply filters
            $month = $this->request->getGet('month');
            $year = $this->request->getGet('year');
            $status = $this->request->getGet('status');
            $property_id = $this->request->getGet('property_id');

            if ($month || $year || $status || $property_id) {
                $payments = array_filter($payments, function ($payment) use ($month, $year, $status, $property_id) {
                    $paymentDate = strtotime($payment['payment_date']);
                    $matchMonth = !$month || date('m', $paymentDate) == $month;
                    $matchYear = !$year || date('Y', $paymentDate) == $year;
                    $matchStatus = !$status || $payment['status'] == $status;
                    $matchProperty = !$property_id || $payment['property_id'] == $property_id;

                    return $matchMonth && $matchYear && $matchStatus && $matchProperty;
                });
            }

            // Calculate payment statistics
            $payment_stats = $this->calculateEnhancedPaymentStats($payments);

        } catch (\Exception $e) {
            log_message('error', 'Payments page error: ' . $e->getMessage());
            $payments = [];
            $properties = [];
            $payment_stats = [];
        }

        $data = [
            'title' => 'Payment History',
            'payments' => array_values($payments),
            'properties' => $properties,
            'payment_stats' => $payment_stats,
            'chart_data' => $this->getPaymentChartData($payments)
        ];

        return view('landlord/payments', $data);
    }

    /**
     * Calculate enhanced payment statistics
     */
    private function calculateEnhancedPaymentStats($payments)
    {
        if (empty($payments)) {
            return [
                'this_month_collected' => 0,
                'this_month_expected' => 0,
                'outstanding' => 0,
                'year_to_date' => 0,
                'total_collected' => 0,
                'average_payment' => 0,
                'my_total_share' => 0
            ];
        }

        $currentMonth = date('Y-m');
        $currentYear = date('Y');

        $stats = [
            'this_month_collected' => 0,
            'this_month_expected' => 0,
            'outstanding' => 0,
            'year_to_date' => 0,
            'total_collected' => 0,
            'average_payment' => 0,
            'my_total_share' => 0
        ];

        $paidPayments = array_filter($payments, function ($p) {
            return $p['status'] === 'paid';
        });

        $thisMonthPaid = array_filter($paidPayments, function ($p) use ($currentMonth) {
            return strpos($p['payment_date'], $currentMonth) === 0;
        });

        $thisYearPaid = array_filter($paidPayments, function ($p) use ($currentYear) {
            return strpos($p['payment_date'], $currentYear) === 0;
        });

        $stats['this_month_collected'] = array_sum(array_column($thisMonthPaid, 'my_share'));
        $stats['year_to_date'] = array_sum(array_column($thisYearPaid, 'my_share'));
        $stats['total_collected'] = array_sum(array_column($paidPayments, 'my_share'));
        $stats['my_total_share'] = array_sum(array_column($payments, 'my_share'));
        $stats['average_payment'] = count($paidPayments) > 0 ? $stats['total_collected'] / count($paidPayments) : 0;

        $outstandingPayments = array_filter($payments, function ($p) {
            return $p['status'] !== 'paid';
        });
        $stats['outstanding'] = array_sum(array_column($outstandingPayments, 'my_share'));

        return $stats;
    }

    /**
     * Helper method to get payment chart data
     */
    private function getPaymentChartData($payments)
    {
        $monthlyData = [];
        $paidPayments = array_filter($payments, function ($p) {
            return $p['status'] === 'paid';
        });

        foreach ($paidPayments as $payment) {
            $month = date('M Y', strtotime($payment['payment_date']));
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = 0;
            }
            $monthlyData[$month] += $payment['my_share'] ?? 0;
        }

        return [
            'labels' => array_keys($monthlyData),
            'data' => array_values($monthlyData)
        ];
    }

    // ===============================
    // UTILITY METHODS
    // ===============================

    /**
     * Helper Methods for Error Handling
     */
    protected function respondWithSuccess($data = [], $message = 'Success')
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }

        session()->setFlashdata('success', $message);
        return redirect()->back();
    }

    protected function respondWithError($message = 'Error occurred', $code = 500)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($code)->setJSON([
                'success' => false,
                'message' => $message
            ]);
        }

        session()->setFlashdata('error', $message);
        return redirect()->back();
    }

    protected function setSuccess($message)
    {
        session()->setFlashdata('success', $message);
    }

    protected function setError($message)
    {
        session()->setFlashdata('error', $message);
    }

    /**
     * Check Database - Enhanced diagnostic method
     */
    public function checkDatabase()
    {
        try {
            $db = \Config\Database::connect();

            echo "<h3>Database Connection Test</h3>";
            $result = $db->query("SELECT 1")->getResult();
            echo "✅ Database connection successful!<br><br>";

            echo "<h3>Required Tables Check</h3>";
            $requiredTables = [
                'properties' => [
                    'id',
                    'property_name',
                    'property_value',
                    'address',
                    'total_shares',
                    'share_value',
                    'contribution_duration',
                    'management_company',
                    'management_percentage',
                    'total_units',
                    'status',
                    'created_at',
                    'updated_at'
                ],
                'property_shareholders' => [
                    'id',
                    'property_id',
                    'user_id',
                    'owner_name',
                    'owner_email',
                    'shares',
                    'ownership_percentage',
                    'is_primary_owner',
                    'status',
                    'joined_at'
                ],
                'property_units' => [
                    'id',
                    'property_id',
                    'unit_name',
                    'status',
                    'rent_amount',
                    'description',
                    'created_at'
                ],
                'reports_log' => [
                    'id',
                    'landlord_id',
                    'report_kind',
                    'report_name',
                    'property_name',
                    'generated_date'
                ]
            ];

            foreach ($requiredTables as $tableName => $expectedColumns) {
                if ($db->tableExists($tableName)) {
                    echo "✅ Table '$tableName' exists<br>";

                    $fields = $db->getFieldNames($tableName);
                    $missingColumns = array_diff($expectedColumns, $fields);
                    $extraColumns = array_diff($fields, $expectedColumns);

                    if (empty($missingColumns)) {
                        echo "&nbsp;&nbsp;&nbsp;✅ All required columns present<br>";
                    } else {
                        echo "&nbsp;&nbsp;&nbsp;❌ Missing columns: " . implode(', ', $missingColumns) . "<br>";
                    }

                    if (!empty($extraColumns)) {
                        echo "&nbsp;&nbsp;&nbsp;ℹ️ Extra columns: " . implode(', ', $extraColumns) . "<br>";
                    }

                } else {
                    echo "❌ Table '$tableName' does not exist<br>";
                }
                echo "<br>";
            }

            echo "<h3>✅ Database check completed!</h3>";
            echo "<p><a href='" . site_url('landlord/dashboard') . "'>Return to Dashboard</a></p>";

        } catch (\Exception $e) {
            echo "❌ Database check failed: " . $e->getMessage() . "<br>";
        }
    }
}