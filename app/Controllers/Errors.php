<?php
// 2. Create an Errors controller if it doesn't exist
// Create file: app/Controllers/Errors.php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Errors extends BaseController
{
    public function show404()
    {
        return view('errors/custom_404'); // or echo 'Not found';
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