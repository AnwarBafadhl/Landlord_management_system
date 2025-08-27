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

    // Lease Management
    $routes->get('leases', 'Admin\Leases::index');
    $routes->get('leases/create', 'Admin\Leases::create');
    $routes->post('leases/store', 'Admin\Leases::store');
    $routes->get('leases/edit/(:num)', 'Admin\Leases::edit/$1');
    $routes->post('leases/update/(:num)', 'Admin\Leases::update/$1');
    $routes->post('leases/terminate/(:num)', 'Admin\Leases::terminate/$1');
    $routes->post('leases/renew/(:num)', 'Admin\Leases::renew/$1');

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

    // AJAX Routes
    $routes->post('send-payment-reminder/(:num)', 'Admin\Ajax::sendPaymentReminder/$1');
    $routes->post('mark-payment-paid/(:num)', 'Admin\Ajax::markPaymentPaid/$1');
    $routes->post('generate-monthly-payments', 'Admin\Ajax::generateMonthlyPayments');
});

$routes->group('landlord', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Landlord::dashboard');
    $routes->get('profile', 'Landlord::profile');
    $routes->post('profile/update', 'Landlord::updateProfile');
    $routes->post('profile/change-password', 'Landlord::changePassword');

    // Properties Management
    $routes->get('properties', 'Landlord::properties');
    $routes->get('properties/view/(:num)', 'Landlord::viewProperty/$1');
    $routes->get('properties/edit/(:num)', 'Landlord::editProperty/$1');
    $routes->post('properties/update/(:num)', 'Landlord::updateProperty/$1');

    // Owner/Shareholder management routes
    $routes->post('properties/add-owner/(:num)', 'Landlord::addOwner/$1');
    $routes->post('properties/update-owner/(:num)/(:num)', 'Landlord::updateOwner/$1/$2');
    $routes->post('properties/remove-owner/(:num)/(:num)', 'Landlord::removeOwner/$1/$2');

    // Unit management routes
    $routes->post('properties/add-unit/(:num)', 'Landlord::addUnit/$1');
    $routes->post('properties/update-unit/(:num)/(:num)', 'Landlord::updateUnit/$1/$2');
    $routes->post('properties/remove-unit/(:num)/(:num)', 'Landlord::removeUnit/$1/$2');

    // Property creation
    $routes->get('request-property', 'Landlord::requestProperty');
    $routes->post('add-property', 'Landlord::addProperty');

    // ENHANCED MONTHLY INCOME & EXPENSE TRACKING (Advanced Feature)
    $routes->get('monthly-tracking', 'Landlord::monthlyTracking'); // Advanced monthly tracking page
    $routes->post('monthly-income/store', 'Landlord::storeMonthlyIncome'); // Store/update monthly income
    $routes->post('monthly-expenses/store', 'Landlord::storeMonthlyExpenses'); // Store/update monthly expenses
    $routes->get('get-unit-monthly-data', 'Landlord::getUnitMonthlyData'); // Get unit monthly data (AJAX)

    // LEGACY MAINTENANCE PAYMENT ROUTES (Keep for backward compatibility)
    $routes->post('maintenance-payment/store', 'Landlord::storeMaintenancePayment'); // Legacy maintenance payment
    $routes->get('get-maintenance-requests', 'Landlord::getMaintenanceRequests'); // Legacy maintenance requests

    // ENHANCED REPORTS & EXPORT ROUTES
    $routes->get('reports', 'Landlord::reports'); // Enhanced reports page
    $routes->post('generate-monthly-report', 'Landlord::generateMonthlyReport'); // Generate comprehensive reports

    // Legacy Reports (keep for backward compatibility)
    $routes->post('reports/generate-ownership-pdf', 'Landlord::generateOwnershipPdf');

    // Maintenance
    $routes->get('maintenance', 'Landlord::maintenance');
    $routes->post('add-maintenance-request', 'Landlord::addMaintenanceRequest');
    $routes->post('update-maintenance-status/(:num)', 'Landlord::updateMaintenanceStatus/$1');
    $routes->get('export-maintenance-report', 'Landlord::exportMaintenanceReport');
    $routes->get('get-units-by-property/(:num)', 'Landlord::getUnitsByProperty/$1');

    // Help & Support
    $routes->get('help', 'Landlord::help');

    // PAYMENT MANAGEMENT
    $routes->get('payments', 'Landlord::payments');
    $routes->post('income-payment/store', 'Landlord::storeIncomePayment');
    $routes->post('expense-payment/store', 'Landlord::storeExpensePayment');

    // EXPORT ROUTES - Multiple options for testing
    $routes->get('payments/export-excel', 'Landlord::exportPaymentsExcel'); // Main CSV
    $routes->get('payments/export-pdf', 'Landlord::exportPaymentsPDF'); // PDF
    $routes->get('payments/export-simple-csv', 'Landlord::exportPaymentsSimpleCSV'); // Backup CSV

    // RECEIPT HANDLING
    $routes->get('receipt/download/(:segment)', 'Landlord::downloadReceipt/$1');
    $routes->get('receipt/view/(:segment)', 'Landlord::viewReceiptFile/$1');
    $routes->get('receipt-file/(:segment)', 'Landlord::serveReceiptFile/$1');

    // AJAX Routes
    $routes->get('get-units-by-property/(:num)', 'Landlord::getUnitsByProperty/$1');
});


