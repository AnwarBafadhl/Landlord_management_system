<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Auth::login');

// Authentication Routes
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('attempt-login', 'Auth::attemptLogin');
    $routes->get('logout', 'Auth::logout');
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('process-forgot-password', 'Auth::processForgotPassword');

    // Reset Password Routes
    $routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
    $routes->post('process-reset-password', 'Auth::processResetPassword');
});

$routes->get('register', 'PublicAuth::register');
$routes->post('register', 'PublicAuth::attemptRegister');

// Admin Routes
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Admin::dashboard');

    // User Management
    $routes->get('users', 'Admin::users');
    $routes->get('users/create', 'Admin::createUser');
    $routes->post('users/store', 'Admin::storeUser');
    $routes->get('users/edit/(:num)', 'Admin::editUser/$1');
    $routes->post('users/update/(:num)', 'Admin::updateUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->get('users/toggle-status/(:num)', 'Admin::toggleUserStatus/$1');
    $routes->post('users/approve/(:num)', 'Admin::approveUser/$1');
    $routes->post('users/reject/(:num)', 'Admin::rejectUser/$1');

    // Property Management
    $routes->get('properties', 'Admin::properties');
    $routes->get('properties/create', 'Admin\Properties::create');
    $routes->post('properties/store', 'Admin\Properties::store');
    $routes->get('properties/edit/(:num)', 'Admin\Properties::edit/$1');
    $routes->post('properties/update/(:num)', 'Admin\Properties::update/$1');
    $routes->get('properties/delete/(:num)', 'Admin\Properties::delete/$1');
    $routes->get('properties/view/(:num)', 'Admin\Properties::view/$1');
    $routes->post('properties/assign-landlord', 'Admin\Properties::assignLandlord');
    $routes->post('properties/remove-landlord', 'Admin\Properties::removeLandlord');

    // Financial Management
    $routes->get('financials', 'Admin::financials');
    $routes->get('payments', 'Admin\Payments::index');
    $routes->get('payments/create', 'Admin\Payments::create');
    $routes->post('payments/store', 'Admin\Payments::store');
    $routes->post('payments/update-status/(:num)', 'Admin\Payments::updateStatus/$1');
    $routes->get('payments/export', 'Admin\Payments::export');

    // Maintenance Management
    $routes->get('maintenance', 'Admin\Maintenance::index');
    $routes->get('maintenance/view/(:num)', 'Admin\Maintenance::view/$1');
    $routes->post('maintenance/assign-staff', 'Admin\Maintenance::assignStaff');
    $routes->post('maintenance/update-status/(:num)', 'Admin\Maintenance::updateStatus/$1');

    // Reports
    $routes->get('reports', 'Admin\Reports::index');
    $routes->get('reports/financial', 'Admin\Reports::financial');
    $routes->get('reports/maintenance', 'Admin\Reports::maintenance');
    $routes->get('reports/occupancy', 'Admin\Reports::occupancy');

    // System Settings
    $routes->get('settings', 'Admin::settings');
    $routes->post('settings/update', 'Admin::updateSettings');
});

// COMPLETE ROUTES CONFIGURATION FOR ENHANCED MAINTENANCE SYSTEM

