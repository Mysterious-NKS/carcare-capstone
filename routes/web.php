<?php
// ─────────────────────────────────────────────────────────────────────────────
// Safety requires (autoload sometimes misses these in your setup)
// ─────────────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/app/controllers/PublicController.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/controllers/CustomerController.php';
require_once dirname(__DIR__) . '/app/controllers/VehicleController.php';
require_once dirname(__DIR__) . '/app/controllers/AppointmentController.php';
require_once dirname(__DIR__) . '/app/controllers/NotificationController.php';
require_once dirname(__DIR__) . '/app/controllers/ProfileController.php';
require_once dirname(__DIR__) . '/app/controllers/RatingController.php';
require_once dirname(__DIR__) . '/app/controllers/HistoryController.php';
require_once dirname(__DIR__) . '/app/controllers/StaffController.php';
require_once dirname(__DIR__) . '/app/controllers/ServiceRecordController.php';

// ─────────────────────────────────────────────────────────────────────────────
// Public / Auth
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/',               [PublicController::class, 'home']);

$router->get('/login',          [AuthController::class, 'showLogin']);
$router->post('/login',         [AuthController::class, 'login']);

$router->get('/register',       [AuthController::class, 'showRegister']);
$router->post('/register',      [AuthController::class, 'register']);
$router->get('/logout',         [AuthController::class, 'logout']);

// Hidden / easter egg registration routes
// These load views from app/views/auth/register_staff.php and register_admin.php
$router->get('/register/staff',  [AuthController::class, 'showRegisterStaff']);
$router->post('/register/staff', [AuthController::class, 'registerStaff']);
$router->get('/register/admin',  [AuthController::class, 'showRegisterAdmin']);
$router->post('/register/admin', [AuthController::class, 'registerAdmin']);

// ─────────────────────────────────────────────────────────────────────────────
// Customer dashboard
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/dashboard',      [CustomerController::class, 'dashboard']);

// ─────────────────────────────────────────────────────────────────────────────
// Appointments (Customer)
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/appointments',                  [AppointmentController::class, 'index']);
$router->get('/appointments/create',           [AppointmentController::class, 'create']);
$router->post('/appointments',                 [AppointmentController::class, 'store']);
$router->post('/appointments/create',          [AppointmentController::class, 'store']); // legacy
$router->get('/appointments/show',             [AppointmentController::class, 'show']); // ?id=123
$router->get('/appointments/view',             [AppointmentController::class, 'view']); // ?id=123
$router->post('/appointments/cancel',          [AppointmentController::class, 'cancel']);

// NOTE: specific path BEFORE the catch-all {id}
$router->get('/appointments/{id}/reschedule',  [AppointmentController::class, 'rescheduleForm']);
$router->post('/appointments/{id}/reschedule', [AppointmentController::class, 'rescheduleSave']);

// Catch-all pretty URL for a single appointment
$router->get('/appointments/{id}',             [AppointmentController::class, 'showById']);

// ─────────────────────────────────────────────────────────────────────────────
// Vehicles (Customer)
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/customer/vehicles',         [VehicleController::class, 'index']);
$router->get('/customer/vehicles/add',     [VehicleController::class, 'create']);
$router->post('/customer/vehicles',        [VehicleController::class, 'store']);
$router->get('/customer/vehicles/edit',    [VehicleController::class, 'edit']);    // ?id=123
$router->post('/customer/vehicles/edit',   [VehicleController::class, 'update']);  // ?id=123
$router->post('/customer/vehicles/delete', [VehicleController::class, 'destroy']); // ?id=123

// Optional legacy paths
$router->get('/customer/appointments',        [AppointmentController::class, 'index']);
$router->get('/customer/appointments/create', [AppointmentController::class, 'create']);

// ─────────────────────────────────────────────────────────────────────────────
// Static pages
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/about',   [PublicController::class, 'about']);
$router->get('/contact', [PublicController::class, 'contact']);

// ─────────────────────────────────────────────────────────────────────────────
// Notifications, Profile, Feedback
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/notifications',                 [NotificationController::class, 'index']);
$router->post('/notifications/mark-read',      [NotificationController::class, 'markRead']);
$router->post('/notifications/custom',         [NotificationController::class, 'custom']); // staff

$router->get('/profile',                       [ProfileController::class, 'show']);
$router->post('/profile',                      [ProfileController::class, 'update']);
$router->post('/profile/password',             [ProfileController::class, 'changePassword']);

$router->get('/feedback',                      [RatingController::class, 'index']);
$router->post('/feedback',                     [RatingController::class, 'store']);

// ─────────────────────────────────────────────────────────────────────────────
// Service History (Phase 4)
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/history',                       [HistoryController::class, 'index']);
$router->get('/history/detail',                [HistoryController::class, 'detail']); // ?id=rec_id

// ─────────────────────────────────────────────────────────────────────────────
// STAFF area (Phase 5)
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/staff',                         [StaffController::class, 'dashboard']);
$router->get('/staff/interactions',            [StaffController::class, 'interactions']);
$router->get('/staff/workflow',                [StaffController::class, 'workflow']);
$router->get('/staff/schedule',                [StaffController::class, 'schedule']);

// Staff actions
$router->post('/appointments/{id}/status',            [AppointmentController::class, 'updateStatus']);
$router->post('/appointments/{id}/staff-reschedule',  [AppointmentController::class, 'staffRescheduleSave']);
$router->post('/service-records/save',                [ServiceRecordController::class, 'save']);
