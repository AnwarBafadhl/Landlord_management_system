<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'url', 'text', 'date'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn()
    {
        return session()->get('is_logged_in') === true;
    }

    /**
     * Check if user has specific role
     */
    protected function hasRole($role)
    {
        return session()->get('role') === $role;
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is landlord
     */
    protected function isLandlord()
    {
        return $this->hasRole('landlord');
    }

    /**
     * Check if user is maintenance staff
     */
    protected function isMaintenance()
    {
        return $this->hasRole('maintenance');
    }

    /**
     * Require login - redirect to login if not authenticated
     */
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page');
        }
        return null;
    }

    /**
     * Require specific role - redirect if user doesn't have required role
     */
    protected function requireRole($role)
    {
        $loginCheck = $this->requireLogin();
        if ($loginCheck) {
            return $loginCheck;
        }

        if (!$this->hasRole($role)) {
            return redirect()->to($this->getDashboardUrl($role))->with('error', 'Access denied');
        }
        return null;
    }

    /**
     * Require admin role
     */
    protected function requireAdmin()
    {
        return $this->requireRole('admin');
    }

    /**
     * Require landlord role
     */
    protected function requireLandlord()
    {
        return $this->requireRole('landlord');
    }

    /**
     * Require maintenance role
     */
    protected function requireMaintenance()
    {
        return $this->requireRole('maintenance');
    }

    /**
     * Get current user ID
     */
    protected function getCurrentUserId()
    {
        return session()->get('user_id');
    }

    /**
     * Get current user role
     */
    protected function getCurrentUserRole()
    {
        return session()->get('role');
    }

    /**
     * Get current user data
     */
    protected function getCurrentUser()
    {
        return [
            'id' => session()->get('user_id'),
            'username' => session()->get('username'),
            'email' => session()->get('email'),
            'role' => session()->get('role'),
            'full_name' => session()->get('full_name')
        ];
    }

    /**
     * Get dashboard URL based on current user role or provided role
     */
    protected function getDashboardUrl($role)
    {
        if ($role === null) {
            $role = session()->get('role');
        }
        
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'landlord':
                return '/landlord/dashboard';
            case 'maintenance':
                return '/maintenance/dashboard';
            default:
                return '/auth/login';
        }
    }

    /**
     * Set flash message
     */
    protected function setMessage($type, $message)
    {
        session()->setFlashdata($type, $message);
    }

    /**
     * Set success message
     */
    protected function setSuccess($message)
    {
        $this->setMessage('success', $message);
    }

    /**
     * Set error message
     */
    protected function setError($message)
    {
        $this->setMessage('error', $message);
    }

    /**
     * Set info message
     */
    protected function setInfo($message)
    {
        $this->setMessage('info', $message);
    }

    /**
     * Set warning message
     */
    protected function setWarning($message)
    {
        $this->setMessage('warning', $message);
    }

    /**
     * Return JSON response
     */
    protected function respondWithJson($data, $statusCode = 200)
    {
        return $this->response->setJSON($data)->setStatusCode($statusCode);
    }

    /**
     * Return success JSON response
     */
    protected function respondWithSuccess($data = [], $message = 'Success')
    {
        return $this->respondWithJson([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return error JSON response
     */
    protected function respondWithError($message = 'Error', $statusCode = 400)
    {
        return $this->respondWithJson([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload($fieldName, $uploadPath = 'uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        $file = $this->request->getFile($fieldName);
        
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Validate file type
        $extension = $file->getClientExtension();
        if (!in_array(strtolower($extension), $allowedTypes)) {
            throw new \Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
        }

        // Generate unique filename
        $newName = $file->getRandomName();
        
        // Create upload directory if it doesn't exist
        $fullPath = WRITEPATH . 'uploads/' . $uploadPath;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Move file
        if ($file->move($fullPath, $newName)) {
            return $uploadPath . $newName;
        }

        throw new \Exception('Failed to upload file');
    }

    /**
     * Delete uploaded file
     */
    protected function deleteFile($filePath)
    {
        $fullPath = WRITEPATH . 'uploads/' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
            return true;
        }
        return false;
    }

    /**
     * Format currency
     */
    protected function formatCurrency($amount)
    {
        return '$' . number_format($amount, 2);
    }

    /**
     * Format date
     */
    protected function formatDate($date, $format = 'M d, Y')
    {
        return date($format, strtotime($date));
    }
}