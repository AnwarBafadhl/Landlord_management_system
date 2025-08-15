<?php

namespace App\Controllers;

use App\Models\UserModel;

class PublicAuth extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    /**
     * Display public registration form
     */
    public function register()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to($this->getDashboardUrl(session()->get('role')));
        }

        $data = [
            'title' => 'Register - Property Management System',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/register', $data);
    }

    /**
     * Process public registration attempt
     */
    public function attemptRegister()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[landlord,tenant,maintenance]',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'terms' => 'required'
        ];

        // REMOVED: Bank account validation since you deleted those fields
        // No more role-specific validation needed

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userModel = new UserModel();
        
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => $this->request->getPost('role'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            // REMOVED: bank_account and bank_name since you deleted those fields
            'is_active' => 0  // All public registrations need admin approval
        ];

        try {
            if ($userModel->insert($userData)) {
                // Different messages based on role
                $message = $this->getSuccessMessage($this->request->getPost('role'));
                
                return redirect()->to('/register/pending')
                               ->with('success', $message)
                               ->with('pending_user_role', $this->request->getPost('role'));
            } else {
                $errors = $userModel->errors();
                if (!empty($errors)) {
                    return redirect()->back()
                                   ->withInput()
                                   ->with('validation', $userModel->errors());
                } else {
                    return redirect()->back()
                                   ->withInput()
                                   ->with('error', 'Failed to create account. Please try again.');
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred while creating your account. Please try again.');
        }
    }

    /**
     * Show pending approval page
     */
    public function pending()
    {
        $data = [
            'title' => 'Registration Pending - Property Management System',
            'role' => session()->getFlashdata('pending_user_role') ?? 'user'
        ];

        return view('auth/pending', $data);
    }

    /**
     * Request activation (for users who registered but haven't been activated)
     */
    public function requestActivation()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userModel = new UserModel();
        $email = $this->request->getPost('email');
        $user = $userModel->where('email', $email)->where('is_active', 0)->first();

        if ($user) {
            // In a real application, you would send an email to admins
            // For now, just show a success message
            return redirect()->to('/register/pending')
                           ->with('success', 'Activation request sent. An administrator will review your account shortly.');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Account not found or already activated.');
        }
    }

    /**
     * Get success message based on role (UPDATED - No bank info mention)
     */
    private function getSuccessMessage($role)
    {
        switch ($role) {
            case 'landlord':
                return 'Your landlord account has been created and is pending admin approval. You will be notified once your account is activated.';
            case 'tenant':
                return 'Your tenant account has been created and is pending approval. A landlord will need to assign you to a property before you can access the system.';
            case 'maintenance':
                return 'Your maintenance staff account has been created and is pending admin approval. You will be notified once your credentials are verified.';
            default:
                return 'Your account has been created and is pending approval.';
        }
    }

    /**
     * Get dashboard URL based on user role
     */
    protected function getDashboardUrl($role)
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'landlord':
                return '/landlord/dashboard';
            case 'tenant':
                return '/tenant/dashboard';
            case 'maintenance':
                return '/maintenance/dashboard';
            default:
                return '/auth/login';
        }
    }
}