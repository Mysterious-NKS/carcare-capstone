<?php
// safety requires (autoload sometimes misses these in your setup)
require_once dirname(__DIR__) . '/app/controllers/VehicleController.php';
require_once dirname(__DIR__) . '/app/controllers/AppointmentController.php';

// ── Public/Auth
$router->get('/',               [PublicController::class, 'home']);
$router->get('/login',          [AuthController::class, 'showLogin']);
$router->post('/login',         [AuthController::class, 'login']);
$router->get('/register',       [AuthController::class, 'showRegister']);
$router->post('/register',      [AuthController::class, 'register']);
$router->get('/logout',         [AuthController::class, 'logout']);

// ── Customer dashboard
$router->get('/dashboard',      [CustomerController::class, 'dashboard']);

// ── Appointments (MUST be above dispatch and with controller required)
$router->get('/appointments',         [AppointmentController::class, 'index']);
$router->get('/appointments/create',  [AppointmentController::class, 'create']);
$router->post('/appointments',        [AppointmentController::class, 'store']);

// (optional) support both paths if you ever link /customer/appointments
$router->get('/customer/appointments',        [AppointmentController::class, 'index']);
$router->get('/customer/appointments/create', [AppointmentController::class, 'create']);

// ── Vehicles
$router->get('/customer/vehicles',         [VehicleController::class, 'index']);
$router->get('/customer/vehicles/add',     [VehicleController::class, 'create']);
$router->post('/customer/vehicles',        [VehicleController::class, 'store']);
$router->get('/customer/vehicles/edit',    [VehicleController::class, 'edit']);    // ?id=123
$router->post('/customer/vehicles/edit',   [VehicleController::class, 'update']);  // ?id=123
$router->post('/customer/vehicles/delete', [VehicleController::class, 'destroy']); // ?id=123

$router->get('/appointments/view',   [AppointmentController::class, 'show']);   // ?id=123
$router->post('/appointments/cancel',[AppointmentController::class, 'cancel']); // ?id=123
