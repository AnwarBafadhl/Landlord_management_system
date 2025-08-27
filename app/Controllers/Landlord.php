<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PropertyModel;
use App\Models\LeaseModel;
use App\Models\PaymentModel;
use App\Models\MaintenanceRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

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
        // Require landlord session
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = (int) $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            /** 1) Property details */
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                $this->setError('Property not found');
                return redirect()->to('/landlord/properties');
            }

            /** 2) Access check */
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                $this->setError('You do not have access to this property');
                return redirect()->to('/landlord/properties');
            }

            /** 3) Shareholders (note the stable id alias) */
            $owners = $db->table('property_shareholders ps')
                ->select('ps.*, ps.id AS shareholder_id, u.first_name, u.last_name')
                ->join('users u', 'u.id = ps.user_id', 'left')
                ->where('ps.property_id', $propertyId)
                ->orderBy('ps.ownership_percentage', 'DESC')
                ->get()
                ->getResultArray();

            // Enrich owners with flags and display name
            foreach ($owners as &$owner) {
                $owner['is_current_user'] = ((int) ($owner['user_id'] ?? 0) === $landlordId);

                if (!empty($owner['first_name']) && !empty($owner['last_name'])) {
                    $owner['name'] = trim($owner['first_name'] . ' ' . $owner['last_name']);
                } else {
                    $owner['name'] = $owner['owner_name'] ?? '—';
                }
            }
            unset($owner);

            /** 4) Determine if current user is the creator */
            $isCreator = false;

            // Preferred: properties.created_by points to users.id
            if (!empty($property['created_by']) && (int) $property['created_by'] === $landlordId) {
                $isCreator = true;
            } else {
                // Fallback: current user is the primary owner on this property
                foreach ($owners as $o) {
                    if ((int) ($o['is_primary_owner'] ?? 0) === 1 && (int) ($o['user_id'] ?? 0) === $landlordId) {
                        $isCreator = true;
                        break;
                    }
                }
            }

            /** 5) Units */
            $units = $db->table('property_units')
                ->where('property_id', $propertyId)
                ->orderBy('unit_name', 'ASC')
                ->get()
                ->getResultArray();

            /** 6) Totals */
            $totalAllocatedShares = is_array($owners) && $owners
                ? array_sum(array_map(static fn($o) => (int) ($o['shares'] ?? 0), $owners))
                : 0;

            /** 7) Pack view data */
            $data = [
                'title' => 'Property Details',
                'property' => $property,
                'owners' => $owners,             // includes shareholder_id
                'isCreator' => $isCreator,          // for view logic
                'currentUserId' => $landlordId,         // if needed client-side
                'units' => $units,
                'totalAllocatedShares' => $totalAllocatedShares,
            ];

            return view('landlord/property_details', $data);

        } catch (\Throwable $e) {
            log_message('error', 'View property error: ' . $e->getMessage());
            $this->setError('Failed to load property details');
            return redirect()->to('/landlord/properties');
        }
    }


    /**
     * Alternative method if you don't have property_shareholders table yet
     */
    public function editPropertySimple($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            // Simple property fetch without shareholders
            $property = $this->propertyModel->find($propertyId);

            if (!$property) {
                $this->setError('Property not found');
                return redirect()->to('/landlord/properties');
            }

            // Simple ownership check - you might have a different field
            // Adjust this based on your actual properties table structure
            if (isset($property['created_by']) && $property['created_by'] != $landlordId) {
                $this->setError('You do not have permission to edit this property');
                return redirect()->to('/landlord/properties');
            }

            $data = [
                'title' => 'Edit Property',
                'property' => $property,
                'owners' => [], // Empty for now
                'totalAllocatedShares' => 0,
                'validation' => \Config\Services::validation()
            ];

            return view('landlord/edit_property', $data);

        } catch (\Exception $e) {
            log_message('error', 'Edit property simple error: ' . $e->getMessage());
            $this->setError('Failed to load property for editing');
            return redirect()->to('/landlord/properties');
        }
    }

    /**
     * Update Property - FIXED VERSION that actually updates
     */
    public function updateProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        // Debug logging
        log_message('debug', "Update Property called - PropertyID: {$propertyId}, LandlordID: {$landlordId}");
        log_message('debug', "POST data: " . json_encode($this->request->getPost()));

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to edit it.');
            return redirect()->to('/landlord/properties');
        }

        // FIXED: Updated validation rules to match form fields
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
            // Get form data
            $propertyValue = (float) $this->request->getPost('property_value');
            $totalShares = (int) $this->request->getPost('total_shares');
            $shareValue = $propertyValue / $totalShares;

            // FIXED: Check if we can reduce total shares
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

            // FIXED: Update property data with correct field names
            $propertyData = [
                'property_name' => $this->request->getPost('property_name'),
                'property_value' => $propertyValue,
                'address' => $this->request->getPost('property_address'), // Note: field name is 'address' not 'property_address'
                'total_shares' => $totalShares,
                'share_value' => $shareValue,
                'contribution_duration' => $this->request->getPost('contribution_duration'),
                'management_company' => $this->request->getPost('management_company') ?: null,
                'management_percentage' => $this->request->getPost('management_percentage') ?: 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('debug', "Updating property with data: " . json_encode($propertyData));

            // Update the property
            if (!$this->propertyModel->update($propertyId, $propertyData)) {
                throw new \Exception('Failed to update property in database');
            }

            // FIXED: Recalculate ownership percentages for all shareholders
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


    // PART 2: Enhanced editProperty method to include units

    /**
     * Edit Property - ENHANCED with Units Management
     */
    public function editProperty($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        try {
            $db = \Config\Database::connect();

            // Get the specific property
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                $this->setError('Property not found');
                return redirect()->to('/landlord/properties');
            }

            // Verify ownership
            if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                $this->setError('Property not found or you do not have permission to edit it.');
                return redirect()->to('/landlord/properties');
            }

            // Get shareholders
            $owners = [];
            $totalAllocatedShares = 0;

            if ($db->tableExists('property_shareholders')) {
                try {
                    $shareholders = $db->table('property_shareholders ps')
                        ->select('
                        ps.id,
                        ps.property_id,
                        ps.user_id,
                        ps.owner_name,
                        ps.owner_email,
                        ps.shares,
                        ps.ownership_percentage,
                        ps.is_primary_owner,
                        ps.status,
                        u.first_name,
                        u.last_name,
                        u.email as user_email
                    ')
                        ->join('users u', 'u.id = ps.user_id', 'left')
                        ->where('ps.property_id', $propertyId)
                        ->orderBy('ps.ownership_percentage', 'DESC')
                        ->get()
                        ->getResultArray();

                    foreach ($shareholders as $shareholder) {
                        $displayName = '';
                        if (!empty($shareholder['first_name']) && !empty($shareholder['last_name'])) {
                            $displayName = trim($shareholder['first_name'] . ' ' . $shareholder['last_name']);
                        } else {
                            $displayName = $shareholder['owner_name'] ?? 'Unknown Owner';
                        }

                        $email = $shareholder['user_email'] ?? $shareholder['owner_email'] ?? '';

                        $owners[] = [
                            'id' => $shareholder['id'],
                            'user_id' => $shareholder['user_id'],
                            'property_id' => $shareholder['property_id'],
                            'name' => $displayName,
                            'owner_name' => $shareholder['owner_name'],
                            'email' => $email,
                            'owner_email' => $shareholder['owner_email'],
                            'shares' => (int) $shareholder['shares'],
                            'ownership_percentage' => (float) $shareholder['ownership_percentage'],
                            'is_primary_owner' => (int) $shareholder['is_primary_owner'],
                            'status' => $shareholder['status'] ?? 'active',
                            'is_current_user' => ($shareholder['user_id'] == $landlordId)
                        ];

                        $totalAllocatedShares += (int) $shareholder['shares'];
                    }
                } catch (\Exception $dbError) {
                    log_message('error', 'Database error fetching shareholders: ' . $dbError->getMessage());
                    $owners = [];
                    $totalAllocatedShares = 0;
                }
            }

            // NEW: Get property units
            $units = [];
            if ($db->tableExists('property_units')) {
                try {
                    $units = $db->table('property_units')
                        ->where('property_id', $propertyId)
                        ->orderBy('unit_name')
                        ->get()
                        ->getResultArray();
                } catch (\Exception $dbError) {
                    log_message('error', 'Database error fetching units: ' . $dbError->getMessage());
                    $units = [];
                }
            }

            // Prepare view data
            $data = [
                'title' => 'Edit Property - ' . $property['property_name'],
                'property' => $property,
                'owners' => $owners,
                'totalAllocatedShares' => $totalAllocatedShares,
                'units' => $units, // NEW: Add units to view data
                'validation' => \Config\Services::validation()
            ];

            return view('landlord/edit_property', $data);

        } catch (\Exception $e) {
            log_message('error', 'Edit property error: ' . $e->getMessage());
            $this->setError('Failed to load property for editing: ' . $e->getMessage());
            return redirect()->to('/landlord/properties');
        }
    }

    public function addOwner($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

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
        $db->transBegin();

        try {
            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                throw new \Exception('Property not found');
            }

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

            $existingUser = $this->userModel->where('email', $this->request->getPost('owner_email'))->first();
            $userId = $existingUser ? $existingUser['id'] : null;

            $ownershipPercentage = ($requestedShares / $property['total_shares']) * 100;

            $shareholderData = [
                'property_id' => $propertyId,
                'user_id' => $userId,
                'owner_name' => $this->request->getPost('owner_name'),
                'owner_email' => $this->request->getPost('owner_email'),
                'shares' => $requestedShares,
                'ownership_percentage' => $ownershipPercentage,
                'is_primary_owner' => 0,
                'status' => $userId ? 'active' : 'pending',
                'joined_at' => date('Y-m-d H:i:s')
            ];

            $db->table('property_shareholders')->insert($shareholderData);
            $db->transCommit();

            $this->setSuccess('Shareholder added successfully!');
            // FIXED: Redirect to property details
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Add owner error: ' . $e->getMessage());
            $this->setError('Failed to add shareholder: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update Owner Shares - FIXED redirect
     */
    public function updateOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        $rules = [
            'shares' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            $this->setError('Please enter valid share information.');
            return redirect()->back()->withInput();
        }

        $db = \Config\Database::connect();

        try {
            $db->transBegin();

            $property = $this->propertyModel->find($propertyId);
            if (!$property) {
                throw new \Exception('Property not found');
            }

            $currentOwner = $db->table('property_shareholders')
                ->where(['id' => $ownerId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$currentOwner) {
                throw new \Exception("Shareholder not found");
            }

            $otherShareholdersShares = $db->table('property_shareholders')
                ->where('property_id', $propertyId)
                ->where('id !=', $ownerId)
                ->selectSum('shares')
                ->get()
                ->getRow()
                ->shares ?? 0;

            $requestedShares = (int) $this->request->getPost('shares');
            $availableShares = $property['total_shares'] - $otherShareholdersShares;

            if ($requestedShares > $availableShares) {
                throw new \Exception("Only {$availableShares} shares are available");
            }

            if ($requestedShares < 1) {
                throw new \Exception("Shares must be at least 1");
            }

            $newOwnershipPercentage = ($requestedShares / $property['total_shares']) * 100;

            $updateData = [
                'shares' => $requestedShares,
                'ownership_percentage' => $newOwnershipPercentage,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateResult = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->update($updateData);

            if ($updateResult === false) {
                $error = $db->error();
                throw new \Exception("Database update failed: {$error['message']}");
            }

            $db->transCommit();

            $this->setSuccess('Owner shares updated successfully!');
            // FIXED: Redirect to property details instead of edit
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            log_message('error', 'Update owner error: ' . $e->getMessage());
            $this->setError('Failed to update owner shares: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove Owner from Property - FIXED error handling
     */
    public function removeOwner($propertyId, $ownerId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

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

            if (($owner['is_primary_owner'] ?? 0) == 1) {
                throw new \Exception('Cannot remove the primary owner');
            }

            if (($owner['user_id'] ?? 0) == $landlordId) {
                throw new \Exception('You cannot remove yourself from the property');
            }

            // Remove the owner
            $deleteResult = $db->table('property_shareholders')
                ->where('id', $ownerId)
                ->delete();

            if ($deleteResult) {
                $this->setSuccess('Shareholder removed successfully!');
            } else {
                $this->setError('Failed to remove shareholder');
            }

            // FIXED: Redirect to property details
            return redirect()->to('/landlord/properties/view/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Remove owner error: ' . $e->getMessage());
            $this->setError('Failed to remove shareholder: ' . $e->getMessage());
            return redirect()->to('/landlord/properties/view/' . $propertyId);
        }
    }

    /**
     * Remove Unit from Property - COMPLETELY FIXED VERSION
     */
    public function removeUnit($propertyId, $unitId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

        log_message('debug', "Remove Unit - PropertyID: {$propertyId}, UnitID: {$unitId}, LandlordID: {$landlordId}");

        // Verify ownership
        if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
            $this->setError('Property not found or you do not have permission to modify it.');
            return redirect()->to('/landlord/properties');
        }

        try {
            $db = \Config\Database::connect();

            // FIXED: Verify unit belongs to this property using 'id' column (not 'unit_id')
            $unit = $db->table('property_units')
                ->where(['id' => $unitId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            log_message('debug', "Unit query result: " . json_encode($unit));

            if (!$unit) {
                throw new \Exception("Unit not found (ID: {$unitId} for Property: {$propertyId})");
            }

            // Check if unit has active leases or tenants
            if ($db->tableExists('leases')) {
                $activeLease = $db->table('leases')
                    ->where(['id' => $unitId, 'property_id' => $propertyId])
                    ->get()
                    ->getRowArray();

                if ($activeLease) {
                    $this->setError('Cannot remove unit with active lease. Please terminate the lease first.');
                    return redirect()->back();
                }
            }

            // Optional: Check if unit is occupied (based on unit status)
            if (isset($unit['status']) && $unit['status'] === 'occupied') {
                $this->setError('Cannot remove an occupied unit. Please mark it as vacant first.');
                return redirect()->back();
            }

            // FIXED: Delete using 'id' column (not 'unit_id')
            $deleteResult = $db->table('property_units')->where('id', $unitId)->delete();

            if ($deleteResult) {
                log_message('debug', "Unit deleted successfully");
                $this->setSuccess('Unit removed successfully!');
            } else {
                $error = $db->error();
                log_message('error', "Failed to delete unit - " . json_encode($error));
                $this->setError('Failed to remove unit from database');
            }

            // Redirect back to edit page
            return redirect()->to('/landlord/properties/edit/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Remove unit error: ' . $e->getMessage());
            log_message('error', 'Error file: ' . $e->getFile() . ' on line ' . $e->getLine());
            $this->setError('Failed to remove unit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update Unit - ALSO FIXED for consistency
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
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        try {
            $db = \Config\Database::connect();

            // FIXED: Verify unit belongs to this property using 'id' column
            $unit = $db->table('property_units')
                ->where(['id' => $unitId, 'property_id' => $propertyId])
                ->get()
                ->getRowArray();

            if (!$unit) {
                throw new \Exception('Unit not found');
            }

            // Check if new unit name conflicts with existing units (except current unit)
            $existingUnit = $db->table('property_units')
                ->where([
                    'property_id' => $propertyId,
                    'unit_name' => trim($this->request->getPost('unit_name')),
                    'id !=' => $unitId
                ])
                ->get()
                ->getRowArray();

            if ($existingUnit) {
                $this->setError('A unit with this name already exists for this property.');
                return redirect()->back()->withInput();
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

            return redirect()->to('/landlord/properties/edit/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Update unit error: ' . $e->getMessage());
            $this->setError('Failed to update unit: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Enhanced Database Check - shows exact table structures
     */
    public function checkDatabase()
    {
        try {
            $db = \Config\Database::connect();

            echo "<h2>🔍 Enhanced Database Structure Check</h2>";
            echo "<style>
            body { font-family: monospace; margin: 20px; }
            .table-info { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .warning { color: #ffc107; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>";

            // Test database connection
            $result = $db->query("SELECT 1")->getResult();
            echo "<div class='success'>✅ Database connection successful!</div><br>";

            // Check specific tables that are causing issues
            $criticalTables = [
                'property_units' => [
                    'id',
                    'property_id',
                    'unit_name',
                    'status',
                    'rent_amount',
                    'description',
                    'created_at'
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
                'properties' => [
                    'id',
                    'property_name',
                    'property_value',
                    'address',
                    'total_shares',
                    'share_value',
                    'management_company',
                    'management_percentage',
                    'created_at'
                ]
            ];

            foreach ($criticalTables as $tableName => $expectedColumns) {
                echo "<div class='table-info'>";
                echo "<h3>📋 Table: {$tableName}</h3>";

                if ($db->tableExists($tableName)) {
                    echo "<div class='success'>✅ Table exists</div>";

                    // Get actual columns
                    $fields = $db->getFieldNames($tableName);
                    echo "<strong>Actual columns:</strong> " . implode(', ', $fields) . "<br>";

                    // Check for missing columns
                    $missingColumns = array_diff($expectedColumns, $fields);
                    $extraColumns = array_diff($fields, $expectedColumns);

                    if (empty($missingColumns)) {
                        echo "<div class='success'>✅ All required columns present</div>";
                    } else {
                        echo "<div class='error'>❌ Missing columns: " . implode(', ', $missingColumns) . "</div>";
                    }

                    if (!empty($extraColumns)) {
                        echo "<div class='warning'>ℹ️ Extra columns: " . implode(', ', $extraColumns) . "</div>";
                    }

                    // Show detailed column info
                    $query = $db->query("DESCRIBE {$tableName}");
                    $columnDetails = $query->getResultArray();

                    echo "<table>";
                    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                    foreach ($columnDetails as $col) {
                        echo "<tr>";
                        echo "<td>" . $col['Field'] . "</td>";
                        echo "<td>" . $col['Type'] . "</td>";
                        echo "<td>" . $col['Null'] . "</td>";
                        echo "<td>" . $col['Key'] . "</td>";
                        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";

                    // Show row count
                    $count = $db->table($tableName)->countAllResults();
                    echo "<strong>Total rows:</strong> {$count}<br>";

                } else {
                    echo "<div class='error'>❌ Table '{$tableName}' does not exist</div>";
                    echo "<div class='warning'>⚠️ You need to create this table or run migrations</div>";
                }
                echo "</div>";
            }

            // Test specific problematic queries
            echo "<div class='table-info'>";
            echo "<h3>🧪 Testing Problematic Queries</h3>";

            // Test 1: property_units query
            try {
                $testUnit = $db->table('property_units')
                    ->where(['id' => 1])  // Using 'id' not 'unit_id'
                    ->get()
                    ->getRowArray();
                echo "<div class='success'>✅ property_units query with 'id' works</div>";
            } catch (\Exception $e) {
                echo "<div class='error'>❌ property_units query failed: " . $e->getMessage() . "</div>";
            }

            // Test 2: property_shareholders query
            try {
                $testOwner = $db->table('property_shareholders')
                    ->where(['id' => 1])
                    ->get()
                    ->getRowArray();
                echo "<div class='success'>✅ property_shareholders query works</div>";
            } catch (\Exception $e) {
                echo "<div class='error'>❌ property_shareholders query failed: " . $e->getMessage() . "</div>";
            }

            echo "</div>";

            // Show recent log entries if possible
            echo "<div class='table-info'>";
            echo "<h3>📝 Recent Error Logs</h3>";
            $logPath = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.php';
            if (file_exists($logPath)) {
                $logContent = file_get_contents($logPath);
                $lines = explode("\n", $logContent);
                $recentLines = array_slice($lines, -20); // Last 20 lines

                echo "<div style='background: #000; color: #fff; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
                foreach ($recentLines as $line) {
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'WARNING') !== false) {
                        echo htmlspecialchars($line) . "<br>";
                    }
                }
                echo "</div>";
            } else {
                echo "<div class='warning'>No log file found for today</div>";
            }
            echo "</div>";

            echo "<br><h3>✅ Database check completed!</h3>";
            echo "<p><a href='" . site_url('landlord/dashboard') . "'>← Return to Dashboard</a></p>";

        } catch (\Exception $e) {
            echo "<div class='error'>❌ Database check failed: " . $e->getMessage() . "</div>";
            echo "<br><strong>Stack trace:</strong><br><pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    /**
     * Add Unit to Property - ALSO FIXED for consistency
     */
    public function addUnit($propertyId)
    {
        $redirect = $this->requireLandlord();
        if ($redirect) {
            return $redirect;
        }

        $landlordId = $this->getCurrentUserId();

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

            // Check if unit name already exists for this property
            $existingUnit = $db->table('property_units')
                ->where([
                    'property_id' => $propertyId,
                    'unit_name' => trim($this->request->getPost('unit_name'))
                ])
                ->get()
                ->getRowArray();

            if ($existingUnit) {
                $this->setError('A unit with this name already exists for this property.');
                return redirect()->back()->withInput();
            }

            $unitData = [
                'property_id' => $propertyId,
                'unit_name' => trim($this->request->getPost('unit_name')),
                'status' => $this->request->getPost('unit_status') ?? 'vacant',
                'rent_amount' => $this->request->getPost('rent_amount') ?? 0,
                'description' => trim($this->request->getPost('unit_description') ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->table('property_units')->insert($unitData)) {
                $this->setSuccess('Unit added successfully!');
            } else {
                $this->setError('Failed to add unit');
            }

            return redirect()->to('/landlord/properties/edit/' . $propertyId);

        } catch (\Exception $e) {
            log_message('error', 'Add unit error: ' . $e->getMessage());
            $this->setError('Failed to add unit: ' . $e->getMessage());
            return redirect()->back()->withInput();
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
     * Generate Ownership PDF Report - FIXED VERSION
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
            $reportName = $this->request->getPost('report_name') ?? 'Property Ownership Report';
            $reportNotes = $this->request->getPost('report_notes') ?? '';

            // Get report options
            $includeOwnerDetails = $this->request->getPost('include_owner_details') ? true : false;
            $includePercentages = $this->request->getPost('include_percentages') ? true : false;
            $includeManagement = $this->request->getPost('include_management') ? true : false;
            $includeConditions = $this->request->getPost('include_conditions') ? true : false;

            // Get property and shareholders data
            if ($propertyId && $propertyId !== 'all') {
                // Single property report
                $property = $this->propertyModel->find($propertyId);
                if (!$property) {
                    throw new \Exception('Property not found');
                }

                // Verify ownership
                if (!$this->verifyPropertyOwnership($propertyId, $landlordId)) {
                    throw new \Exception('You do not have permission to generate reports for this property');
                }

                $properties = [$property];
            } else {
                // All properties report
                $properties = $db->table('properties p')
                    ->join('property_shareholders ps', 'ps.property_id = p.id')
                    ->where('ps.user_id', $landlordId)
                    ->select('p.*')
                    ->groupBy('p.id')
                    ->orderBy('p.property_name')
                    ->get()
                    ->getResultArray();
            }

            if (empty($properties)) {
                $this->setError('No properties found for report generation');
                return redirect()->back();
            }

            // FIXED: Generate comprehensive report data with all needed information
            $reportData = [];
            $totalPortfolioValue = 0;
            $totalSharesOwned = 0;
            $totalPropertiesCount = count($properties);

            foreach ($properties as $property) {
                $shareholders = $db->table('property_shareholders ps')
                    ->select('ps.*, u.first_name, u.last_name')
                    ->join('users u', 'u.id = ps.user_id', 'left')
                    ->where('ps.property_id', $property['id'])
                    ->orderBy('ps.ownership_percentage', 'DESC')
                    ->get()
                    ->getResultArray();

                // Get property units
                $units = $db->table('property_units')
                    ->select('unit_name')
                    ->where('property_id', $property['id'])
                    ->orderBy('unit_name', 'ASC')
                    ->get()
                    ->getResultArray();

                // Calculate property statistics
                $totalAllocatedShares = array_sum(array_column($shareholders, 'shares'));
                $availableShares = $property['total_shares'] - $totalAllocatedShares;
                $propertyFullyAllocated = ($availableShares <= 0);

                // Calculate user's investment in this property
                $userShareholder = array_filter($shareholders, function ($sh) use ($landlordId) {
                    return $sh['user_id'] == $landlordId;
                });
                $userSharesInProperty = !empty($userShareholder) ? array_values($userShareholder)[0]['shares'] : 0;
                $userInvestmentInProperty = $userSharesInProperty * $property['share_value'];

                $totalPortfolioValue += $userInvestmentInProperty;
                $totalSharesOwned += $userSharesInProperty;

                // FIXED: Create single entry with ALL data including units AND statistics
                $reportData[] = [
                    'property' => $property,
                    'shareholders' => $shareholders,
                    'units' => $units,  // Include units data
                    'statistics' => [   // Include statistics data
                        'total_allocated_shares' => $totalAllocatedShares,
                        'available_shares' => $availableShares,
                        'fully_allocated' => $propertyFullyAllocated,
                        'user_shares' => $userSharesInProperty,
                        'user_investment' => $userInvestmentInProperty,
                        'allocation_percentage' => ($totalAllocatedShares / $property['total_shares']) * 100
                    ]
                ];
            }

            // Portfolio summary
            $portfolioSummary = [
                'total_properties' => $totalPropertiesCount,
                'total_investment_value' => $totalPortfolioValue,
                'total_shares_owned' => $totalSharesOwned,
                'report_generated_by' => $this->getCurrentUserName(),
                'report_generated_at' => date('Y-m-d H:i:s')
            ];

            // SIMPLE FIX: Use the simpler PDF generation method instead of the complex one
            $pdfContent = $this->generateOwnershipPdfContent($reportData);

            // Log report generation
            $propertyName = ($propertyId && $propertyId !== 'all') ? $properties[0]['property_name'] : 'All Properties';
            $this->logReportGeneration($landlordId, 'ownership', $reportName . ' - ' . date('Y-m-d H:i'), $propertyName, $propertyId);

            // Generate filename safely
            $filename = $this->createSafeFilename($reportName . '_' . date('Y-m-d_H-i') . '.pdf');

            session()->remove('success');
            session()->remove('error');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0')
                ->setBody($pdfContent);

        } catch (\Exception $e) {
            log_message('error', 'Generate ownership PDF error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->setError('Failed to generate report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Create a safe filename for downloads
     * 
     * @param string $filename
     * @return string
     */


    /**
     * FIXED: Get current user's full name for reports
     */
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

    /**
     * FIXED: Create a safe filename for downloads
     */
    private function createSafeFilename($filename)
    {
        // Remove or replace invalid characters for filenames
        $filename = preg_replace('/[^\w\-_\. ]/', '_', $filename);

        // Remove multiple spaces and replace with single underscore
        $filename = preg_replace('/\s+/', '_', $filename);

        // Remove multiple underscores and replace with single underscore
        $filename = preg_replace('/_+/', '_', $filename);

        // Remove leading/trailing underscores and dots
        $filename = trim($filename, '_.');

        // Ensure the filename is not empty
        if (empty($filename)) {
            $filename = 'ownership_report_' . date('Y-m-d_H-i') . '.pdf';
        }

        // Limit filename length (without extension)
        $pathinfo = pathinfo($filename);
        $name = $pathinfo['filename'] ?? $filename;
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';

        if (strlen($name) > 200) {
            $name = substr($name, 0, 200);
        }

        return $name . $extension;
    }

    /**
     * FIXED: Log report generation for audit trail
     */

    private function logReportGeneration($userId, $reportType, $reportName, $propertyName, $propertyId = null)
    {
        try {
            $db = \Config\Database::connect();

            // Check if report_logs table exists first
            if (!$db->tableExists('report_logs')) {
                log_message('warning', 'Report logs table does not exist. Skipping report logging.');
                return;
            }

            $logData = [
                'user_id' => $userId,
                'report_type' => $reportType,
                'report_name' => $reportName,
                'property_name' => $propertyName,
                'property_id' => $propertyId,
                'generated_at' => date('Y-m-d H:i:s'),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('report_logs')->insert($logData);

        } catch (\Exception $e) {
            log_message('warning', 'Failed to log report generation: ' . $e->getMessage());
            // Don't fail the report generation if logging fails
        }
    }
    /**
     * FIXED - Verify Property Ownership method
     */
    private function verifyPropertyOwnership($propertyId, $userId)
    {
        try {
            $db = \Config\Database::connect();

            // Check if property_shareholders table exists
            if (!$db->tableExists('property_shareholders')) {
                log_message('warning', 'property_shareholders table does not exist, using basic property check');

                // Fallback: check if user created the property (adjust field name as needed)
                $property = $this->propertyModel->find($propertyId);
                if ($property && isset($property['created_by'])) {
                    return $property['created_by'] == $userId;
                }

                // If no created_by field, allow access (adjust this logic as needed)
                return true;
            }

            // Check if user is a shareholder of this specific property
            $shareholder = $db->table('property_shareholders')
                ->where([
                    'property_id' => $propertyId,  // CRUCIAL: Check specific property
                    'user_id' => $userId,
                    'status' => 'active'
                ])
                ->get()
                ->getRowArray();

            $hasAccess = !empty($shareholder);
            log_message('debug', "Property ownership check for PropertyID {$propertyId}, UserID {$userId}: " . ($hasAccess ? 'GRANTED' : 'DENIED'));

            return $hasAccess;

        } catch (\Exception $e) {
            log_message('error', 'Property ownership verification failed: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Generate Enhanced PDF Content for Ownership Report
     */
    private function generateEnhancedOwnershipPdfContent($reportData, $portfolioSummary, $reportName, $reportNotes, $options)
    {
        $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: "DejaVu Sans", Arial, sans-serif; 
                margin: 15px;
                color: #333;
                line-height: 1.4;
                font-size: 11px;
            }
            
            .header { 
                text-align: center; 
                margin-bottom: 25px; 
                padding: 15px 0;
                border-bottom: 3px solid #4e73df;
                background: #f8f9fc;
                margin: -15px -15px 25px -15px;
                padding: 20px 15px;
            }
            
            .report-title {
                font-size: 22px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 8px;
            }
            
            .report-subtitle {
                font-size: 14px;
                color: #6c757d;
                margin-bottom: 12px;
            }
            
            .report-meta {
                font-size: 10px;
                color: #6c757d;
                background: white;
                padding: 8px;
                border-radius: 4px;
                margin-top: 10px;
            }
            
            .portfolio-summary {
                background: #f8f9fc;
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 25px;
                border-left: 4px solid #4e73df;
                page-break-inside: avoid;
            }
            
            .summary-title {
                font-size: 14px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 10px;
            }
            
            .property-section { 
                margin-bottom: 35px; 
                page-break-inside: avoid;
                border: 1px solid #e3e6f0;
                border-radius: 6px;
                overflow: hidden;
            }
            
            .property-header {
                background: linear-gradient(135deg, #4e73df 0%, #3c61d1 100%);
                color: white;
                padding: 12px 15px;
                margin: 0;
            }
            
            .property-title { 
                font-size: 16px; 
                font-weight: bold; 
                margin: 0;
            }
            
            .property-address {
                font-size: 11px;
                opacity: 0.9;
                margin-top: 3px;
            }
            
            .property-content {
                padding: 15px;
            }
            
            .section-title {
                font-size: 13px;
                font-weight: bold;
                color: #2c3e50;
                margin: 15px 0 8px 0;
                padding-bottom: 3px;
                border-bottom: 1px solid #e3e6f0;
            }
            
            .section-title:first-child {
                margin-top: 0;
            }
            
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 12px;
                font-size: 10px;
            }
            
            .info-table th, .info-table td { 
                border: 1px solid #e3e6f0; 
                padding: 6px 8px; 
                text-align: left; 
                vertical-align: top;
            }
            
            .info-table th { 
                background: #f8f9fc; 
                font-weight: bold;
                color: #5a5c69;
                width: 25%;
            }
            
            .shareholders-table {
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 12px;
                font-size: 9px;
            }
            
            .shareholders-table th, .shareholders-table td { 
                border: 1px solid #e3e6f0; 
                padding: 5px 6px; 
                text-align: left; 
                vertical-align: top;
            }
            
            .shareholders-table th { 
                background: #f8f9fc; 
                font-weight: bold;
                color: #5a5c69;
                font-size: 8px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            
            .status-badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 8px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.2px;
            }
            
            .status-active { background: #d4edda; color: #155724; }
            .status-pending { background: #fff3cd; color: #856404; }
            .status-primary { background: #cce7ff; color: #004085; }
            
            .management-info {
                background: #e8f5e8;
                padding: 10px;
                border-radius: 4px;
                border-left: 3px solid #28a745;
                margin: 10px 0;
                font-size: 10px;
            }
            
            .conditions-section {
                background: #fff3cd;
                padding: 12px;
                border-radius: 4px;
                border-left: 3px solid #ffc107;
                margin: 15px 0;
                font-size: 10px;
            }
            
            .conditions-title {
                font-weight: bold;
                color: #856404;
                margin-bottom: 8px;
                font-size: 11px;
            }
            
            .condition-item {
                margin-bottom: 4px;
                padding-left: 12px;
                position: relative;
                line-height: 1.3;
            }
            
            .condition-item:before {
                content: "•";
                position: absolute;
                left: 0;
                color: #856404;
                font-weight: bold;
            }
            
            .notes-section {
                background: #f8f9fc;
                padding: 12px;
                border-radius: 4px;
                border-left: 3px solid #6f42c1;
                margin: 15px 0;
                font-size: 10px;
            }
            
            .notes-title {
                font-weight: bold;
                color: #6f42c1;
                margin-bottom: 8px;
                font-size: 11px;
            }
            
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #e3e6f0;
                font-size: 9px;
                color: #6c757d;
                text-align: center;
                page-break-inside: avoid;
            }
            
            .disclaimer {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                margin-top: 10px;
                font-size: 9px;
                color: #6c757d;
                line-height: 1.3;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            /* Summary tables */
            .summary-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
                font-size: 10px;
            }
            
            .summary-table th, .summary-table td {
                border: 1px solid #e3e6f0;
                padding: 6px 8px;
                text-align: left;
            }
            
            .summary-table th {
                background: #f8f9fc;
                font-weight: bold;
                color: #5a5c69;
            }
        </style>
    </head>
    <body>';

        // Header
        $html .= '<div class="header">';
        $html .= '<div class="report-title">' . htmlspecialchars($reportName) . '</div>';
        $html .= '<div class="report-subtitle">Comprehensive Property Ownership Analysis</div>';
        $html .= '<div class="report-meta">';
        $html .= '<strong>Generated:</strong> ' . date('l, F j, Y \a\t g:i A') . ' | ';
        $html .= '<strong>Report ID:</strong> OWN-' . date('Ymd-His') . ' | ';
        $html .= '<strong>Generated by:</strong> ' . htmlspecialchars($portfolioSummary['report_generated_by']);
        $html .= '</div>';
        $html .= '</div>';

        // Portfolio Summary (only show for multiple properties)
        if (count($reportData) > 1) {
            $html .= '<div class="portfolio-summary">';
            $html .= '<div class="summary-title">📊 Portfolio Overview</div>';
            $html .= '<table class="summary-table">';
            $html .= '<tr><th width="40%">Total Properties in Portfolio</th><td><strong>' . number_format($portfolioSummary['total_properties']) . ' properties</strong></td></tr>';
            $html .= '<tr><th>Total Investment Value</th><td><strong>SAR ' . number_format($portfolioSummary['total_investment_value'], 2) . '</strong></td></tr>';
            $html .= '<tr><th>Total Shares Owned</th><td><strong>' . number_format($portfolioSummary['total_shares_owned']) . ' shares</strong></td></tr>';

            $avgInvestmentPerProperty = $portfolioSummary['total_properties'] > 0 ?
                $portfolioSummary['total_investment_value'] / $portfolioSummary['total_properties'] : 0;

            $html .= '<tr><th>Average Investment per Property</th><td>SAR ' . number_format($avgInvestmentPerProperty, 2) . '</td></tr>';
            $html .= '</table>';
            $html .= '</div>';
        }

        // Property Details - Each property with complete information
        foreach ($reportData as $index => $data) {
            $property = $data['property'];
            $shareholders = $data['shareholders'];
            $stats = $data['statistics'];

            // Add page break for properties after the first one (except for single property reports)
            if ($index > 0 && count($reportData) > 1) {
                $html .= '<div class="page-break"></div>';
            }

            $html .= '<div class="property-section">';

            // Property Header
            $html .= '<div class="property-header">';
            $html .= '<div class="property-title">🏢 ' . htmlspecialchars($property['property_name']) . '</div>';
            $html .= '<div class="property-address">📍 ' . htmlspecialchars($property['address']) . '</div>';
            $html .= '</div>';

            $html .= '<div class="property-content">';

            // Property Information Section
            $html .= '<div class="section-title">Property Information</div>';
            $html .= '<table class="info-table">';
            $html .= '<tr><th>Property Value</th><td><strong>SAR ' . number_format($property['property_value'], 2) . '</strong></td></tr>';
            $html .= '<tr><th>Address</th><td>' . htmlspecialchars($property['address']) . '</td></tr>';
            $html .= '<tr><th>Total Shares</th><td>' . number_format($property['total_shares']) . ' shares</td></tr>';
            $html .= '<tr><th>Share Value</th><td>SAR ' . number_format($property['share_value'], 2) . ' per share</td></tr>';
            $html .= '<tr><th>Contribution Duration</th><td>' . $property['contribution_duration'] . ' months</td></tr>';
            $html .= '</table>';

            // Management Information Section
            if ($options['include_management']) {
                $html .= '<div class="section-title">Management Information</div>';
                $html .= '<div class="management-info">';
                $html .= '<strong>Company:</strong> ' . htmlspecialchars($property['management_company']) . '<br>';
                $html .= '<strong>Fee:</strong> ' . $property['management_percentage'] . '% of rental income';
                $html .= '</div>';
            }

            // Shareholders Information Section
            if ($options['include_owner_details'] && !empty($shareholders)) {
                $html .= '<div class="section-title">Shareholders Information</div>';
                $html .= '<table class="shareholders-table">';
                $html .= '<thead><tr>';
                $html .= '<th width="25%">Name</th>';
                $html .= '<th width="25%">Email</th>';
                $html .= '<th width="12%">Shares</th>';
                if ($options['include_percentages']) {
                    $html .= '<th width="13%">Ownership %</th>';
                    $html .= '<th width="20%">Investment Value</th>';
                }
                $html .= '<th width="5%">Status</th>';
                $html .= '</tr></thead>';
                $html .= '<tbody>';

                foreach ($shareholders as $shareholder) {
                    $name = ($shareholder['first_name'] && $shareholder['last_name'])
                        ? $shareholder['first_name'] . ' ' . $shareholder['last_name']
                        : htmlspecialchars($shareholder['owner_name']);

                    $investmentValue = $shareholder['shares'] * $property['share_value'];

                    $html .= '<tr>';
                    $html .= '<td><strong>' . $name . '</strong>';

                    // Add badges for special roles
                    if ($shareholder['is_primary_owner']) {
                        $html .= '<br><span class="status-badge status-primary">Primary</span>';
                    }

                    $html .= '</td>';
                    $html .= '<td>' . htmlspecialchars($shareholder['owner_email']) . '</td>';
                    $html .= '<td class="text-center"><strong>' . number_format($shareholder['shares']) . '</strong></td>';

                    if ($options['include_percentages']) {
                        $html .= '<td class="text-center"><strong>' . number_format($shareholder['ownership_percentage'], 2) . '%</strong></td>';
                        $html .= '<td class="text-right"><strong>SAR ' . number_format($investmentValue, 2) . '</strong></td>';
                    }

                    $statusClass = $shareholder['status'] === 'active' ? 'status-active' : 'status-pending';
                    $html .= '<td class="text-center"><span class="status-badge ' . $statusClass . '">' . ucfirst($shareholder['status']) . '</span></td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody>';

                // Add totals row
                if ($options['include_percentages']) {
                    $totalShares = array_sum(array_column($shareholders, 'shares'));
                    $totalInvestment = $totalShares * $property['share_value'];
                    $totalPercentage = array_sum(array_column($shareholders, 'ownership_percentage'));

                    $html .= '<tfoot>';
                    $html .= '<tr style="background: #f8f9fc; font-weight: bold;">';
                    $html .= '<td colspan="2" class="text-right"><strong>TOTALS:</strong></td>';
                    $html .= '<td class="text-center"><strong>' . number_format($totalShares) . '</strong></td>';
                    $html .= '<td class="text-center"><strong>' . number_format($totalPercentage, 2) . '%</strong></td>';
                    $html .= '<td class="text-right"><strong>SAR ' . number_format($totalInvestment, 2) . '</strong></td>';
                    $html .= '<td></td>';
                    $html .= '</tr>';
                    $html .= '</tfoot>';
                }

                $html .= '</table>';

                // Share allocation summary
                $html .= '<table class="info-table" style="margin-top: 10px;">';
                $html .= '<tr><th>Allocated Shares</th><td>' . number_format($stats['total_allocated_shares']) . ' / ' . number_format($property['total_shares']) . ' (' . number_format($stats['allocation_percentage'], 1) . '%)</td></tr>';
                $html .= '<tr><th>Available Shares</th><td>';
                if ($stats['available_shares'] > 0) {
                    $html .= '<span style="color: #28a745;">' . number_format($stats['available_shares']) . ' shares available</span>';
                } else {
                    $html .= '<span style="color: #dc3545;">Fully allocated</span>';
                }
                $html .= '</td></tr>';
                $html .= '<tr><th>Available Investment</th><td>SAR ' . number_format($stats['available_shares'] * $property['share_value'], 2) . '</td></tr>';
                $html .= '</table>';
            }

            // Shareholding Agreement Conditions Section
            if ($options['include_conditions']) {
                $html .= '<div class="section-title">Shareholding Agreement Conditions</div>';
                $html .= '<div class="conditions-section">';
                $html .= '<div class="condition-item">All shareholders agree to contribute their proportional share of expenses</div>';
                $html .= '<div class="condition-item">Rental income distributed according to ownership percentages</div>';
                $html .= '<div class="condition-item">Major decisions require majority shareholder approval</div>';
                $html .= '<div class="condition-item">Share transfers must be approved by existing shareholders</div>';
                $html .= '</div>';
            }

            $html .= '</div>'; // property-content
            $html .= '</div>'; // property-section
        }

        // Additional Notes Section (shown once at the end for all properties)
        if (!empty($reportNotes)) {
            $html .= '<div class="notes-section">';
            $html .= '<div class="notes-title">📝 Additional Notes:</div>';
            $html .= nl2br(htmlspecialchars($reportNotes));
            $html .= '</div>';
        }

        // Footer
        $html .= '<div class="footer">';
        $html .= '<div class="disclaimer">';
        $html .= '<strong>DISCLAIMER:</strong> This report is generated for informational purposes only. ';
        $html .= 'All financial figures and ownership percentages are based on data available at the time of generation. ';
        $html .= 'For legal or financial decisions, please consult with qualified professionals. ';
        $html .= 'This document is confidential and intended only for the named recipient(s).';
        $html .= '</div>';
        $html .= '<p>Generated by Property Management System | ' . date('Y') . ' | Report ID: OWN-' . date('Ymd-His') . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        // Generate PDF using DomPDF
        return $this->generatePdfFromHtml($html);
    }

    /**
     * FIXED: Generate PDF from HTML using DomPDF
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
            $options->set('isPhpEnabled', false); // Security: disable PHP in PDF
            $options->set('defaultPaperSize', 'A4');
            $options->set('defaultPaperOrientation', 'portrait');
            $options->set('chroot', realpath(ROOTPATH)); // Security: restrict file access

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
    }

    /**
     * ALTERNATIVE: Simple HTML to PDF fallback if DomPDF not available
     */
    private function generateSimplePdfContent($reportData, $portfolioSummary, $reportName, $reportNotes, $options)
    {
        // Simple HTML content that can be displayed as PDF alternative
        $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . htmlspecialchars($reportName) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
            .property-section { margin-bottom: 30px; page-break-inside: avoid; }
            .property-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
        </style>
    </head>
    <body>';

        $html .= '<div class="header">';
        $html .= '<h1>' . htmlspecialchars($reportName) . '</h1>';
        $html .= '<p>Generated on: ' . date('F j, Y, g:i a') . '</p>';
        $html .= '<p>Generated by: ' . htmlspecialchars($portfolioSummary['report_generated_by']) . '</p>';
        $html .= '</div>';

        // Portfolio Summary (if multiple properties)
        if (count($reportData) > 1) {
            $html .= '<div class="portfolio-summary">';
            $html .= '<h2>Portfolio Overview</h2>';
            $html .= '<table>';
            $html .= '<tr><th>Total Properties</th><td>' . $portfolioSummary['total_properties'] . '</td></tr>';
            $html .= '<tr><th>Total Investment Value</th><td>SAR ' . number_format($portfolioSummary['total_investment_value'], 2) . '</td></tr>';
            $html .= '<tr><th>Total Shares Owned</th><td>' . number_format($portfolioSummary['total_shares_owned']) . '</td></tr>';
            $html .= '</table>';
            $html .= '</div>';
        }

        // Property Details
        foreach ($reportData as $data) {
            $property = $data['property'];
            $shareholders = $data['shareholders'];
            $stats = $data['statistics'] ?? [];

            $html .= '<div class="property-section">';
            $html .= '<div class="property-title">' . htmlspecialchars($property['property_name']) . '</div>';

            // Property Information
            $html .= '<h3>Property Information</h3>';
            $html .= '<table>';
            $html .= '<tr><th>Property Value</th><td>SAR ' . number_format($property['property_value'], 2) . '</td></tr>';
            $html .= '<tr><th>Address</th><td>' . htmlspecialchars($property['address']) . '</td></tr>';
            $html .= '<tr><th>Total Shares</th><td>' . number_format($property['total_shares']) . '</td></tr>';
            $html .= '<tr><th>Share Value</th><td>SAR ' . number_format($property['share_value'], 2) . '</td></tr>';
            $html .= '</table>';

            // Management Information (if included)
            if ($options['include_management']) {
                $html .= '<h3>Management Information</h3>';
                $html .= '<table>';
                $html .= '<tr><th>Management Company</th><td>' . htmlspecialchars($property['management_company'] ?? 'Self-Managed') . '</td></tr>';
                $html .= '<tr><th>Management Fee</th><td>' . ($property['management_percentage'] ?? 0) . '%</td></tr>';
                $html .= '</table>';
            }

            // Shareholders Information (if included)
            if ($options['include_owner_details'] && !empty($shareholders)) {
                $html .= '<h3>Shareholders Information</h3>';
                $html .= '<table>';
                $html .= '<tr><th>Name</th><th>Email</th><th>Shares</th>';
                if ($options['include_percentages']) {
                    $html .= '<th>Ownership %</th><th>Investment Value</th>';
                }
                $html .= '</tr>';

                foreach ($shareholders as $shareholder) {
                    $name = (!empty($shareholder['first_name']) && !empty($shareholder['last_name']))
                        ? $shareholder['first_name'] . ' ' . $shareholder['last_name']
                        : $shareholder['owner_name'];

                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($name) . '</td>';
                    $html .= '<td>' . htmlspecialchars($shareholder['owner_email']) . '</td>';
                    $html .= '<td class="text-center">' . number_format($shareholder['shares']) . '</td>';

                    if ($options['include_percentages']) {
                        $html .= '<td class="text-center">' . number_format($shareholder['ownership_percentage'], 2) . '%</td>';
                        $html .= '<td class="text-right">SAR ' . number_format($shareholder['shares'] * $property['share_value'], 2) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';

                // Share allocation summary
                if (!empty($stats)) {
                    $html .= '<table>';
                    $html .= '<tr><th>Allocated Shares</th><td>' . number_format($stats['total_allocated_shares']) . ' / ' . number_format($property['total_shares']) . '</td></tr>';
                    $html .= '<tr><th>Available Shares</th><td>' . number_format($stats['available_shares']) . '</td></tr>';
                    $html .= '</table>';
                }
            }

            $html .= '</div>';
        }

        // Additional Notes
        if (!empty($reportNotes)) {
            $html .= '<div class="notes">';
            $html .= '<h2>Additional Notes</h2>';
            $html .= '<p>' . nl2br(htmlspecialchars($reportNotes)) . '</p>';
            $html .= '</div>';
        }

        // Shareholders Agreement Conditions (if included)
        if ($options['include_conditions']) {
            $html .= '<div class="conditions">';
            $html .= '<h2>Shareholders Agreement Conditions</h2>';
            $html .= '<ol>';
            $html .= '<li>Shareholders have no involvement in the property\'s operation at all.</li>';
            $html .= '<li>Any financial income from the property will be distributed to shareholders after deducting expenses.</li>';
            $html .= '<li>In case of any violation, the shareholder\'s contribution amount will be refunded.</li>';
            $html .= '<li>Shareholders are not allowed to sell their shares to anyone outside the current shareholders.</li>';
            $html .= '</ol>';
            $html .= '</div>';
        }

        $html .= '</body></html>';

        return $html;
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
     * Generate Simple PDF Content for Ownership Report - FIXED VERSION
     */
    private function generateOwnershipPdfContent($reportData)
    {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>
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
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #4e73df;
        }
        .company-info {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .property-section { 
            margin-bottom: 40px; 
            page-break-inside: avoid; 
        }
        .property-title { 
            background-color: #f8f9fa; 
            padding: 12px 15px; 
            font-size: 18px; 
            font-weight: bold; 
            color: #2c5aa0; 
            border-left: 4px solid #2c5aa0; 
            margin-bottom: 20px; 
        }
        .unit-info {
            background-color: #e3f2fd;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
            color: #1565c0;
        }
        h3 { 
            color: #2c5aa0; 
            border-bottom: 2px solid #e9ecef; 
            padding-bottom: 5px; 
            margin: 25px 0 15px 0; 
            font-size: 16px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        th { 
            background-color: #2c5aa0; 
            color: white; 
            padding: 12px; 
            text-align: left; 
            font-weight: bold; 
        }
        td { 
            padding: 10px 12px; 
            border-bottom: 1px solid #dee2e6; 
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .currency { 
            text-align: right; 
            font-weight: bold; 
            color: #28a745; 
        }
        .percentage { 
            text-align: center; 
            font-weight: bold; 
            color: #007bff; 
        }
        .footer { 
            margin-top: 40px; 
            padding-top: 20px; 
            border-top: 1px solid #dee2e6; 
            font-size: 11px; 
            color: #888; 
            text-align: center; 
        }
        .summary-box {
            background-color: #f0f8ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
        }
    </style>';
        $html .= '</head><body>';

        // Report Header - Fixed spacing
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
            $units = $data['units'] ?? [];
            $statistics = $data['statistics'] ?? [];

            $html .= '<div class="property-section">';
            $html .= '<div class="property-title">' . htmlspecialchars($property['property_name']) . '</div>';

            // Display Units (only unit names)
            if (!empty($units)) {
                $unitNames = array_column($units, 'unit_name');
                $html .= '<div class="unit-info">';
                $html .= 'Units: ' . htmlspecialchars(implode(', ', $unitNames));
                $html .= '</div>';
            }

            // Property Information Section
            $html .= '<h3>Property Information</h3>';
            $html .= '<table>';
            $html .= '<tr><th width="30%">Property Value</th><td class="currency">SAR ' . number_format($property['property_value'], 2) . '</td></tr>';
            $html .= '<tr><th>Address</th><td>' . htmlspecialchars($property['address'] ?? $property['property_address'] ?? 'N/A') . '</td></tr>';

            // Add total units count
            if (!empty($units)) {
                $html .= '<tr><th>Total Units</th><td>' . count($units) . ' units</td></tr>';
            }

            $html .= '<tr><th>Total Shares</th><td>' . number_format($property['total_shares']) . ' shares</td></tr>';
            $html .= '<tr><th>Share Value</th><td class="currency">SAR ' . number_format($property['share_value'], 2) . ' per share</td></tr>';
            $html .= '<tr><th>Contribution Duration</th><td>' . $property['contribution_duration'] . ' months</td></tr>';

            if (!empty($property['management_company'])) {
                $html .= '<tr><th>Management Company</th><td>' . htmlspecialchars($property['management_company']) . '</td></tr>';
            }
            if (!empty($property['management_percentage'])) {
                $html .= '<tr><th>Management Fee</th><td>' . $property['management_percentage'] . '%</td></tr>';
            }
            $html .= '</table>';

            // Property Summary Box
            $totalInvestment = $property['total_shares'] * $property['share_value'];
            $html .= '<div class="summary-box">';
            $html .= '<div class="summary-title">Property Investment Summary</div>';
            $html .= '<div><strong>Total Investment Value:</strong> SAR ' . number_format($totalInvestment, 2) . '</div>';
            $html .= '<div><strong>Number of Shareholders:</strong> ' . count($shareholders) . '</div>';

            // Add statistics if available
            if (!empty($statistics)) {
                $html .= '<div><strong>Available Shares:</strong> ' . number_format($statistics['available_shares'] ?? 0) . '</div>';
                $html .= '<div><strong>Allocation:</strong> ' . number_format($statistics['allocation_percentage'] ?? 0, 1) . '%</div>';
            }
            $html .= '</div>';

            // Shareholders Information Section
            $html .= '<h3>Shareholders Information</h3>';
            $html .= '<table>';
            $html .= '<tr><th>Name</th><th>Email</th><th>Shares</th><th>Ownership %</th><th>Investment Value</th></tr>';

            foreach ($shareholders as $shareholder) {
                $name = '';
                if (!empty($shareholder['first_name']) && !empty($shareholder['last_name'])) {
                    $name = $shareholder['first_name'] . ' ' . $shareholder['last_name'];
                } elseif (!empty($shareholder['owner_name'])) {
                    $name = $shareholder['owner_name'];
                } elseif (!empty($shareholder['firstname']) && !empty($shareholder['lastname'])) {
                    $name = $shareholder['firstname'] . ' ' . $shareholder['lastname'];
                } else {
                    $name = 'Unknown Shareholder';
                }

                $email = $shareholder['owner_email'] ?? $shareholder['email'] ?? 'N/A';
                $shares = $shareholder['shares'] ?? 0;
                $ownershipPercentage = $shareholder['ownership_percentage'] ?? 0;
                $investmentValue = ($shares * $property['share_value']);

                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($name) . '</td>';
                $html .= '<td>' . htmlspecialchars($email) . '</td>';
                $html .= '<td>' . number_format($shares) . '</td>';
                $html .= '<td class="percentage">' . number_format($ownershipPercentage, 2) . '%</td>';
                $html .= '<td class="currency">SAR ' . number_format($investmentValue, 2) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            $html .= '</div>';
        }

        // Footer
        $html .= '<div class="footer">';
        $html .= '<p>This report is confidential and intended solely for the use of the property shareholders.<br>';
        $html .= 'Generated automatically by the Property Management System on ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        // Generate PDF using DomPDF
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
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

   public function payments()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');

        try {
            $db = \Config\Database::connect();

            $filters = [
                'payment_type' => $this->request->getGet('payment_type') ?? 'all',
                'property_id' => $this->request->getGet('property_id') ?? '',
                'date_from' => $this->request->getGet('date_from') ?? '',
                'date_to' => $this->request->getGet('date_to') ?? ''
            ];

            $allPayments = $this->getIncomeExpensePayments($landlordId, $filters);
            $totals = $this->calculateIncomeExpenseTotalsOptimized($landlordId);
            $properties = $this->getLandlordProperties($landlordId);

            $data = [
                'title' => 'Payment Management',
                'payments' => $allPayments,
                'totals' => $totals,
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
                'totals' => ['net_income' => 0, 'total_expenses' => 0, 'monthly_net' => 0],
                'properties' => [],
                'filters' => $filters ?? []
            ]);
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
            $unit = $this->verifyUnitOwnership($this->request->getPost('unit_id'), $landlordId);
            
            if (!$unit) {
                session()->setFlashdata('error', 'Unit not found or access denied.');
                return redirect()->back()->withInput();
            }

            $receiptFile = null;
            $receipt = $this->request->getFile('receipt_file');
            if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
                // Only allow PDF files
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
                'property_id' => $this->request->getPost('property_id'),
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

            if ($db->table('income_expense_payments')->insert($paymentData)) {
                session()->setFlashdata('success', 'Income payment added successfully.');
            } else {
                session()->setFlashdata('error', 'Failed to add income payment.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error storing income payment: ' . $e->getMessage());
            session()->setFlashdata('error', 'Error adding income payment.');
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
            $unit = $this->verifyUnitOwnership($this->request->getPost('unit_id'), $landlordId);
            
            if (!$unit) {
                session()->setFlashdata('error', 'Unit not found or access denied.');
                return redirect()->back()->withInput();
            }

            $receiptFile = null;
            $receipt = $this->request->getFile('receipt_file');
            if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
                // Only allow PDF files
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
                'property_id' => $this->request->getPost('property_id'),
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

            if ($db->table('income_expense_payments')->insert($paymentData)) {
                session()->setFlashdata('success', 'Expense payment added successfully.');
            } else {
                session()->setFlashdata('error', 'Failed to add expense payment.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error storing expense payment: ' . $e->getMessage());
            session()->setFlashdata('error', 'Error adding expense payment.');
        }

        return redirect()->to('landlord/payments');
    }

    public function exportPaymentsExcel()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');
        
        $filters = [
            'payment_type' => $this->request->getGet('payment_type') ?? 'all',
            'property_id' => $this->request->getGet('property_id') ?? '',
            'date_from' => $this->request->getGet('date_from') ?? '',
            'date_to' => $this->request->getGet('date_to') ?? ''
        ];

        try {
            $payments = $this->getIncomeExpensePayments($landlordId, $filters);
            $filename = 'income_expense_payments_' . date('Y-m-d_H-i-s') . '.csv';

            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set proper CSV headers with UTF-8 encoding
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM to fix Arabic character display
            fprintf($output, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($output, [
                'Date',
                'Type', 
                'Property',
                'Unit',
                'Amount (SAR)',
                'Source/Category',
                'Description',
                'Payment Method',
                'Period'
            ]);

            // Data rows with proper UTF-8 encoding
            foreach ($payments as $payment) {
                $type = ucfirst($payment['type']);
                
                fputcsv($output, [
                    date('n/j/Y', strtotime($payment['date'])),
                    $type,
                    mb_convert_encoding($payment['property_name'] ?? 'N/A', 'UTF-8'),
                    mb_convert_encoding($payment['unit_name'] ?? 'N/A', 'UTF-8'),
                    number_format($payment['amount'], 2),
                    mb_convert_encoding($payment['source'] ?? '', 'UTF-8'),
                    mb_convert_encoding($payment['description'] ?? '', 'UTF-8'),
                    ucfirst(str_replace('_', ' ', $payment['method'] ?? 'N/A')),
                    $payment['period'] ?? 'N/A'
                ]);
            }

            fclose($output);
            exit();

        } catch (\Exception $e) {
            log_message('error', 'Export Excel error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to export data. Please try again.');
            return redirect()->back();
        }
    }

    public function exportPaymentsPDF()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return redirect()->to('/login');
        }

        $landlordId = session()->get('user_id');
        
        $filters = [
            'payment_type' => $this->request->getGet('payment_type') ?? 'all',
            'property_id' => $this->request->getGet('property_id') ?? '',
            'date_from' => $this->request->getGet('date_from') ?? '',
            'date_to' => $this->request->getGet('date_to') ?? ''
        ];

        try {
            $userModel = model('UserModel');
            $payments = $this->getIncomeExpensePayments($landlordId, $filters);
            $user = $userModel->find($landlordId);
            $totals = $this->calculateIncomeExpenseTotalsOptimized($landlordId);

            // Generate HTML content for PDF
            $html = $this->generateReportHTML($payments, $user, $filters, $totals);
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set PDF headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="income_expense_report_' . date('Y-m-d_H-i-s') . '.pdf"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            header('Expires: 0');

            // For now, output HTML that can be saved as PDF by browser
            // In production, you'd use a PDF library like TCPDF or DOMPDF
            echo $html;
            exit();

        } catch (\Exception $e) {
            log_message('error', 'Export PDF error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to export PDF.');
            return redirect()->back();
        }
    }

    public function getUnitsByProperty($propertyId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'landlord') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $landlordId = session()->get('user_id');

        try {
            $db = \Config\Database::connect();

            $propertyBuilder = $db->table('property_shareholders ps');
            $propertyBuilder->select('ps.property_id');
            $propertyBuilder->where('ps.property_id', $propertyId);
            $propertyBuilder->where('ps.user_id', $landlordId);
            $propertyBuilder->where('ps.status', 'active');
            
            $hasAccess = $propertyBuilder->get()->getRowArray();
            
            if (!$hasAccess) {
                return $this->response->setJSON(['error' => 'Property not found or access denied']);
            }

            $builder = $db->table('property_units pu');
            $builder->select('pu.id, pu.unit_name, pu.status');
            $builder->where('pu.property_id', $propertyId);
            $builder->orderBy('pu.unit_name', 'ASC');

            $units = $builder->get()->getResultArray();

            foreach ($units as &$unit) {
                if (empty($unit['unit_name'])) {
                    $unit['unit_name'] = 'Unit ' . $unit['id'];
                }
            }

            return $this->response->setJSON($units);

        } catch (\Exception $e) {
            log_message('error', 'Get units by property error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load units']);
        }
    }

    // Helper Methods

    private function verifyUnitOwnership($unitId, $landlordId)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table('property_units pu');
            $builder->select('pu.*, p.property_name, p.management_percentage');
            $builder->join('properties p', 'p.id = pu.property_id');
            
            if ($db->tableExists('property_shareholders')) {
                $builder->join('property_shareholders ps', 'ps.property_id = p.id');
                $builder->where('ps.user_id', $landlordId);
                $builder->where('ps.status', 'active');
            }
            
            $builder->where('pu.id', $unitId);
            return $builder->get()->getRowArray();
            
        } catch (\Exception $e) {
            log_message('error', 'Error verifying unit ownership: ' . $e->getMessage());
            return null;
        }
    }

    private function getLandlordProperties($landlordId)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table('properties p');
            $builder->select('p.id, p.property_name, p.address');
            
            if ($db->tableExists('property_shareholders')) {
                $builder->join('property_shareholders ps', 'ps.property_id = p.id');
                $builder->where('ps.user_id', $landlordId);
                $builder->where('ps.status', 'active');
            }
            
            $builder->orderBy('p.property_name', 'ASC');
            return $builder->get()->getResultArray();
            
        } catch (\Exception $e) {
            log_message('error', 'Error fetching properties: ' . $e->getMessage());
            return [];
        }
    }

    private function getIncomeExpensePayments($landlordId, $filters)
    {
        $db = \Config\Database::connect();

        try {
            $this->createIncomePaymentsTable($db);

            $builder = $db->table('income_expense_payments iep');
            $builder->select('
                iep.*,
                p.property_name,
                p.address as property_address,
                pu.unit_name,
                DATE_FORMAT(iep.date, "%y-%b") as period
            ');
            $builder->join('properties p', 'p.id = iep.property_id', 'left');
            $builder->join('property_units pu', 'pu.id = iep.unit_id', 'left');
            $builder->join('property_shareholders ps', 'ps.property_id = iep.property_id AND ps.user_id = ' . $landlordId, 'inner');
            $builder->where('ps.status', 'active');

            if ($filters['payment_type'] && $filters['payment_type'] !== 'all') {
                $builder->where('iep.type', $filters['payment_type']);
            }
            if ($filters['property_id']) {
                $builder->where('iep.property_id', $filters['property_id']);
            }
            if ($filters['date_from']) {
                $builder->where('iep.date >=', $filters['date_from']);
            }
            if ($filters['date_to']) {
                $builder->where('iep.date <=', $filters['date_to']);
            }

            $builder->orderBy('iep.date', 'DESC');
            return $builder->get()->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error fetching payments: ' . $e->getMessage());
            return [];
        }
    }

    private function calculateIncomeExpenseTotalsOptimized($landlordId)
    {
        $db = \Config\Database::connect();
        
        try {
            $propertiesBuilder = $db->table('property_shareholders ps');
            $propertiesBuilder->select('ps.property_id, ps.ownership_percentage, p.management_percentage');
            $propertiesBuilder->join('properties p', 'p.id = ps.property_id');
            $propertiesBuilder->where('ps.user_id', $landlordId);
            $propertiesBuilder->where('ps.status', 'active');
            
            $properties = $propertiesBuilder->get()->getResultArray();
            
            $totalNetIncome = 0;
            $totalExpenses = 0;
            $monthlyNetIncome = 0;
            
            foreach ($properties as $property) {
                $propertyId = $property['property_id'];
                $ownershipShare = $property['ownership_percentage'] / 100;
                $managementFeeRate = $property['management_percentage'] / 100;
                
                // All-time income and expenses
                $allTimeData = $db->query("
                    SELECT 
                        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
                    FROM income_expense_payments 
                    WHERE landlord_id = ? AND property_id = ?
                ", [$landlordId, $propertyId])->getRowArray();
                
                // Monthly income and expenses  
                $monthlyData = $db->query("
                    SELECT 
                        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as monthly_income,
                        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as monthly_expenses
                    FROM income_expense_payments 
                    WHERE landlord_id = ? AND property_id = ? 
                      AND YEAR(date) = ? AND MONTH(date) = ?
                ", [$landlordId, $propertyId, date('Y'), date('n')])->getRowArray();
                
                $allTimeIncome = $allTimeData['total_income'] ?? 0;
                $allTimeExpensesAmount = $allTimeData['total_expenses'] ?? 0;
                $monthlyIncome = $monthlyData['monthly_income'] ?? 0;
                $monthlyExpensesAmount = $monthlyData['monthly_expenses'] ?? 0;
                
                // Calculate with ownership shares
                $grossIncome = $allTimeIncome * $ownershipShare;
                $managementFees = $grossIncome * $managementFeeRate;
                $netIncomeFromProperty = $grossIncome - $managementFees;
                $expensesFromProperty = $allTimeExpensesAmount * $ownershipShare;
                $totalNetIncome += ($netIncomeFromProperty - $expensesFromProperty);
                
                $totalExpenses += $allTimeExpensesAmount;
                
                $monthlyGrossIncome = $monthlyIncome * $ownershipShare;
                $monthlyManagementFees = $monthlyGrossIncome * $managementFeeRate;
                $monthlyNetIncomeFromProperty = $monthlyGrossIncome - $monthlyManagementFees;
                $monthlyExpensesFromProperty = $monthlyExpensesAmount * $ownershipShare;
                $monthlyNetIncome += ($monthlyNetIncomeFromProperty - $monthlyExpensesFromProperty);
            }
            
            return [
                'net_income' => $totalNetIncome,
                'total_expenses' => $totalExpenses,
                'monthly_net' => $monthlyNetIncome
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error calculating totals: ' . $e->getMessage());
            return ['net_income' => 0, 'total_expenses' => 0, 'monthly_net' => 0];
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
                log_message('error', 'Failed to create table: ' . $e->getMessage());
            }
        }
    }

    private function generateReportHTML($payments, $user, $filters, $totals)
    {
        ob_start();
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Payment Report</title></head><body>';
        echo '<h1>Income & Expense Report</h1>';
        echo '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        echo '<p>Total Net Income: SAR ' . number_format($totals['net_income'], 2) . '</p>';
        echo '<p>Total Expenses: SAR ' . number_format($totals['total_expenses'], 2) . '</p>';
        echo '<p>Monthly Net Income: SAR ' . number_format($totals['monthly_net'], 2) . '</p>';
        echo '<table border="1" style="width:100%; border-collapse:collapse;">';
        echo '<tr><th>Date</th><th>Type</th><th>Property</th><th>Unit</th><th>Amount</th><th>Source</th></tr>';
        
        foreach ($payments as $payment) {
            echo '<tr>';
            echo '<td>' . date('Y-m-d', strtotime($payment['date'])) . '</td>';
            echo '<td>' . ucfirst($payment['type']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['property_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($payment['unit_name'] ?? '') . '</td>';
            echo '<td>SAR ' . number_format($payment['amount'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($payment['source'] ?? '') . '</td>';
            echo '</tr>';
        }
        
        echo '</table></body></html>';
        return ob_get_clean();
    }

    

    /**
     * Check if user is landlord and get user ID - FIXED
     */
    protected function requireLandlord()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        if ($userRole !== 'landlord') {
            return redirect()->to('/dashboard');
        }

        return null; // No redirect needed
    }

    
    /**
     * Get current user ID from session - FIXED
     */
    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    /**
     * Set success flash message - FIXED
     */
    protected function setSuccess($message)
    {
        session()->setFlashdata('success', $message);
    }

    /**
     * Set error flash message - FIXED  
     */
    protected function setError($message)
    {
        session()->setFlashdata('error', $message);
    }


    /**
     * Initialize user model if needed - FIXED VERSION
     */
    protected function initializeModels()
    {
        if (!isset($this->userModel)) {
            $this->userModel = model('UserModel');
        }
    }

    /**
     * Database debug helper - shows table structure
     *//*
    public function debugDatabase()
    {
        // Only allow in development
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $db = \Config\Database::connect();

        echo "<h2>Database Debug Information</h2>";
        echo "<style>body{font-family:monospace;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f2f2f2;}</style>";

        $tables = ['properties', 'property_units', 'property_shareholders', 'income_expense_payments'];

        foreach ($tables as $table) {
            echo "<h3>Table: {$table}</h3>";
            if ($db->tableExists($table)) {
                echo "<p style='color:green'>✅ Table exists</p>";

                $fields = $db->getFieldNames($table);
                echo "<p><strong>Fields:</strong> " . implode(', ', $fields) . "</p>";

                $count = $db->table($table)->countAllResults();
                echo "<p><strong>Record count:</strong> {$count}</p>";

                if ($count > 0 && $count <= 5) {
                    echo "<table>";
                    echo "<tr>";
                    foreach ($fields as $field) {
                        echo "<th>{$field}</th>";
                    }
                    echo "</tr>";

                    $records = $db->table($table)->limit(5)->get()->getResultArray();
                    foreach ($records as $record) {
                        echo "<tr>";
                        foreach ($fields as $field) {
                            echo "<td>" . htmlspecialchars($record[$field] ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p style='color:red'>❌ Table does not exist</p>";
            }
        }
    } */
}