// Landlord Routes - Enhanced Maintenance Management
$routes->group('landlord', ['filter' => 'auth'], function ($routes) {

    // Dashboard & Profile
    $routes->get('dashboard', 'Landlord::dashboard');
    $routes->get('profile', 'Landlord::profile');
    $routes->post('profile/update', 'Landlord::updateProfile');
    $routes->post('profile/change-password', 'Landlord::changePassword');

    // Properties Management
    $routes->get('properties', 'Landlord::properties');
    $routes->get('request-property', 'Landlord::requestProperty');
    $routes->post('add-property', 'Landlord::addProperty');
    $routes->get('properties/view/(:num)', 'Landlord::viewProperty/$1');
    $routes->get('properties/edit/(:num)', 'Landlord::editProperty/$1');
    $routes->post('properties/update/(:num)', 'Landlord::updateProperty/$1');

    // Property Units Management
    $routes->post('properties/add-unit/(:num)', 'Landlord::addPropertyUnit/$1');
    $routes->post('properties/update-unit/(:num)/(:num)', 'Landlord::updatePropertyUnit/$1/$2');
    $routes->get('properties/remove-unit/(:num)/(:num)', 'Landlord::removePropertyUnit/$1/$2');

    // Property Owners Management  
    $routes->post('properties/add-owner/(:num)', 'Landlord::addPropertyOwner/$1');
    $routes->post('properties/update-owner/(:num)/(:num)', 'Landlord::updatePropertyOwner/$1/$2');
    $routes->get('properties/remove-owner/(:num)/(:num)', 'Landlord::removePropertyOwner/$1/$2');

    // ENHANCED MAINTENANCE MANAGEMENT
    $routes->get('maintenance', 'Landlord::maintenance');
    $routes->get('maintenance/get-units/(:num)', 'Landlord::getUnits/$1');
    $routes->post('add-maintenance-request', 'Landlord::addMaintenanceRequest');
    $routes->delete('delete-maintenance-request/(:num)', 'Landlord::deleteMaintenanceRequest/$1');
    $routes->get('maintenance/view-request/(:num)', 'Landlord::viewMaintenanceRequest/$1'); // NEW: View request details with images

    // Reports
    $routes->get('reports', 'Landlord::reports');
    $routes->get('download-report/(:num)', 'Landlord::downloadReport/$1');
    $routes->post('generate-ownership-report', 'Landlord::generateOwnershipReport');
    $routes->post('generate-income-expense-report', 'Landlord::generateIncomeExpenseReport');
    $routes->post('generate-maintenance-report', 'Landlord::generateMaintenanceReport');
    $routes->post('generate-monthly-report', 'Landlord::generateMonthlyReport');

    // Payments
    $routes->get('payments', 'Landlord::payments');
    $routes->post('income-payment/store', 'Landlord::storeIncomePayment');
    $routes->post('expense-payment/store', 'Landlord::storeExpensePayment');
    $routes->post('process-transfer-receipt', 'Landlord::processTransferReceipt');
    $routes->get('get-property-remaining-balance/(:num)', 'Landlord::getPropertyRemainingBalance/$1');
    $routes->get('get-transfer-history/(:num)', 'Landlord::getTransferHistory/$1');
    $routes->post('download-transfer-history-pdf', 'Landlord::downloadTransferHistoryPdf');


    // AJAX & API Routes
    $routes->get('get-units-by-property/(:num)', 'Landlord::getUnitsByProperty/$1');

    // File Downloads & Views
    $routes->get('download-transfer-receipt/(:any)', 'Landlord::downloadTransferReceipt/$1');
    $routes->get('download-receipt/(:any)', 'Landlord::downloadReceipt/$1');
    $routes->get('view-receipt-file/(:any)', 'Landlord::viewReceiptFile/$1');

    // Help & Support
    $routes->get('help', 'Landlord::help');
    $routes->post('send-admin-message', 'Landlord::sendAdminMessage');

    $routes->get('maintenance/image/(:any)', 'Landlord::serveMaintenanceImage/$1');
});

