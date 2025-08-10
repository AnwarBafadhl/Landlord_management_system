<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    /**
     * Display login form
     */
    public function login()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to($this->getDashboardUrl(session()->get('role')));
        }

        $data = [
            'title' => 'Login - Landlord Management System',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Process login attempt
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->authenticate($username, $password);

        if ($user) {
            // Set session data
            session()->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['first_name'] . ' ' . $user['last_name'],
                'is_logged_in' => true,  // for BaseController
                'isLoggedIn' => true     // for your Landlord controller checks
            ]);

            // Redirect to appropriate dashboard
            return redirect()->to($this->getDashboardUrl($user['role']))
                ->with('success', 'Welcome back, ' . $user['first_name'] . '!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid username or password');
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully');
    }

    /**
     * Display registration form (for admins to create users)
     */
    public function register()
    {
        // Only allow admins to access registration
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Register User - Landlord Management System',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/register', $data);
    }

    /**
     * Process user registration
     */
    public function attemptRegister()
    {
        // Only allow admins to create users
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[admin,landlord,tenant,maintenance]',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]'
        ];

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
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_name' => $this->request->getPost('bank_name'),
            'is_active' => 1
        ];

        if ($userModel->insert($userData)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User created successfully');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user')
                ->with('validation', $userModel->errors());
        }
    }

    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Forgot Password - Landlord Management System',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userModel = new UserModel();
        $email = $this->request->getPost('email');
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            // Generate reset token (in production, implement proper token generation and email sending)
            $resetToken = bin2hex(random_bytes(32));

            // For now, just redirect with success message
            // In production, you would:
            // 1. Store the reset token in database with expiration
            // 2. Send email with reset link
            return redirect()->to('/auth/login')
                ->with('success', 'Password reset instructions have been sent to your email');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email address not found');
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

    /**
     * Check if current user is admin
     */
    protected function isAdmin()
    {
        return session()->get('role') === 'admin';
    }
}
