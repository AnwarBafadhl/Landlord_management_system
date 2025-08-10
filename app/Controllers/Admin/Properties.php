<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Models\UserModel;

class Properties extends BaseController
{
    protected $propertyModel;
    protected $userModel;

    public function __construct()
    {
        $this->propertyModel = new PropertyModel();
        $this->userModel = new UserModel();
    }

    /**
     * Properties list
     */
    public function index()
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $properties = $this->propertyModel->getPropertiesWithLandlords();

        $data = [
            'title' => 'Property Management',
            'properties' => $properties
        ];

        return view('admin/properties/index', $data);
    }

    /**
     * Create property form
     */
    public function create()
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $landlords = $this->userModel->getLandlords();

        $data = [
            'title' => 'Add New Property',
            'landlords' => $landlords,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/properties/create', $data);
    }

    /**
     * Store new property
     */
    public function store()
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'address' => 'required|min_length[10]',
            'property_type' => 'required|in_list[apartment,house,condo,commercial]',
            'base_rent' => 'required|decimal|greater_than[0]',
            'bedrooms' => 'integer|greater_than_equal_to[0]',
            'bathrooms' => 'decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $propertyData = [
            'property_name' => $this->request->getPost('property_name'),
            'address' => $this->request->getPost('address'),
            'property_type' => $this->request->getPost('property_type'),
            'bedrooms' => $this->request->getPost('bedrooms') ?: 0,
            'bathrooms' => $this->request->getPost('bathrooms') ?: 0,
            'square_feet' => $this->request->getPost('square_feet'),
            'base_rent' => $this->request->getPost('base_rent'),
            'deposit' => $this->request->getPost('deposit') ?: 0,
            'description' => $this->request->getPost('description'),
            'status' => 'vacant'
        ];

        if ($propertyId = $this->propertyModel->insert($propertyData)) {
            // Assign landlord if selected
            $landlordId = $this->request->getPost('landlord_id');
            $ownershipPercentage = $this->request->getPost('ownership_percentage') ?: 100;
            
            if ($landlordId) {
                $this->propertyModel->assignLandlord($propertyId, $landlordId, $ownershipPercentage);
            }

            $this->setSuccess('Property added successfully');
            return redirect()->to('/admin/properties');
        } else {
            $this->setError('Failed to add property');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit property form
     */
    public function edit($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $property = $this->propertyModel->find($id);
        if (!$property) {
            $this->setError('Property not found');
            return redirect()->to('/admin/properties');
        }

        $landlords = $this->userModel->getLandlords();
        $propertyLandlords = $this->propertyModel->getPropertyLandlords($id);

        $data = [
            'title' => 'Edit Property',
            'property' => $property,
            'landlords' => $landlords,
            'property_landlords' => $propertyLandlords,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/properties/edit', $data);
    }

    /**
     * Update property
     */
    public function update($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $property = $this->propertyModel->find($id);
        if (!$property) {
            $this->setError('Property not found');
            return redirect()->to('/admin/properties');
        }

        $rules = [
            'property_name' => 'required|min_length[3]|max_length[100]',
            'address' => 'required|min_length[10]',
            'property_type' => 'required|in_list[apartment,house,condo,commercial]',
            'base_rent' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $propertyData = [
            'property_name' => $this->request->getPost('property_name'),
            'address' => $this->request->getPost('address'),
            'property_type' => $this->request->getPost('property_type'),
            'bedrooms' => $this->request->getPost('bedrooms') ?: 0,
            'bathrooms' => $this->request->getPost('bathrooms') ?: 0,
            'square_feet' => $this->request->getPost('square_feet'),
            'base_rent' => $this->request->getPost('base_rent'),
            'deposit' => $this->request->getPost('deposit') ?: 0,
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->propertyModel->update($id, $propertyData)) {
            $this->setSuccess('Property updated successfully');
        } else {
            $this->setError('Failed to update property');
        }

        return redirect()->to('/admin/properties');
    }

    /**
     * Delete property
     */
    public function delete($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        if ($this->propertyModel->delete($id)) {
            $this->setSuccess('Property deleted successfully');
        } else {
            $this->setError('Failed to delete property');
        }

        return redirect()->to('/admin/properties');
    }

    /**
     * View property details
     */
    public function view($id)
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $properties = $this->propertyModel->getPropertiesWithLandlords($id);
        if (empty($properties)) {
            $this->setError('Property not found');
            return redirect()->to('/admin/properties');
        }

        $data = [
            'title' => 'Property Details',
            'properties' => $properties
        ];

        return view('admin/properties/view', $data);
    }

    /**
     * Assign landlord to property
     */
    public function assignLandlord()
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $propertyId = $this->request->getPost('property_id');
        $landlordId = $this->request->getPost('landlord_id');
        $ownershipPercentage = $this->request->getPost('ownership_percentage') ?: 100;

        if ($this->propertyModel->assignLandlord($propertyId, $landlordId, $ownershipPercentage)) {
            return $this->respondWithSuccess([], 'Landlord assigned successfully');
        } else {
            return $this->respondWithError('Failed to assign landlord');
        }
    }

    /**
     * Remove landlord from property
     */
    public function removeLandlord()
    {
        $redirect = $this->requireAdmin();
        if ($redirect) return $redirect;

        $propertyId = $this->request->getPost('property_id');
        $landlordId = $this->request->getPost('landlord_id');

        if ($this->propertyModel->removeLandlord($propertyId, $landlordId)) {
            return $this->respondWithSuccess([], 'Landlord removed successfully');
        } else {
            return $this->respondWithError('Failed to remove landlord');
        }
    }
}