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
require_once dirname(__DIR__) . '/app/controllers/AdminController.php';

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

$router->get('/appointments/{id}/reschedule',  [AppointmentController::class, 'rescheduleForm']);
$router->post('/appointments/{id}/reschedule', [AppointmentController::class, 'rescheduleSave']);
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

$router->post('/appointments/{id}/status',            [AppointmentController::class, 'updateStatus']);
$router->post('/appointments/{id}/staff-reschedule',  [AppointmentController::class, 'staffRescheduleSave']);
$router->post('/service-records/save',                [ServiceRecordController::class, 'save']);

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN (Phase 6+)
// ─────────────────────────────────────────────────────────────────────────────
$router->get('/admin',                    [AdminController::class, 'dashboard']);
$router->get('/admin/administration',     [AdminController::class, 'administration']);

// Reporting
$router->get('/admin/reports',            [AdminController::class, 'reports']);
$router->get('/admin/reports/export/csv', [AdminController::class, 'reportsExportCsv']);
$router->get('/admin/reports/print',      [AdminController::class, 'reportsPrint']);
$router->get('/admin/reports/export/pdf', [AdminController::class, 'reportsExportPdf']);

// Analytics
$router->get('/admin/analytics',                [AdminController::class, 'analytics']);
$router->get('/admin/analytics/export/pdf',     [AdminController::class, 'analyticsExportPdf']);

// Ops quick actions
$router->post('/admin/appointments/{id}/assign-staff', [AdminController::class, 'assignStaff']);
$router->post('/admin/appointments/{id}/status',       [AdminController::class, 'changeStatus']);

// Administration: Vehicles
$router->post('/admin/vehicles',                       [AdminController::class, 'vehiclesStore']);
$router->post('/admin/vehicles/{id}/update',           [AdminController::class, 'vehiclesUpdate']);
$router->post('/admin/vehicles/{id}/delete',           [AdminController::class, 'vehiclesDelete']);

// Administration: Users
$router->post('/admin/users',                          [AdminController::class, 'usersStore']);
$router->post('/admin/users/{id}/update',              [AdminController::class, 'usersUpdate']);
$router->post('/admin/users/{id}/toggle',              [AdminController::class, 'usersToggle']); // lock/unlock

// Administration: Customer Interactions (new)
$router->get('/admin/interactions',                    [AdminController::class, 'interactions']);
$router->post('/admin/interactions/feedback/{id}/delete', [AdminController::class, 'deleteFeedback']);
$router->post('/admin/interactions/reply/{id}/delete',    [AdminController::class, 'deleteReply']);