// Maintenance routes group with authentication filter
$routes->group('maintenance', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Maintenance::dashboard');
    $routes->get('/', 'Maintenance::dashboard'); // Default route

    // Requests management
    $routes->get('requests', 'Maintenance::requests');
    $routes->get('requests/view/(:num)', 'Maintenance::view/$1');
    $routes->get('requests/(:num)', 'Maintenance::viewRequest/$1'); // Alternative route

    // Request actions (AJAX endpoints)
    $routes->post('requests/accept/(:num)', 'Maintenance::accept/$1');
    $routes->post('requests/start/(:num)', 'Maintenance::start/$1');
    $routes->post('requests/complete/(:num)', 'Maintenance::complete/$1');
    $routes->post('requests/upload-image/(:num)', 'Maintenance::uploadImage/$1');
    $routes->post('requests/update-status/(:num)', 'Maintenance::updateStatus/$1');

    // Schedule management
    $routes->get('schedule', 'Maintenance::schedule');
    $routes->post('schedule/update', 'Maintenance::updateAvailability');
    $routes->post('schedule/set-availability', 'Maintenance::setAvailability'); // Alternative endpoint

    // Profile management
    $routes->get('profile', 'Maintenance::profile');
    $routes->post('profile/update', 'Maintenance::updateProfile');
    $routes->post('profile/change-password', 'Maintenance::changePassword');
    $routes->post('profile/set-availability', 'Maintenance::setAvailability');

    // Help/Support - FIXED ROUTES
    $routes->get('help', 'Maintenance::profile'); // Redirect GET help to profile
    $routes->post('help', 'Maintenance::help');
    $routes->post('send-help', 'Maintenance::sendHelpMessage'); // Alternative endpoint

    // Admin functions (CLI/Admin only)
    $routes->get('auto-reject-stale', 'Maintenance::autoRejectStaleRequests');
});

// CRON/Scheduled Tasks Routes
$routes->group('cron', function ($routes) {
    // NEW: Auto-rejection of stale maintenance requests
    $routes->get('auto-reject-stale-maintenance', 'Maintenance::autoRejectStaleRequests');

    // Other CRON tasks
    $routes->get('generate-rent-payments', 'Cron::generateRentPayments');
    $routes->get('mark-overdue-payments', 'Cron::markOverduePayments');
    $routes->get('backup-database', 'Cron::backupDatabase');
});

// Admin Routes - Enhanced Maintenance Management
$routes->group('admin', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Admin::dashboard');

    // User Management
    $routes->get('users', 'Admin::users');
    $routes->get('users/create', 'Admin::createUser');
    $routes->post('users/store', 'Admin::storeUser');
    $routes->get('users/edit/(:num)', 'Admin::editUser/$1');
    $routes->post('users/update/(:num)', 'Admin::updateUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->get('users/toggle-status/(:num)', 'Admin::toggleUserStatus/$1');
    $routes->post('users/approve/(:num)', 'Admin::approveUser/$1');
    $routes->post('users/reject/(:num)', 'Admin::rejectUser/$1');

    // Property Management
    $routes->get('properties', 'Admin::properties');
    $routes->get('properties/create', 'Admin\Properties::create');
    $routes->post('properties/store', 'Admin\Properties::store');
    $routes->get('properties/edit/(:num)', 'Admin\Properties::edit/$1');
    $routes->post('properties/update/(:num)', 'Admin\Properties::update/$1');
    $routes->get('properties/delete/(:num)', 'Admin\Properties::delete/$1');
    $routes->get('properties/view/(:num)', 'Admin\Properties::view/$1');
    $routes->post('properties/assign-landlord', 'Admin\Properties::assignLandlord');
    $routes->post('properties/remove-landlord', 'Admin\Properties::removeLandlord');

    // Financial Management
    $routes->get('financials', 'Admin::financials');
    $routes->get('payments', 'Admin\Payments::index');
    $routes->get('payments/create', 'Admin\Payments::create');
    $routes->post('payments/store', 'Admin\Payments::store');
    $routes->post('payments/update-status/(:num)', 'Admin\Payments::updateStatus/$1');
    $routes->get('payments/export', 'Admin\Payments::export');

    // ENHANCED MAINTENANCE MANAGEMENT FOR ADMIN
    $routes->get('maintenance', 'Admin\Maintenance::index');
    $routes->get('maintenance/view/(:num)', 'Admin\Maintenance::view/$1');
    $routes->post('maintenance/assign-staff', 'Admin\Maintenance::assignStaff');
    $routes->post('maintenance/update-status/(:num)', 'Admin\Maintenance::updateStatus/$1');
    $routes->get('maintenance/export', 'Admin\Maintenance::export');
    $routes->post('maintenance/bulk-assign', 'Admin\Maintenance::bulkAssignStaff');     // NEW: Bulk operations
    $routes->post('maintenance/force-reject/(:num)', 'Admin\Maintenance::forceReject/$1'); // NEW: Admin override

    // Reports
    $routes->get('reports', 'Admin\Reports::index');
    $routes->get('reports/financial', 'Admin\Reports::financial');
    $routes->get('reports/maintenance', 'Admin\Reports::maintenance');
    $routes->get('reports/occupancy', 'Admin\Reports::occupancy');

    // System Settings
    $routes->get('settings', 'Admin::settings');
    $routes->post('settings/update', 'Admin::updateSettings');
});

