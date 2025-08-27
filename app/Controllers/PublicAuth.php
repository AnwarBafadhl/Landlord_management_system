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

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userModel = new UserModel();

        try {
            // Simplified user data - only basic fields, no bank account
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role' => $this->request->getPost('role'),
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'is_active' => 1  // User is active immediately
            ];

            // Add optional basic contact fields only if they have values
            $phone = $this->request->getPost('phone');
            $address = $this->request->getPost('address');

            if (!empty($phone)) {
                $userData['phone'] = $phone;
            }

            if (!empty($address)) {
                $userData['address'] = $address;
            }

            // Try to insert the user
            if ($userModel->insert($userData)) {

                // Get the newly created user
                $newUser = $userModel->find($userModel->getInsertID());

                if ($newUser) {
                    // Log the user in immediately
                    session()->set([
                        'user_id' => $newUser['id'],
                        'username' => $newUser['username'],
                        'email' => $newUser['email'],
                        'role' => $newUser['role'],
                        'full_name' => $newUser['first_name'] . ' ' . $newUser['last_name'],
                        'is_logged_in' => true,
                        'isLoggedIn' => true
                    ]);

                    // Redirect directly to appropriate dashboard
                    $dashboardUrl = $this->getDashboardUrl($newUser['role']);
                    $welcomeMessage = "Welcome to Property Management System, " . $newUser['first_name'] . "! Your account has been created successfully.";

                    return redirect()->to($dashboardUrl)->with('success', $welcomeMessage);
                }

            } else {
                // Failed - get validation errors from model
                $errors = $userModel->errors();

                if (!empty($errors)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('validation', $errors);
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to create account. Please try again.');
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());

            // Check if it's a database constraint error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'email') !== false) {
                    $errorMsg = 'This email address is already registered. Please use a different email or try logging in.';
                } else {
                    $errorMsg = 'Username already exists. Please choose a different username.';
                }
            } else {
                $errorMsg = 'An error occurred while creating your account. Please try again.';
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMsg);
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