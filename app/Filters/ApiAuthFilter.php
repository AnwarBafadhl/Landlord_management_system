<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * This filter handles API authentication for API routes.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // For API routes, check for API token or session authentication
        $authHeader = $request->getHeaderLine('Authorization');
        $apiToken = $request->getHeaderLine('X-API-Token');

        // Check for Bearer token
        if (!empty($authHeader) && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            return $this->validateApiToken($token);
        }

        // Check for API token header
        if (!empty($apiToken)) {
            return $this->validateApiToken($apiToken);
        }

        // Fall back to session authentication for AJAX requests
        if ($request->hasHeader('X-Requested-With') && 
            $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' && 
            session()->get('is_logged_in')) {
            return null; // Allow access
        }

        // Return JSON error for API routes
        $response = service('response');
        return $response->setJSON([
            'success' => false,
            'message' => 'Authentication required',
            'error' => 'UNAUTHORIZED'
        ])->setStatusCode(401);
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
        // Add CORS headers for API responses
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                 ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Token');

        return $response;
    }

    /**
     * Validate API token
     * In a real application, you would validate against a database
     */
    private function validateApiToken($token)
    {
        // For now, this is a simple token validation
        // In production, you would:
        // 1. Check token against database
        // 2. Verify token expiration
        // 3. Load user data associated with token
        
        if (empty($token) || strlen($token) < 32) {
            $response = service('response');
            return $response->setJSON([
                'success' => false,
                'message' => 'Invalid API token',
                'error' => 'INVALID_TOKEN'
            ])->setStatusCode(401);
        }

        // Token is valid, continue processing
        return null;
    }
}