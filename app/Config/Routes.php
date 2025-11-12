<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->get('/', 'Frontend::index');
$routes->setDefaultController('Frontend');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(true);

// Frontend routes
$routes->get('frontend', 'Frontend::index');
$routes->get('frontend/about', 'Frontend::about');
$routes->get('frontend/contact', 'Frontend::contact');
$routes->get('frontend/item/(:num)', 'Frontend::item/$1');
$routes->get('frontend/catalog', 'Frontend_Catalog::index');

$routes->get('catalog', 'Frontend_Catalog::index'); // Alias for catalog
$routes->get('location', 'Frontend_Location::index'); // Alias for frontend/location
$routes->get('check-warranty', 'Frontend_Garansi::index'); // Warranty check page
$routes->get('cek-garansi', 'Frontend_Garansi::index'); // Warranty check page (alias)
$routes->post('cek-garansi', 'Frontend_Garansi::check'); // Warranty check process

// Cart and Checkout routes
$routes->get('cart', 'Checkout::cart'); // Cart page
$routes->get('checkout', 'Checkout::index'); // Checkout page
$routes->post('checkout/process', 'Checkout::process'); // Process checkout


// Backend routes are defined below. Add your backend or admin panel routes here.
# Category
$routes->get('item-category', 'Item_Category::index');
$routes->get('item-category/add', 'Item_Category::add');
$routes->get('item-category/edit', 'Item_Category::edit');
$routes->match(['get', 'post'], 'item-category/delete', 'Item_Category::delete');
$routes->match(['get', 'post'], 'item-category/getDataDT', 'Item_Category::getDataDT');
$routes->match(['get', 'post'], 'item-category/toggleStatus', 'Item_Category::toggleStatus');
$routes->post('item-category/store', 'Item_Category::store');
$routes->post('item-category/update/(:num)', 'Item_Category::update/$1');
$routes->post('item-category/delete/(:num)', 'Item_Category::delete/$1');

# Brand
$routes->get('item-brand', 'Item_Brand::index');
$routes->get('item-brand/add', 'Item_Brand::add');
$routes->get('item-brand/edit', 'Item_Brand::edit');
$routes->match(['get', 'post'], 'item-brand/delete', 'Item_Brand::delete');
$routes->match(['get', 'post'], 'item-brand/getDataDT', 'Item_Brand::getDataDT');
$routes->match(['get', 'post'], 'item-brand/toggleStatus', 'Item_Brand::toggleStatus');
$routes->post('item-brand/store', 'Item_Brand::store');
$routes->post('item-brand/update/(:num)', 'Item_Brand::update/$1');
$routes->post('item-brand/delete/(:num)', 'Item_Brand::delete/$1');

# Spec
$routes->get('item-spec', 'Item_Spec::index');
$routes->get('item-spec/add', 'Item_Spec::add');
$routes->get('item-spec/edit', 'Item_Spec::edit');
$routes->match(['get', 'post'], 'item-spec/delete', 'Item_Spec::delete');
$routes->match(['get', 'post'], 'item-spec/getDataDT', 'Item_Spec::getDataDT');
$routes->match(['get', 'post'], 'item-spec/toggleStatus', 'Item_Spec::toggleStatus');
$routes->post('item-spec/store', 'Item_Spec::store');
$routes->post('item-spec/update/(:num)', 'Item_Spec::update/$1');
$routes->post('item-spec/delete/(:num)', 'Item_Spec::delete/$1');

# Item Agent
$routes->get('item-agent', 'Item_Agent::index');
$routes->get('item-agent/add', 'Item_Agent::add');
$routes->get('item-agent/edit', 'Item_Agent::edit');
$routes->match(['get', 'post'], 'item-agent/delete', 'Item_Agent::delete');
$routes->match(['get', 'post'], 'item-agent/getDataDT', 'Item_Agent::getDataDT');
$routes->match(['get', 'post'], 'item-agent/toggleStatus', 'Item_Agent::toggleStatus');
$routes->post('item-agent/store', 'Item_Agent::store');
$routes->post('item-agent/update/(:num)', 'Item_Agent::update/$1');
$routes->post('item-agent/delete/(:num)', 'Item_Agent::delete/$1');

# Agent Rules
$routes->get('agent-rules', 'AgentRules::index');
$routes->get('agent-rules/form', 'AgentRules::form');
$routes->get('agent-rules/form/(:num)', 'AgentRules::form/$1');
$routes->post('agent-rules/save', 'AgentRules::save');
$routes->post('agent-rules/delete/(:num)', 'AgentRules::delete/$1');

