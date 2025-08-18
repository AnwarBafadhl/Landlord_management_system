<?php
// 2. Create an Errors controller if it doesn't exist
// Create file: app/Controllers/Errors.php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Errors extends BaseController
{
    public function show404()
    {
        $this->response->setStatusCode(404);
        
        if (session()->get('role') === 'admin') {
            return view('admin/errors/404');
        } elseif (session()->get('role') === 'landlord') {
            return view('landlord/errors/404');
        } elseif (session()->get('role') === 'tenant') {
            return view('tenant/errors/404');
        } else {
            return view('errors/404');
        }
    }
    
    public function showError($message = 'An error occurred')
    {
        $data = [
            'title' => 'Error',
            'message' => $message
        ];
        
        return view('errors/general', $data);
    }
}

?>