// File Serving Routes (for maintenance images)
$routes->group('uploads', function ($routes) {
    $routes->post('property-images', 'Uploads::propertyImages', ['filter' => 'auth']);
    $routes->post('maintenance-images', 'Uploads::maintenanceImages', ['filter' => 'auth']);
    $routes->post('documents', 'Uploads::documents', ['filter' => 'auth']);
    $routes->get('serve/(:any)', 'Uploads::serve/$1', ['filter' => 'auth']);

    // NEW: Serve maintenance completion images
    $routes->get('maintenance/(:any)', 'Uploads::serveMaintenanceImage/$1', ['filter' => 'auth']);
});

// API Routes (for mobile app or AJAX calls)
$routes->group('api', function ($routes) {
    // Authentication
    $routes->post('login', 'Api\Auth::login');
    $routes->post('logout', 'Api\Auth::logout', ['filter' => 'api-auth']);

    // Properties
    $routes->get('properties', 'Api\Properties::index', ['filter' => 'api-auth']);
    $routes->get('properties/(:num)', 'Api\Properties::show/$1', ['filter' => 'api-auth']);

    // Payments
    $routes->get('payments', 'Api\Payments::index', ['filter' => 'api-auth']);
    $routes->post('payments', 'Api\Payments::store', ['filter' => 'api-auth']);

    // ENHANCED MAINTENANCE API
    $routes->get('maintenance', 'Api\Maintenance::index', ['filter' => 'api-auth']);
    $routes->post('maintenance', 'Api\Maintenance::store', ['filter' => 'api-auth']);
    $routes->put('maintenance/(:num)', 'Api\Maintenance::update/$1', ['filter' => 'api-auth']);
    $routes->post('maintenance/(:num)/images', 'Api\Maintenance::uploadImage/$1', ['filter' => 'api-auth']); // NEW: API image upload
    $routes->get('maintenance/(:num)/images', 'Api\Maintenance::getImages/$1', ['filter' => 'api-auth']);    // NEW: API get images
});

// CLI Routes
$routes->cli('migrate', 'Cli\Migrate::run');
$routes->cli('seed', 'Cli\Seed::run');
$routes->cli('create-admin', 'Cli\Setup::createAdmin');

// NEW: CLI route for maintenance cleanup
$routes->cli('maintenance/auto-reject', 'Maintenance::autoRejectStaleRequests');

// Public Routes (no authentication required)
$routes->group('public', function ($routes) {
    $routes->get('property-search', 'Public::propertySearch');
    $routes->get('contact', 'Public::contact');
    $routes->post('contact/send', 'Public::sendContact');
});

// Authentication Routes
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('attempt-login', 'Auth::attemptLogin');
    $routes->get('logout', 'Auth::logout');
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('process-forgot-password', 'Auth::processForgotPassword');

    // Reset Password Routes
    $routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
    $routes->post('process-reset-password', 'Auth::processResetPassword');
});

// Registration Routes
$routes->get('register', 'PublicAuth::register');
$routes->post('register', 'PublicAuth::attemptRegister');

// Default route
$routes->get('/', 'Auth::login');

// Error Pages
$routes->set404Override();

// Redirects for common mistyped URLs
$routes->addRedirect('admin', 'admin/dashboard');
$routes->addRedirect('landlord', 'landlord/dashboard');
$routes->addRedirect('maintenance', 'maintenance/dashboard');
$routes->addRedirect('login', 'auth/login');
$routes->addRedirect('logout', 'auth/logout');