/* Bagian agen */
$routes->group('agent', function($routes) {
    // Agent Dashboard
    $routes->get('dashboard', 'Agent\Dashboard::index');
    $routes->get('dashboard/ajaxGetPenjualan', 'Agent\Dashboard::ajaxGetPenjualan');
    $routes->get('dashboard/ajaxGetItemTerjual', 'Agent\Dashboard::ajaxGetItemTerjual');
    $routes->get('dashboard/ajaxGetKategoriTerjual', 'Agent\Dashboard::ajaxGetKategoriTerjual');
    $routes->get('dashboard/ajaxGetPenjualanTerbaru', 'Agent\Dashboard::ajaxGetPenjualanTerbaru');
    $routes->get('dashboard/ajaxGetPelangganTerbesar', 'Agent\Dashboard::ajaxGetPelangganTerbesar');
    $routes->post('dashboard/getDataDTPenjualanTerbesar', 'Agent\Dashboard::getDataDTPenjualanTerbesar');

    // Agent Item (POS View)
    $routes->get('item', 'Agent\Item::index');

    // Agent Sales (Cart & Checkout)
    $routes->get('sales', 'Agent\Sales::index');
    $routes->get('sales/(:num)', 'Agent\Sales::detail/$1');
    $routes->get('sales/cart', 'Agent\Sales::cart');
    $routes->get('sales/sn', 'Agent\Sales::sn');
    $routes->get('sales/sn/activate/(:num)', 'Agent\Sales::activateForm/$1');
    $routes->post('sales/getSnDataDT', 'Agent\Sales::getSnDataDT');
    $routes->post('sales/getSnData/(:num)', 'Agent\Sales::getSnData/$1');
    $routes->post('sales/activateSN/(:num)', 'Agent\Sales::activateSN/$1');
    $routes->post('sales/store', 'Agent\Sales::store');
    $routes->post('sales/addToCart', 'Agent\Sales::addToCart');
    $routes->post('sales/updateCart', 'Agent\Sales::updateCart');
    $routes->post('sales/removeFromCart', 'Agent\Sales::removeFromCart');
    $routes->get('sales/clearCart', 'Agent\Sales::clearCart');
    $routes->post('sales/getDataDT', 'Agent\Sales::getDataDT');    
    
    // Agent Payment Result Pages
    $routes->get('payment/thankyou', 'Agent\Payment::thankyou');
    $routes->get('payment/status', 'Agent\Payment::status');
    $routes->get('payment/not-found', 'Agent\Payment::notFound');

    // Agent Sales Confirm (Admin verification of agent orders)
    $routes->get('sales/confirm', 'Agent\SalesConfirm::index');
    $routes->get('sales/confirm/(:num)', 'Agent\SalesConfirm::detail/$1');
    $routes->post('sales/confirm/getDataDT', 'Agent\SalesConfirm::getDataDT');
    $routes->post('sales/confirm/verify/(:num)', 'Agent\SalesConfirm::verify/$1');
    $routes->post('sales/confirm/assignSN/(:num)', 'Agent\SalesConfirm::assignSN/$1');
});

# Agent Gateway Check (for agent/post integration)
$routes->match(['get', 'post'], 'agent/checkActiveGateway', 'Agent::checkActiveGateway');
$routes->match(['get', 'post'], 'agent/getGatewayByCode', 'Agent::getGatewayByCode');
$routes->get('agent/getGatewayByCode/(:any)', 'Agent::getGatewayByCode/$1');

# Agent Password Update
$routes->post('agent/updateUserPassword', 'Agent::updateUserPassword');

# Product Promo (moved to Item controller)
$routes->get('item/promoList/(:num)', 'Item::promoList/$1');
$routes->post('item/promoStore', 'Item::promoStore');
$routes->post('item/promoDelete/(:num)', 'Item::promoDelete/$1');

# Product Rule (Item controller)
$routes->post('item/saveProductRule/(:num)', 'Item::saveProductRule/$1');

# Item Agent helper API
$routes->get('item-agent/list-by-item/(:num)', 'Item_Agent::listByItem/$1');

# Item SN (Serial Number) Management
$routes->get('item-sn/(:num)', 'Item_Sn::index/$1');
$routes->post('item-sn/store', 'Item_Sn::store');
$routes->post('item-sn/importExcel', 'Item_Sn::importExcel');
$routes->get('item-sn/downloadTemplate', 'Item_Sn::downloadTemplate');
$routes->post('item-sn/getSnList', 'Item_Sn::getSnList');
$routes->get('item-sn/delete/(:num)', 'Item_Sn::delete/$1');

# Item Variant Management
$routes->get('item-varian/(:num)', 'Item_Varian::index/$1');
$routes->get('item-varian/add/(:num)', 'Item_Varian::add/$1');
$routes->post('item-varian/store', 'Item_Varian::store');
$routes->get('item-varian/delete/(:num)', 'Item_Varian::delete/$1');
$routes->get('item-varian/getByItem/(:num)', 'Item_Varian::getByItem/$1');

# Sales Management

# Platform Management
# Specific routes must come before parameterized routes
$routes->post('platform/store', 'Platform::store');
$routes->post('platform/getDataDT', 'Platform::getDataDT');
$routes->get('platform/add', 'Platform::add');
$routes->get('platform/edit/(:num)', 'Platform::edit/$1');
$routes->post('platform/update/(:num)', 'Platform::update/$1');
$routes->get('platform/delete/(:num)', 'Platform::delete/$1');
$routes->match(['get', 'post'], 'platform/checkActiveGateway', 'Platform::checkActiveGateway');
$routes->match(['get', 'post'], 'platform/getGatewayByCode', 'Platform::getGatewayByCode');
$routes->get('platform/getGatewayByCode/(:any)', 'Platform::getGatewayByCode/$1');
$routes->get('platform', 'Platform::index');

# Sales Management
$routes->group('sales',  function($routes){
    $routes->get('/', 'Sales::index');
    $routes->get('add', 'Sales::create');
    $routes->post('store', 'Sales::store');
    $routes->get('(:num)', 'Sales::detail/$1');
    $routes->post('getDataDT', 'Sales::getDataDT');
    $routes->get('getUnusedSNs', 'Sales::getUnusedSNs');
    $routes->get('print_dm/(:num)', 'Sales::print_dm/$1');
});

# Payment Gateway Callback (No authentication required - called by Midtrans)
$routes->match(['get', 'post'], 'api/sales/callback', 'Api\Sales::callback');




# Migration Runner (Web-based migration tool)
$routes->get('migrate/run', 'MigrationController::run');
$routes->get('migrate/rollback', 'MigrationController::rollback');

// Utility routes (for system setup - no authentication required)
$routes->get('util', 'Util::index'); // Utility controller for module/role injection
/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
/* $routes->get('/', 'Home::index');
$routes->setTranslateURIDashes(true);
 */
 
/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