// Maintenance Staff Routes
$routes->group('maintenance', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Maintenance::dashboard');

    // Requests
    $routes->get('requests', 'Maintenance::requests');
    $routes->get('requests/view/(:num)', 'Maintenance::viewRequest/$1');
    $routes->post('requests/update-status/(:num)', 'Maintenance::updateStatus/$1');
    $routes->post('requests/complete/(:num)', 'Maintenance::completeRequest/$1');
    $routes->post('requests/upload-image/(:num)', 'Maintenance::uploadImage/$1');

    // Schedule
    $routes->get('schedule', 'Maintenance::schedule');
    $routes->post('schedule/update', 'Maintenance::updateSchedule');

    // Profile
    $routes->get('profile', 'Maintenance::profile');
    $routes->post('profile/update', 'Maintenance::updateProfile');
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

    // Maintenance
    $routes->get('maintenance', 'Api\Maintenance::index', ['filter' => 'api-auth']);
    $routes->post('maintenance', 'Api\Maintenance::store', ['filter' => 'api-auth']);
    $routes->put('maintenance/(:num)', 'Api\Maintenance::update/$1', ['filter' => 'api-auth']);
});

// File Upload Routes
$routes->group('uploads', function ($routes) {
    $routes->post('property-images', 'Uploads::propertyImages', ['filter' => 'auth']);
    $routes->post('maintenance-images', 'Uploads::maintenanceImages', ['filter' => 'auth']);
    $routes->post('documents', 'Uploads::documents', ['filter' => 'auth']);
    $routes->get('serve/(:any)', 'Uploads::serve/$1', ['filter' => 'auth']);
});

// CRON/Scheduled Tasks Routes (should be protected or called via CLI)
$routes->group('cron', function ($routes) {
    $routes->get('generate-rent-payments', 'Cron::generateRentPayments');
    $routes->get('mark-overdue-payments', 'Cron::markOverduePayments');
    $routes->get('expire-leases', 'Cron::expireLeases');
    $routes->get('send-lease-expiry-reminders', 'Cron::sendLeaseExpiryReminders');
    $routes->get('backup-database', 'Cron::backupDatabase');
});

// Public Routes (no authentication required)
$routes->group('public', function ($routes) {
    $routes->get('property-search', 'Public::propertySearch');
    $routes->get('contact', 'Public::contact');
    $routes->post('contact/send', 'Public::sendContact');
});


// Error Pages
$routes->set404Override('Errors::show404');

// CLI Routes
$routes->cli('migrate', 'Cli\Migrate::run');
$routes->cli('seed', 'Cli\Seed::run');
$routes->cli('create-admin', 'Cli\Setup::createAdmin');

// Redirects for common mistyped URLs
$routes->addRedirect('admin', 'admin/dashboard');
$routes->addRedirect('landlord', 'landlord/dashboard');
$routes->addRedirect('maintenance', 'maintenance/dashboard');
$routes->addRedirect('login', 'auth/login');
$routes->addRedirect('logout', 'auth/logout');
