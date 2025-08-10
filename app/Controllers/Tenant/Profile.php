<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Check if user is tenant
     */
    protected function requireTenant()  // Changed from private to protected
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'tenant') {
            return redirect()->to('/auth/login');
        }
        return null;
    }

    /**
     * Get current user ID
     */
    protected function getCurrentUserId()  // Changed from private to protected
    {
        return session()->get('user_id');
    }

    /**
     * Set success message
     */
    protected function setSuccess($message)  // Changed from private to protected
    {
        session()->setFlashdata('success', $message);
    }

    /**
     * Set error message
     */
    protected function setError($message)  // Changed from private to protected
    {
        session()->setFlashdata('error', $message);
    }

    public function index()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];

        return view('tenant/profile', $data);
    }

    public function update()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();
        
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name'  => 'required|min_length[2]|max_length[50]',
            'email'      => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone'      => 'permit_empty|min_length[10]|max_length[15]',
            'address'    => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone'),
            'address'    => $this->request->getPost('address'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->update($userId, $data)) {
            // Update session data
            session()->set([
                'full_name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email']
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
            }
            return redirect()->to('tenant/profile')->with('success', 'Profile updated successfully!');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ]);
            }
            return redirect()->back()->with('error', 'Failed to update profile');
        }
    }

    public function changePassword()
    {
        $redirect = $this->requireTenant();
        if ($redirect) return $redirect;

        $userId = $this->getCurrentUserId();
        
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Get current user
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Verify current password
        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
        }

        // Update password
        $data = [
            'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->update($userId, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to change password'
            ]);
        }
    }
}