<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * This filter ensures only admin users can access admin routes.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page');
        }

        // Check if user is admin
        if (session()->get('role') !== 'admin') {
            // Redirect to user's appropriate dashboard
            $userRole = session()->get('role');
            $dashboardUrl = $this->getDashboardUrl($userRole);
            return redirect()->to($dashboardUrl)->with('error', 'Access denied. Admin privileges required.');
        }

        return null;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /**
     * Get dashboard URL based on user role
     */
    private function getDashboardUrl($role)
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