<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GuestFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * This filter is used to redirect already authenticated users
     * away from auth pages (login, register) to their dashboard.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('is_logged_in')) {
            $userRole = session()->get('role');
            $dashboardUrl = $this->getDashboardUrl($userRole);
            return redirect()->to($dashboardUrl);
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
            case 'maintenance':
                return '/maintenance/dashboard';
            default:
                return '/auth/login';
        }
    }
}