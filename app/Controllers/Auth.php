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
     * Add this method to your Auth controller
     */
    public function forgotPassword()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to($this->getDashboardUrl(session()->get('role')));
        }

        $data = [
            'title' => 'Forgot Password - Property Management System',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Display reset password form
     * Add this method to your Auth controller
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to('/auth/forgot-password')
                ->with('error', 'Invalid reset token.');
        }

        // Check if token exists and is not expired
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            return redirect()->to('/auth/forgot-password')
                ->with('error', 'Invalid or expired reset token. Please request a new password reset.');
        }

        $data = [
            'title' => 'Reset Password - Property Management System',
            'token' => $token,
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Process Forgot Password - Working Version with Basic Mail
     * Replace your current processForgotPassword method with this
     */
    public function processForgotPassword()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $email = $this->request->getPost('email');
        log_message('info', 'Password reset requested for: ' . $email);

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->where('is_active', 1)->first();

        if (!$user) {
            log_message('warning', 'Password reset requested for non-existent email: ' . $email);
            // Don't reveal if email exists or not for security
            return redirect()->back()
                ->with('success', 'If the email exists in our system, you will receive reset instructions shortly.');
        }

        log_message('info', 'User found for reset: ' . $user['username']);

        try {
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Check if reset columns exist in database
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('users');

            if (!in_array('reset_token', $fields) || !in_array('reset_expires', $fields)) {
                log_message('error', 'reset_token or reset_expires columns missing from users table');
                return redirect()->back()
                    ->with('error', 'Password reset feature is not properly configured. Please contact administrator.');
            }

            // Save reset token
            $updateResult = $userModel->update($user['id'], [
                'reset_token' => $resetToken,
                'reset_expires' => $resetExpiry
            ]);

            if (!$updateResult) {
                throw new \Exception('Failed to save reset token');
            }

            log_message('info', 'Reset token saved for user: ' . $user['id']);

            // Send email using basic mail function
            $emailService = \Config\Services::email();

            $resetUrl = site_url('auth/reset-password/' . $resetToken);

            $subject = 'Password Reset Request - Property Management System';
            $message = "Hello {$user['first_name']} {$user['last_name']},\n\n";
            $message .= "We received a request to reset your password for your Property Management System account.\n\n";
            $message .= "Click the link below to reset your password:\n";
            $message .= $resetUrl . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this password reset, please ignore this email.\n\n";
            $message .= "Best regards,\n";
            $message .= "Property Management System";

            $emailService->setTo($email);
            $emailService->setSubject($subject);
            $emailService->setMessage($message);

            log_message('info', 'Attempting to send email to: ' . $email);

            $emailSent = $emailService->send();

            if ($emailSent) {
                log_message('info', 'Password reset email sent successfully');
                return redirect()->back()
                    ->with('success', 'Password reset instructions have been sent to your email address.');
            } else {
                $emailError = $emailService->printDebugger(['headers']);
                log_message('error', 'Failed to send email: ' . $emailError);

                return redirect()->back()
                    ->with('error', 'Failed to send email. Please contact administrator or try again later.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Password reset exception: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while processing your request. Please try again.');
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
