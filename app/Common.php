<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

/**
 * Sanitize filename for safe file downloads
 * 
 * @param string $filename
 * @return string
 */
if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename)
    {
        // Remove or replace invalid characters for filenames
        $filename = preg_replace('/[^\w\-_\. ]/', '_', $filename);
        
        // Remove multiple spaces and replace with single underscore
        $filename = preg_replace('/\s+/', '_', $filename);
        
        // Remove multiple underscores and replace with single underscore
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Remove leading/trailing underscores and dots
        $filename = trim($filename, '_.');
        
        // Ensure the filename is not empty
        if (empty($filename)) {
            $filename = 'download';
        }
        
        // Limit filename length (without extension)
        $pathinfo = pathinfo($filename);
        $name = $pathinfo['filename'] ?? $filename;
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
        
        if (strlen($name) > 200) {
            $name = substr($name, 0, 200);
        }
        
        return $name . $extension;
    }
}