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

    $routes->get('register', 'PublicAuth::register');
    $routes->post('register', 'PublicAuth::attemptRegister');
});

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

// Landlord Routes 
$routes->group('landlord', ['namespace' => 'App\Controllers'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Landlord::dashboard');
    $routes->get('dashboard', 'Landlord::dashboard');

    // Properties
    $routes->get('properties', 'Landlord::properties');
    $routes->get('properties/view/(:num)', 'Landlord::viewProperty/$1');
    $routes->post('properties/update/(:num)', 'Landlord::updateProperty/$1');

    // Add property routes (both patterns for flexibility)
    $routes->get('request-property', 'Landlord::requestProperty');
    $routes->get('properties/add', 'Landlord::requestProperty'); // Add this line
    $routes->post('add-property', 'Landlord::addProperty');
    $routes->post('properties/add', 'Landlord::addProperty'); // Add this line

    // Tenants
    $routes->get('tenants', 'Landlord::tenants');
    $routes->get('tenants/view/(:num)', 'Landlord::viewTenant/$1');
    $routes->get('tenants/details/(:num)', 'Landlord::getTenantDetails/$1');
    $routes->post('tenants/add', 'Landlord::addTenant');
    $routes->post('tenants/send-message', 'Landlord::sendMessage');
    $routes->post('tenants/renew-lease', 'Landlord::renewLease');
    $routes->post('tenants/terminate/(:num)', 'Landlord::terminateLease/$1');
    $routes->get('tenants/get-lease-info/(:num)', 'Landlord::getLeaseInfo/$1');
    $routes->get('tenants/export', 'Landlord::exportTenants');

    // Payments
    $routes->get('payments', 'Landlord::payments');
    $routes->get('payments/details/(:num)', 'Landlord::getPaymentDetails/$1');
    $routes->post('payments/mark-paid', 'Landlord::markPaymentAsPaid');
    $routes->get('payments/receipt/(:num)', 'Landlord::downloadReceipt/$1');
    $routes->get('payments/export', 'Landlord::exportPayments');

    // Maintenance
    $routes->get('maintenance', 'Landlord::maintenance');
    $routes->get('maintenance/details/(:num)', 'Landlord::getMaintenanceDetails/$1');
    $routes->post('maintenance/create', 'Landlord::createMaintenanceRequest');
    $routes->post('maintenance/approve/(:num)', 'Landlord::approveMaintenance/$1');
    $routes->post('maintenance/reject/(:num)', 'Landlord::rejectMaintenance/$1');
    $routes->post('maintenance/update-progress/(:num)', 'Landlord::updateMaintenanceProgress/$1');
    $routes->get('maintenance/export', 'Landlord::exportMaintenance');

    // Reports routes
    $routes->get('reports', 'Reports::index');
    $routes->post('reports/generate-pdf', 'Reports::generatePdf');
    $routes->post('reports/chart-data', 'Reports::getChartData');
    $routes->delete('reports/delete/(:num)', 'Reports::delete/$1');
    $routes->get('reports/download/(:num)', 'Reports::download/$1');

    // Profile routes
    $routes->get('profile', 'Landlord\Profile::index');
    $routes->post('profile/update', 'Landlord\Profile::update');
    $routes->post('profile/change-password', 'Landlord\Profile::changePassword');

    // Help & Support
    $routes->get('help', 'Landlord::help');

    // Income Reports (legacy routes)
    $routes->get('income-report', 'Landlord::incomeReport');

    // Contact admin - FIXED (removed duplicate landlord/)
    $routes->post('send-admin-message', 'Landlord::sendAdminMessage');

    // Payment verification pages
    $routes->get('payment-verification', 'PaymentVerification::index');
    $routes->get('payments', 'PaymentVerification::index'); // Alternative URL

    // Payment verification actions
    $routes->post('verify-receipt/(:num)', 'PaymentVerification::verifyReceipt/$1');
    $routes->post('reject-receipt/(:num)', 'PaymentVerification::rejectReceipt/$1');
    $routes->get('download-receipt/(:num)', 'PaymentVerification::downloadReceipt/$1');
});



// Tenant Routes
$routes->group('tenant', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Tenant::dashboard');

    // Lease
    $routes->get('lease', 'Tenant::lease');

    // Profile routes
    $routes->get('profile', 'Tenant::profile');
    $routes->post('profile/update', 'Tenant::updateProfile');
    $routes->post('profile/change-password', 'Tenant::changePassword');

    // Payments
    $routes->get('payments', 'Tenant::payments');
    $routes->get('payments/make', 'Tenant::makePayment');
    $routes->post('payments/process', 'Tenant::processPayment');
    $routes->get('payments/receipt/(:num)', 'Tenant::paymentReceipt/$1');

    // Maintenance
    $routes->get('maintenance', 'Tenant::maintenance');
    $routes->get('maintenance/create', 'Tenant::createMaintenance');
    $routes->post('maintenance/store', 'Tenant::storeMaintenance');
    $routes->get('maintenance/view/(:num)', 'Tenant::viewMaintenance/$1');
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
$routes->addRedirect('tenant', 'tenant/dashboard');
$routes->addRedirect('maintenance', 'maintenance/dashboard');
$routes->addRedirect('login', 'auth/login');
$routes->addRedirect('logout', 'auth/logout');

$routes->group('tenant', function ($routes) {
    $routes->get('profile', 'Tenant\Profile::index');
    $routes->post('profile/update', 'Tenant\Profile::update');
});

$routes->group('tenant', function ($routes) {
    $routes->get('profile', 'Tenant\Profile::index');
    $routes->post('profile/update', 'Tenant\Profile::update');
    $routes->get('profile/test', 'Tenant\Profile::test');
});
