<?php

class AdminController extends Controller
{
    /** quick helpers like other controllers */
    private function pdo(): PDO { return DB::pdo(); }
    private function uid(): int  { return (int)Auth::id(); }

  private function tableExists(PDO $pdo, string $table): bool {
    try {
        // Fast and permission-friendly: if table doesn’t exist, this throws.
        $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1");
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

private function colExists(PDO $pdo, string $table, string $col): bool {
    try {
        $st = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $st->execute([$col]);
        return (bool)$st->fetch(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        return false;
    }
}




    private function adminName(PDO $pdo): string {
        $fallback = 'Admin';
        if (!$this->tableExists($pdo, 'users')) return $fallback;

        $parts = [];
        if ($this->colExists($pdo, 'users', 'full_name')) $parts[] = "NULLIF(full_name,'')";
        if ($this->colExists($pdo, 'users', 'name'))      $parts[] = "NULLIF(name,'')";
        if ($this->colExists($pdo, 'users', 'username'))  $parts[] = "NULLIF(username,'')";

        $coalesce = 'email';
        if (!empty($parts)) $coalesce = implode(',', $parts) . ', email';

        $sql = "SELECT COALESCE($coalesce) AS display FROM users WHERE id = ? LIMIT 1";
        $st  = $pdo->prepare($sql);
        $st->execute([$this->uid()]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return trim((string)($row['display'] ?? '')) ?: $fallback;
    }

    // ADD directly below adminName()
private function apptRange(): array|null {
    // appts = all | today | 7d
    $mode = strtolower(trim($_GET['appts'] ?? 'all'));
    if ($mode === 'today') {
        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');
        return [$start, $end, 'today'];
    }
    if ($mode === '7d' || $mode === '7days' || $mode === 'last7') {
        $start = date('Y-m-d 00:00:00', strtotime('-6 days')); // inclusive of today
        $end   = date('Y-m-d 23:59:59');
        return [$start, $end, '7d'];
    }
    // default = all (no date bound)
    return null;
}

    // Helper: attempt to fetch a staff reply for a given appointment id
    private function findFeedbackReply(PDO $pdo, int $appointmentId): ?string {
        if (!$this->tableExists($pdo, 'notifications')) return null;

        $hasType = $this->colExists($pdo,'notifications','type');
        $hasBody = $this->colExists($pdo,'notifications','body');
        $hasAppt = $this->colExists($pdo,'notifications','appointment_id');

        // Preferred: direct column join
        if ($hasType && $hasAppt) {
            $sql = "SELECT ".($hasBody ? "body" : "title")." AS msg
                    FROM notifications
                    WHERE appointment_id = ? AND type = 'FEEDBACK_REPLY'
                    ORDER BY created_at DESC, id DESC
                    LIMIT 1";
            $st = $pdo->prepare($sql);
            $st->execute([$appointmentId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row['msg'] ?? null;
        }

        // Fallback: pattern in title like "...appointment #16"
        if ($hasType) {
            $sql = "SELECT ".($hasBody ? "body" : "title")." AS msg
                    FROM notifications
                    WHERE type = 'FEEDBACK_REPLY' AND title LIKE ?
                    ORDER BY created_at DESC, id DESC
                    LIMIT 1";
            $st = $pdo->prepare($sql);
            $st->execute(['%#'.$appointmentId.'%']);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row['msg'] ?? null;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // OPS DASHBOARD
    // ─────────────────────────────────────────────────────────────────────
    // REPLACE AdminController::dashboard() with this
// REPLACE AdminController::dashboard() with this
public function dashboard()
{
    Auth::requireRole('ADMIN');
    $pdo = $this->pdo();
    $adminName = $this->adminName($pdo);

    // KPIs (today-based for quick glance)
    $metrics = ['today'=>0,'active'=>0,'vehicles'=>0,'urgent'=>0];
    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd   = date('Y-m-d 23:59:59');

    if ($this->tableExists($pdo, 'appointments')) {
        $c = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE scheduled_at BETWEEN ? AND ?");
        $c->execute([$todayStart, $todayEnd]);
        $metrics['today'] = (int)$c->fetchColumn();

        $cu = $pdo->prepare("SELECT COUNT(*) FROM appointments
                             WHERE scheduled_at BETWEEN ? AND ?
                               AND status IN ('DELAYED','PENDING')");
        $cu->execute([$todayStart, $todayEnd]);
        $metrics['urgent'] = (int)$cu->fetchColumn();
    }

    if ($this->tableExists($pdo, 'users') && $this->colExists($pdo,'users','role')) {
        $c = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('CUSTOMER','STAFF','ADMIN')");
        $metrics['active'] = (int)$c->fetchColumn();
    }

    if ($this->tableExists($pdo, 'vehicles')) {
        $c = $pdo->query("SELECT COUNT(*) FROM vehicles");
        $metrics['vehicles'] = (int)$c->fetchColumn();
    }

    // ── Appointment Manager scope ──────────────────────────────────────────
    // scope=all (default) | today | week (last 7 days)
    $scope = strtolower(trim($_GET['scope'] ?? 'all'));
    if (!in_array($scope, ['all','today','week'], true)) $scope = 'all';

    $apptRows = [];
    if ($this->tableExists($pdo,'appointments')) {
        $sel = "
            SELECT a.id, a.status, a.scheduled_at,
                   s.name AS service_name,
                   v.year, v.make, v.model, v.plate_no
        ";
        $from = "
            FROM appointments a
            JOIN services  s ON s.id = a.service_id
            JOIN vehicles  v ON v.id = a.vehicle_id
        ";

        if ($this->colExists($pdo,'appointments','staff_id')) {
            if ($this->tableExists($pdo,'staff')) {
                $sel .= ", st.name AS staff_name";
                $from .= " LEFT JOIN staff st ON st.id = a.staff_id ";
            } elseif ($this->tableExists($pdo,'users')) {
                $nameCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
                         : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
                $sel .= ", u.$nameCol AS staff_name";
                $from .= " LEFT JOIN users u ON u.id = a.staff_id ";
            } else {
                $sel .= ", '' AS staff_name";
            }
        } else {
            $sel .= ", '' AS staff_name";
        }

        $where = ["1=1"]; $args = [];
        if ($scope === 'today') {
            $where[] = "a.scheduled_at BETWEEN ? AND ?"; $args[] = $todayStart; $args[] = $todayEnd;
        } elseif ($scope === 'week') {
            $weekStart = date('Y-m-d 00:00:00', strtotime('-6 days'));
            $where[] = "a.scheduled_at BETWEEN ? AND ?"; $args[] = $weekStart; $args[] = $todayEnd;
        }
        // Order newest first so recent items are on top; show more when All
        $limit = ($scope === 'all') ? 100 : 60;

        $sql = $sel.$from." WHERE ".implode(' AND ', $where)." ORDER BY a.scheduled_at DESC LIMIT $limit";
        $st  = $pdo->prepare($sql);
        $st->execute($args);
        $apptRows = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // System log / Feedback
    $systemLog = [];
    if ($this->tableExists($pdo, 'notifications')) {
        $select = "id, type, title, created_at";
        if ($this->colExists($pdo,'notifications','body')) $select = "id, type, title, body, created_at";
        $q = $pdo->query("SELECT $select FROM notifications ORDER BY created_at DESC LIMIT 6");
        $systemLog = $q->fetchAll(PDO::FETCH_ASSOC);
    }

    // Recent feedback + possible staff reply via notifications
    $recentFeedback = [];
    if ($this->tableExists($pdo, 'ratings')) {
        $hasAppt = $this->colExists($pdo,'ratings','appointment_id');
        $cols = "stars, comment, created_at";
        if ($hasAppt) $cols = "stars, comment, created_at, appointment_id";
        $q = $pdo->query("SELECT $cols FROM ratings ORDER BY created_at DESC LIMIT 5");
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $reply = null;
            if ($hasAppt && !empty($r['appointment_id'])) {
                $reply = $this->findFeedbackReply($pdo, (int)$r['appointment_id']);
            }
            $recentFeedback[] = [
                'stars'      => (int)($r['stars'] ?? 0),
                'comment'    => (string)($r['comment'] ?? ''),
                'created_at' => (string)($r['created_at'] ?? ''),
                'reply'      => $reply,
            ];
        }
    }

    $staffOptions = $this->staffOptions($pdo);

    $this->render('admin/index.php', [
        'adminName'         => $adminName,
        'metrics'           => $metrics,
        'systemLog'         => $systemLog,
        'recentFeedback'    => $recentFeedback,
        // keep the old variable name for compatibility with the view:
        'todayAppointments' => $apptRows,
        'staffOptions'      => $staffOptions,
        // pass the scope so the view can label the section
        'apptScope'         => $scope,
    ]);
}



    // ─────────────────────────────────────────────────────────────────────
    // ADMINISTRATION (lists)
    // ─────────────────────────────────────────────────────────────────────
    public function administration()
{
    Auth::requireRole('ADMIN');
    $pdo = $this->pdo();
    $adminName = $this->adminName($pdo);

    // ========= Vehicles: search + pagination =========
    $q    = trim((string)($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per  = 10;
    $args = [];
    $where = "WHERE 1=1";

    if ($q !== '') {
        $where .= " AND (v.plate_no LIKE ? OR v.make LIKE ? OR v.model LIKE ? OR v.id = ?)";
        $args[] = "%$q%";
        $args[] = "%$q%";
        $args[] = "%$q%";
        $args[] = (int)$q;
    }

    $vTotal = 0;
    if ($this->tableExists($pdo,'vehicles')) {
        $ct = $pdo->prepare("SELECT COUNT(*) FROM vehicles v $where");
        $ct->execute($args);
        $vTotal = (int)$ct->fetchColumn();
    }

    $vPages = max(1, (int)ceil($vTotal / $per));
    $page   = min($page, $vPages);
    $off    = ($page - 1) * $per;

    $vehicles = [];
    if ($this->tableExists($pdo,'vehicles')) {
        $sql = "SELECT v.id, v.user_id, v.year, v.make, v.model, v.plate_no
                FROM vehicles v
                $where
                ORDER BY v.id DESC
                LIMIT $per OFFSET $off";
        $st = $pdo->prepare($sql);
        $st->execute($args);
        $vehicles = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    $vehPager = [
        'q'        => $q,
        'page'     => $page,
        'pages'    => $vPages,
        'per'      => $per,
        'total'    => $vTotal,
        'has_prev' => $page > 1,
        'has_next' => $page < $vPages,
    ];

    // ========= Users: search + pagination =========
    $uq    = trim((string)($_GET['uq'] ?? ''));
    $upage = max(1, (int)($_GET['upage'] ?? 1));
    $uper  = 10;

    // ---- Users list (show status/locked)
// REPLACE the existing users query block with this:
// ---- Users list (shows live status + email/role)
$users = [];
if ($this->tableExists($pdo,'users')) {
    // best display column
    $displayCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
               : ( $this->colExists($pdo,'users','name') ? 'name' : 'email' );

    // include columns only if they exist
    $selEmail  = $this->colExists($pdo,'users','email')  ? ', email'  : '';
    $selRole   = $this->colExists($pdo,'users','role')   ? ', role'   : '';
    $selStatus = $this->colExists($pdo,'users','status') ? ', status' : '';
    $selLocked = $this->colExists($pdo,'users','is_locked') ? ', is_locked' : '';

    $sql = "SELECT id, $displayCol AS name$selEmail$selRole$selStatus$selLocked
            FROM users
            ORDER BY id DESC
            LIMIT 10";
    $st  = $pdo->prepare($sql);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // Normalize a 'status' value even on older schemas (derive from is_locked)
    foreach ($rows as $r) {
        $status = 'ACTIVE';
        if (array_key_exists('status', $r) && $r['status'] !== null && $r['status'] !== '') {
            $status = strtoupper((string)$r['status']);
        } elseif (array_key_exists('is_locked', $r)) {
            $status = ((int)$r['is_locked'] ? 'LOCKED' : 'ACTIVE');
        }
        $r['status'] = $status;
        $users[] = $r;
    }
}


    $usersPager = [
        'q'        => $uq,
        'page'     => 1,
        'pages'    => 1,
        'per'      => $uper,
        'total'    => 0,
        'has_prev' => false,
        'has_next' => false,
    ];

    if ($this->tableExists($pdo,'users')) {
        // choose display name column
        $displayCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
                    : ($this->colExists($pdo,'users','name') ? 'name' : 'email');

        $selEmail = $this->colExists($pdo,'users','email') ? ', email' : '';
        $selRole  = $this->colExists($pdo,'users','role')  ? ', role'  : '';

        // status columns (either explicit status or boolean is_locked)
        $selStatus = '';
        if ($this->colExists($pdo,'users','status')) {
            $selStatus = ', status';
        } elseif ($this->colExists($pdo,'users','is_locked')) {
            $selStatus = ', is_locked';
        }

        $uw = "WHERE 1=1";
        $uargs = [];
        if ($uq !== '') {
            $uw .= " AND ( $displayCol LIKE ? OR email LIKE ? OR id = ? )";
            $uargs[] = "%$uq%";
            $uargs[] = "%$uq%";
            $uargs[] = (int)$uq;
        }

        // count
        $uct = $pdo->prepare("SELECT COUNT(*) FROM users $uw");
        $uct->execute($uargs);
        $uTotal = (int)$uct->fetchColumn();

        $uPages = max(1, (int)ceil($uTotal / $uper));
        $upage  = min($upage, $uPages);
        $uoff   = ($upage - 1) * $uper;

        $usql = "SELECT id, $displayCol AS name$selEmail$selRole$selStatus
                 FROM users
                 $uw
                 ORDER BY id DESC
                 LIMIT $uper OFFSET $uoff";
        $ust  = $pdo->prepare($usql);
        $ust->execute($uargs);
        $users = $ust->fetchAll(PDO::FETCH_ASSOC);

        $usersPager = [
            'q'        => $uq,
            'page'     => $upage,
            'pages'    => $uPages,
            'per'      => $uper,
            'total'    => $uTotal,
            'has_prev' => $upage > 1,
            'has_next' => $upage < $uPages,
        ];
    }

    $this->render('admin/administration.php', [
        'adminName' => $adminName,
        'vehicles'  => $vehicles,
        'vehPager'  => $vehPager,
        'users'     => $users,
        'usersPager'=> $usersPager,
    ]);
}



    // ─────────────────────────────────────────────────────────────────────
    // REPORTS (UI)
    // ─────────────────────────────────────────────────────────────────────
    public function reports()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();

        $services = [];
        if ($this->tableExists($pdo,'services')) {
            $services = $pdo->query("SELECT id, name, price FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }

        $staff = [];
        if ($this->tableExists($pdo,'staff')) {
            $st = $pdo->query("SELECT id, name FROM staff ORDER BY name");
            $staff = $st->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($this->tableExists($pdo,'users') && $this->colExists($pdo,'users','role')) {
            $col = $this->colExists($pdo,'users','full_name') ? 'full_name' : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
            $st = $pdo->prepare("SELECT id, $col AS name FROM users WHERE role='STAFF' ORDER BY $col");
            $st->execute();
            $staff = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        $f = [
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to']   ?? ''),
            'status'     => trim($_GET['status']    ?? ''),
            'service_id' => (int)($_GET['service_id'] ?? 0),
            'staff_id'   => (int)($_GET['staff_id']   ?? 0),
            'plate'      => trim($_GET['plate']     ?? ''),
            'sort'       => trim($_GET['sort']      ?? 'date_desc'),
        ];

        $select = "
            SELECT
                a.id,
                a.status,
                a.scheduled_at,
                s.name  AS service_name,
                s.price AS service_price,
                v.year, v.make, v.model, v.plate_no
        ";
        $from = "
            FROM appointments a
            JOIN services  s ON s.id = a.service_id
            JOIN vehicles  v ON v.id = a.vehicle_id
        ";

        $staffColExists = $this->colExists($pdo,'appointments','staff_id');
        if ($staffColExists) {
            if ($this->tableExists($pdo,'staff')) {
                $select .= ", st.name AS staff_name";
                $from   .= " LEFT JOIN staff st ON st.id = a.staff_id ";
            } elseif ($this->tableExists($pdo,'users')) {
                $nameCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
                          : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
                $select .= ", u.$nameCol AS staff_name";
                $from   .= " LEFT JOIN users u ON u.id = a.staff_id ";
            } else {
                $select .= ", '' AS staff_name";
            }
        } else {
            $select .= ", '' AS staff_name";
        }

        $where = ["1=1"]; $args = [];
        if ($f['date_from'] !== '') { $where[] = "a.scheduled_at >= ?"; $args[] = $f['date_from']." 00:00:00"; }
        if ($f['date_to']   !== '') { $where[] = "a.scheduled_at <= ?"; $args[] = $f['date_to']." 23:59:59"; }
        if ($f['status']    !== '') { $where[] = "a.status = ?";      $args[] = $f['status']; }
        if ($f['service_id']> 0)    { $where[] = "a.service_id = ?";  $args[] = $f['service_id']; }
        if ($staffColExists && $f['staff_id']>0){ $where[]="a.staff_id=?"; $args[]=$f['staff_id']; }
        if ($f['plate']     !== '') { $where[] = "v.plate_no LIKE ?"; $args[] = "%".$f['plate']."%"; }

        $order = "a.scheduled_at DESC";
        switch ($f['sort']) {
            case 'date_asc':    $order = "a.scheduled_at ASC"; break;
            case 'price_asc':   $order = "s.price ASC, a.scheduled_at DESC"; break;
            case 'price_desc':  $order = "s.price DESC, a.scheduled_at DESC"; break;
            case 'status':      $order = "a.status ASC, a.scheduled_at DESC"; break;
            default:            $order = "a.scheduled_at DESC";
        }

        $sql = $select . $from . " WHERE " . implode(' AND ', $where) . " ORDER BY $order LIMIT 250";
        $st  = $pdo->prepare($sql);
        $st->execute($args);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $totalAppointments = 0;
        $totalRevenue      = 0.0;
        $byStatus          = [];
        foreach ($rows as $r) {
            $totalAppointments++;
            $totalRevenue += (float)($r['service_price'] ?? 0);
            $stKey = (string)$r['status'];
            $byStatus[$stKey] = ($byStatus[$stKey] ?? 0) + 1;
        }

        $statuses = [
            'PENDING','APPROVED','IN_PROGRESS','WAITING_PARTS',
            'COMPLETED','CANCELLED','REJECTED','DELAYED'
        ];

        $sectionTitle = 'reports';

        $this->render('admin/reports.php', [
            'filters'          => $f,
            'rows'             => $rows,
            'services'         => $services,
            'staff'            => $staff,
            'statuses'         => $statuses,
            'totalAppointments'=> $totalAppointments,
            'totalRevenue'     => $totalRevenue,
            'byStatus'         => $byStatus,
            'sectionTitle'     => $sectionTitle,
        ]);
    }

    /** shared report builder (CSV/Print/PDF) */
    private function buildReportData(PDO $pdo): array
    {
        $f = [
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to']   ?? ''),
            'status'     => trim($_GET['status']    ?? ''),
            'service_id' => (int)($_GET['service_id'] ?? 0),
            'staff_id'   => (int)($_GET['staff_id']   ?? 0),
            'plate'      => trim($_GET['plate']     ?? ''),
            'sort'       => trim($_GET['sort']      ?? 'date_desc'),
        ];

        $select = "
            SELECT
                a.id,
                a.status,
                a.scheduled_at,
                s.name  AS service_name,
                s.price AS service_price,
                v.year, v.make, v.model, v.plate_no
        ";
        $from = "
            FROM appointments a
            JOIN services  s ON s.id = a.service_id
            JOIN vehicles  v ON v.id = a.vehicle_id
        ";

        $staffColExists = $this->colExists($pdo,'appointments','staff_id');
        if ($staffColExists) {
            if ($this->tableExists($pdo,'staff')) {
                $select .= ", st.name AS staff_name";
                $from   .= " LEFT JOIN staff st ON st.id = a.staff_id ";
            } elseif ($this->tableExists($pdo,'users')) {
                $nameCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
                         : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
                $select .= ", u.$nameCol AS staff_name";
                $from   .= " LEFT JOIN users u ON u.id = a.staff_id ";
            } else {
                $select .= ", '' AS staff_name";
            }
        } else {
            $select .= ", '' AS staff_name";
        }

        $where = ["1=1"]; $args = [];
        if ($f['date_from'] !== '') { $where[] = "a.scheduled_at >= ?"; $args[] = $f['date_from']." 00:00:00"; }
        if ($f['date_to']   !== '') { $where[] = "a.scheduled_at <= ?"; $args[] = $f['date_to']." 23:59:59"; }
        if ($f['status']    !== '') { $where[] = "a.status = ?";      $args[] = $f['status']; }
        if ($f['service_id']> 0)    { $where[] = "a.service_id = ?";  $args[] = $f['service_id']; }
        if ($staffColExists && $f['staff_id']>0){ $where[]="a.staff_id=?"; $args[]=$f['staff_id']; }
        if ($f['plate']     !== '') { $where[] = "v.plate_no LIKE ?"; $args[] = "%".$f['plate']."%"; }

        $order = "a.scheduled_at DESC";
        switch ($f['sort']) {
            case 'date_asc':   $order = "a.scheduled_at ASC"; break;
            case 'price_asc':  $order = "s.price ASC, a.scheduled_at DESC"; break;
            case 'price_desc': $order = "s.price DESC, a.scheduled_at DESC"; break;
            case 'status':     $order = "a.status ASC, a.scheduled_at DESC"; break;
        }

        $sql = $select.$from." WHERE ".implode(' AND ',$where)." ORDER BY $order LIMIT 1000";
        $st  = $pdo->prepare($sql);
        $st->execute($args);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $sum = ['totalAppointments'=>0, 'totalRevenue'=>0.0];
        foreach ($rows as $r) {
            $sum['totalAppointments']++;
            $sum['totalRevenue'] += (float)($r['service_price'] ?? 0);
        }

        return [$rows, $f, $sum];
    }

    public function reportsExportCsv()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        [$rows, $f, $sum] = $this->buildReportData($pdo);

        $fname = 'report_'.date('Y-m-d_H-i').'.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$fname.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output','w');
        fprintf($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID','Date','Status','Service','Price','Vehicle','Plate','Staff']);

        foreach ($rows as $r) {
            $veh = trim(($r['year'] ?? '').' '.($r['make'] ?? '').' '.($r['model'] ?? ''));
            fputcsv($out, [
                (int)$r['id'],
                date('Y-m-d H:i', strtotime($r['scheduled_at'])),
                (string)$r['status'],
                (string)$r['service_name'],
                (float)$r['service_price'],
                $veh,
                (string)$r['plate_no'],
                (string)($r['staff_name'] ?? ''),
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Totals', '', '', '', number_format($sum['totalRevenue'],2), 'Appointments', $sum['totalAppointments']]);
        fclose($out);
        exit;
    }

    public function reportsPrint()
    {
        Auth::requireRole('ADMIN');

        $pdo = $this->pdo();
        [$rows, $filters, $sum] = $this->buildReportData($pdo);

        $this->render('admin/report_print.php', [
            'rows'   => $rows,
            'filters'=> $filters,
            'sum'    => $sum,
            'title'  => 'Report',
        ]);
    }

    public function reportsExportPdf()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        [$rows, $filters, $sum] = $this->buildReportData($pdo);

        // Build daily series for the chart (date -> {count, revenue})
        $daily = [];
        foreach ($rows as $r) {
            if (empty($r['scheduled_at'])) continue;
            $d = date('Y-m-d', strtotime($r['scheduled_at']));
            if (!isset($daily[$d])) $daily[$d] = ['count' => 0, 'revenue' => 0.0];
            $daily[$d]['count']   += 1;
            $daily[$d]['revenue'] += (float)($r['service_price'] ?? 0);
        }
        ksort($daily);

        if (!$daily) {
            $today = date('Y-m-d');
            $daily[$today] = ['count' => 0, 'revenue' => 0.0];
        }

        $labels   = array_keys($daily);
        $counts   = array_column($daily, 'count');
        $revenue  = array_map('floatval', array_column($daily, 'revenue'));

        // Chart.js config for QuickChart (rendered as PNG)
        $chartCfg = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Appointments',
                        'data' => $counts,
                        'tension' => 0.3,
                        'borderWidth' => 2,
                        'pointRadius' => 0,
                        'yAxisID' => 'y1',
                    ],
                    [
                        'label' => 'Revenue (RM)',
                        'data' => $revenue,
                        'tension' => 0.3,
                        'borderWidth' => 2,
                        'pointRadius' => 0,
                        'yAxisID' => 'y2',
                    ],
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales' => [
                    'y1' => ['type' => 'linear', 'position' => 'left',  'beginAtZero' => true],
                    'y2' => ['type' => 'linear', 'position' => 'right', 'beginAtZero' => true, 'grid' => ['drawOnChartArea' => false]],
                ],
            ],
        ];

        // Fetch PNG from QuickChart with cURL and embed as data URI
        $___chartDataUri = '';
        try {
            $qcUrl = 'https://quickchart.io/chart';
            $payload = http_build_query([
                'width'  => 1000,
                'height' => 380,
                'format' => 'png',
                'backgroundColor' => 'white',
                'c' => json_encode($chartCfg),
            ]);

            $ch = curl_init($qcUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
            ]);
            $png = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($png !== false && $http >= 200 && $http < 300) {
                $___chartDataUri = 'data:image/png;base64,' . base64_encode($png);
            }
            // If it failed, we just skip the chart; PDF will still render.
        } catch (\Throwable $e) {
            // swallow — chart is optional
        }

        // 1) Composer autoload (Dompdf)
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        // 2) Render the view to HTML; make chart available as $___chartDataUri
        $title = 'Report';
        ob_start();
        include view('admin/reports_pdf.php'); // this file will see $rows, $filters, $sum, $title, and $___chartDataUri
        $html = ob_get_clean();

        // 3) Dompdf setup
        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', true);     // fine even though we're using a data URI
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf\Dompdf($options);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 4) Stream to browser
        $fname = 'report_' . date('Y-m-d_H-i') . '.pdf';
        $dompdf->stream($fname, ['Attachment' => true]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ANALYTICS (page + PDF via SimplePdf)
    // ─────────────────────────────────────────────────────────────────────
    private function buildAnalytics(PDO $pdo): array
    {
        // dropdowns
        $services = [];
        if ($this->tableExists($pdo,'services')) {
            $services = $pdo->query("SELECT id, name, price FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
        $staff = [];
        if ($this->tableExists($pdo,'staff')) {
            $st = $pdo->query("SELECT id, name FROM staff ORDER BY name");
            $staff = $st->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($this->tableExists($pdo,'users') && $this->colExists($pdo,'users','role')) {
            $col = $this->colExists($pdo,'users','full_name') ? 'full_name' : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
            $st = $pdo->prepare("SELECT id, $col AS name FROM users WHERE role='STAFF' ORDER BY $col");
            $st->execute();
            $staff = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // filters
        $f = [
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to']   ?? ''),
            'status'     => trim($_GET['status']    ?? ''),
            'service_id' => (int)($_GET['service_id'] ?? 0),
            'staff_id'   => (int)($_GET['staff_id']   ?? 0),
            'plate'      => trim($_GET['plate']     ?? ''),
        ];

        // series by day
        $series = [];
        $kpis   = ['appointments'=>0,'revenue'=>0.0,'avg_price'=>0.0,'completion_rate'=>0.0];

        if ($this->tableExists($pdo,'appointments')) {
            $where = ["1=1"]; $args = [];
            if ($f['date_from'] !== '') { $where[] = "a.scheduled_at >= ?"; $args[] = $f['date_from']." 00:00:00"; }
            if ($f['date_to']   !== '') { $where[] = "a.scheduled_at <= ?"; $args[] = $f['date_to']." 23:59:59"; }
            if ($f['status']    !== '') { $where[] = "a.status = ?";      $args[] = $f['status']; }
            if ($f['service_id']> 0)    { $where[] = "a.service_id = ?";  $args[] = $f['service_id']; }
            if ($this->colExists($pdo,'appointments','staff_id') && $f['staff_id']>0){ $where[]="a.staff_id=?"; $args[]=$f['staff_id']; }
            if ($f['plate']     !== '') { $where[] = "v.plate_no LIKE ?"; $args[] = "%".$f['plate']."%"; }

            $sql = "
                SELECT
                    DATE(a.scheduled_at) AS d,
                    COUNT(*)             AS c,
                    SUM(s.price)         AS r,
                    SUM(a.status='COMPLETED') AS completed
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE ".implode(' AND ',$where)."
                GROUP BY DATE(a.scheduled_at)
                ORDER BY d ASC
                LIMIT 366
            ";
            $st = $pdo->prepare($sql); $st->execute($args);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            $totalC = 0; $totalR = 0.0; $totalCompleted = 0;
            foreach ($rows as $r) {
                $d = (string)$r['d'];
                $c = (int)$r['c'];
                $rrev = (float)$r['r'];
                $series[] = ['date'=>$d,'count'=>$c,'revenue'=>$rrev];
                $totalC += $c; $totalR += $rrev; $totalCompleted += (int)$r['completed'];
            }

            $kpis['appointments'] = $totalC;
            $kpis['revenue']      = $totalR;
            $kpis['avg_price']    = $totalC ? ($totalR / $totalC) : 0.0;
            $kpis['completion_rate'] = $totalC ? ($totalCompleted * 100.0 / $totalC) : 0.0;
        }

        $statuses = [
            'PENDING','APPROVED','IN_PROGRESS','WAITING_PARTS',
            'COMPLETED','CANCELLED','REJECTED','DELAYED'
        ];

        return [$f, $services, $staff, $statuses, $kpis, $series];
    }

    public function analytics()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();

        [$filters,$services,$staff,$statuses,$kpis,$series] = $this->buildAnalytics($pdo);

        $this->render('admin/analytics.php', [
            'filters'  => $filters,
            'services' => $services,
            'staff'    => $staff,
            'statuses' => $statuses,
            'kpis'     => $kpis,
            'series'   => $series,
        ]);
    }

    public function analyticsExportPdf()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        [$filters,$services,$staff,$statuses,$kpis,$series] = $this->buildAnalytics($pdo);

        // 1) Composer autoload (Dompdf)
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        // 2) Render the view to HTML
        $title = 'Analytics';
        ob_start();
        include view('admin/analytics_pdf.php');
        $html = ob_get_clean();

        // 3) Dompdf setup
        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf\Dompdf($options);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 4) Stream
        $fname = 'analytics_' . date('Y-m-d_H-i') . '.pdf';
        $dompdf->stream($fname, ['Attachment' => true]);
        exit;
    }

    /** tiny helper to draw a unicode sparkline without graphics libs */
    private function sparkline(array $values): string {
        if (empty($values)) return '';
        $blocks = ['▁','▂','▃','▄','▅','▆','▇','█'];
        $min = min($values); $max = max($values);
        if ($max == $min) return str_repeat($blocks[0], count($values));
        $out = '';
        foreach ($values as $v) {
            $i = (int)floor( ($v - $min) / max(1, ($max-$min)) * (count($blocks)-1) );
            $out .= $blocks[$i];
        }
        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────
    // OPS quick actions (Assign staff / Change status)
    // ─────────────────────────────────────────────────────────────────────
    private function staffOptions(PDO $pdo): array {
        if ($this->tableExists($pdo,'staff')) {
            return $pdo->query("SELECT id, name FROM staff ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($this->tableExists($pdo,'users') && $this->colExists($pdo,'users','role')) {
            $nameCol = $this->colExists($pdo,'users','full_name') ? 'full_name'
                    : ($this->colExists($pdo,'users','name') ? 'name' : 'email');
            $st = $pdo->prepare("SELECT id, $nameCol AS name FROM users WHERE role='STAFF' ORDER BY $nameCol");
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function assignStaff($id)
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();

        $id = (int)$id;
        $staffId = (int)($_POST['staff_id'] ?? 0);
        if ($id <= 0 || $staffId <= 0) { header('Location: '.url('admin')); exit; }
        if (!$this->tableExists($pdo,'appointments') || !$this->colExists($pdo,'appointments','staff_id')) {
            header('Location: '.url('admin')); exit;
        }

        $exists = false;
        if ($this->tableExists($pdo,'staff')) {
            $st = $pdo->prepare("SELECT 1 FROM staff WHERE id=? LIMIT 1");
            $st->execute([$staffId]); $exists = (bool)$st->fetchColumn();
        } elseif ($this->tableExists($pdo,'users') && $this->colExists($pdo,'users','role')) {
            $st = $pdo->prepare("SELECT 1 FROM users WHERE id=? AND role='STAFF' LIMIT 1");
            $st->execute([$staffId]); $exists = (bool)$st->fetchColumn();
        }
        if (!$exists) { header('Location: '.url('admin')); exit; }

        $u = $pdo->prepare("UPDATE appointments SET staff_id=? WHERE id=?");
        $u->execute([$staffId,$id]);

        header('Location: '.url('admin'));
        exit;
    }

    public function changeStatus($id)
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        $id = (int)$id;

        if ($id <= 0 || !$this->tableExists($pdo,'appointments') || !$this->colExists($pdo,'appointments','status')) {
            header('Location: '.url('admin')); exit;
        }

        $status = strtoupper(trim($_POST['status'] ?? ''));
        $allowed = ['PENDING','APPROVED','CONFIRMED','IN_PROGRESS','WAITING_PARTS','DELAYED','COMPLETED','CANCELLED','REJECTED'];
        if (!in_array($status, $allowed, true)) { header('Location: '.url('admin')); exit; }

        $u = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
        $u->execute([$status,$id]);

        header('Location: '.url('admin'));
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ADMINISTRATION: VEHICLES
    // ─────────────────────────────────────────────────────────────────────
    public function vehiclesStore()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        if (!$this->tableExists($pdo,'vehicles')) { header('Location: '.url('admin/administration')); exit; }

        $data = [
            'year'     => trim($_POST['year'] ?? ''),
            'make'     => trim($_POST['make'] ?? ''),
            'model'    => trim($_POST['model'] ?? ''),
            'plate_no' => trim($_POST['plate_no'] ?? ''),
        ];
        // owner can be user_id OR customer_id depending on schema
        $ownerId = (int)($_POST['user_id'] ?? 0);

        $cols = []; $vals = []; $args = [];
        foreach ($data as $k=>$v) {
            if ($v === '') continue;
            if ($this->colExists($pdo,'vehicles',$k)) { $cols[]=$k; $vals[]='?'; $args[]=$v; }
        }
        if ($ownerId > 0) {
            if ($this->colExists($pdo,'vehicles','user_id')) { $cols[]='user_id'; $vals[]='?'; $args[]=$ownerId; }
            elseif ($this->colExists($pdo,'vehicles','customer_id')) { $cols[]='customer_id'; $vals[]='?'; $args[]=$ownerId; }
        }
        if (empty($cols)) { header('Location: '.url('admin/administration')); exit; }

        $sql = "INSERT INTO vehicles (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
        $st  = $pdo->prepare($sql); $st->execute($args);

        header('Location: '.url('admin/administration'));
        exit;
    }

    public function vehiclesUpdate($id)
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo(); $id=(int)$id;
        if ($id<=0 || !$this->tableExists($pdo,'vehicles')) { header('Location: '.url('admin/administration')); exit; }

        $candidates = ['year','make','model','plate_no'];
        $sets = []; $args = [];
        foreach ($candidates as $c) {
            if (isset($_POST[$c]) && $this->colExists($pdo,'vehicles',$c)) {
                $sets[] = "$c = ?"; $args[] = trim($_POST[$c]);
            }
        }

        // optional owner change
        $ownerId = (int)($_POST['user_id'] ?? 0);
        if ($ownerId>0) {
            if ($this->colExists($pdo,'vehicles','user_id')) { $sets[]='user_id=?'; $args[]=$ownerId; }
            elseif ($this->colExists($pdo,'vehicles','customer_id')) { $sets[]='customer_id=?'; $args[]=$ownerId; }
        }

        if (!empty($sets)) {
            $args[] = $id;
            $sql = "UPDATE vehicles SET ".implode(',',$sets)." WHERE id=?";
            $st  = $pdo->prepare($sql); $st->execute($args);
        }
        header('Location: '.url('admin/administration')); exit;
    }

    public function vehiclesDelete($id)
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo(); $id=(int)$id;
        if ($id<=0 || !$this->tableExists($pdo,'vehicles')) { header('Location: '.url('admin/administration')); exit; }

        $st = $pdo->prepare("DELETE FROM vehicles WHERE id=?"); $st->execute([$id]);
        header('Location: '.url('admin/administration')); exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ADMINISTRATION: USERS
    // ─────────────────────────────────────────────────────────────────────
    public function usersStore()
    {
        Auth::requireRole('ADMIN');
        $pdo = $this->pdo();
        if (!$this->tableExists($pdo,'users')) { header('Location: '.url('admin/administration')); exit; }

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role     = trim($_POST['role'] ?? 'CUSTOMER');

        $cols=[]; $vals=[]; $args=[];
        // name column can be full_name OR name
        if ($fullName !== '') {
            if ($this->colExists($pdo,'users','full_name')) { $cols[]='full_name'; $vals[]='?'; $args[]=$fullName; }
            elseif ($this->colExists($pdo,'users','name'))   { $cols[]='name';      $vals[]='?'; $args[]=$fullName; }
        }
        if ($email !== '' && $this->colExists($pdo,'users','email')) { $cols[]='email'; $vals[]='?'; $args[]=$email; }
        if ($this->colExists($pdo,'users','role')) { $cols[]='role'; $vals[]='?'; $args[]=$role; }

        // password column may be 'password' or 'password_hash'
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($this->colExists($pdo,'users','password')) {
                $cols[]='password'; $vals[]='?'; $args[]=$hash;
            } elseif ($this->colExists($pdo,'users','password_hash')) {
                $cols[]='password_hash'; $vals[]='?'; $args[]=$hash;
            }
        }

        if (!empty($cols)) {
            $sql="INSERT INTO users (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
            $st=$pdo->prepare($sql); $st->execute($args);
        }
        header('Location: '.url('admin/administration')); exit;
    }

    public function usersUpdate($id)
    {
        Auth::requireRole('ADMIN');
        $pdo=$this->pdo(); $id=(int)$id;
        if ($id<=0 || !$this->tableExists($pdo,'users')) { header('Location: '.url('admin/administration')); exit; }

        $sets=[]; $args=[];
        if (isset($_POST['full_name'])) {
            $val = trim($_POST['full_name']);
            if ($this->colExists($pdo,'users','full_name')) { $sets[]='full_name=?'; $args[]=$val; }
            elseif ($this->colExists($pdo,'users','name'))  { $sets[]='name=?';      $args[]=$val; }
        }
        if (isset($_POST['email']) && $this->colExists($pdo,'users','email')) {
            $sets[]='email=?'; $args[]=trim($_POST['email']);
        }
        if (isset($_POST['role']) && $this->colExists($pdo,'users','role')) {
            $sets[]='role=?'; $args[]=trim($_POST['role']);
        }
        if (!empty($_POST['password'])) {
            $hash = password_hash((string)$_POST['password'], PASSWORD_DEFAULT);
            if ($this->colExists($pdo,'users','password')) {
                $sets[]='password=?'; $args[]=$hash;
            } elseif ($this->colExists($pdo,'users','password_hash')) {
                $sets[]='password_hash=?'; $args[]=$hash;
            }
        }

        if (!empty($sets)) {
            $args[]=$id;
            $sql="UPDATE users SET ".implode(',',$sets)." WHERE id=?";
            $st=$pdo->prepare($sql); $st->execute($args);
        }
        header('Location: '.url('admin/administration')); exit;
    }

    public function usersToggle($id)
{
    Auth::requireRole('ADMIN');
    $pdo=$this->pdo(); $id=(int)$id;
    if ($id<=0 || !$this->tableExists($pdo,'users')) { header('Location: '.url('admin/administration')); exit; }

    if ($this->colExists($pdo,'users','is_locked')) {
        // boolean lock style
        $pdo->prepare("UPDATE users SET is_locked = NOT IFNULL(is_locked,0) WHERE id=?")->execute([$id]);
    } elseif ($this->colExists($pdo,'users','status')) {
        // enum('ACTIVE','BANNED') per schema
        $pdo->prepare("UPDATE users SET status = IF(status='BANNED','ACTIVE','BANNED') WHERE id=?")->execute([$id]);
    }
    header('Location: '.url('admin/administration#users')); exit;
}


    // ─────────────────────────────────────────────────────────────────────
// ADMIN: Customer Interactions (list + deletes)
// ─────────────────────────────────────────────────────────────────────
public function interactions()
{
    Auth::requireRole('ADMIN');
    $pdo = $this->pdo();

    $items = [];

    if ($this->tableExists($pdo, 'ratings')) {
        // Base feedback rows
        $baseSql = "SELECT id, appointment_id, stars, comment, created_at
                    FROM ratings ORDER BY created_at DESC LIMIT 200";
        $rows = $pdo->query($baseSql)->fetchAll(PDO::FETCH_ASSOC);

        // If notifications exist, fetch latest FEEDBACK_REPLY per appointment using title pattern "... #<appointment_id>"
        $withReplies = [];
        if (!empty($rows) && $this->tableExists($pdo, 'notifications')) {
            // Build a map appointment_id => latest reply
            $map = [];
            foreach ($rows as $r) {
                $aid = (int)$r['appointment_id'];
                if ($aid <= 0) continue;
                $st = $pdo->prepare("
                    SELECT id, body AS reply_text, created_at
                    FROM notifications
                    WHERE type='FEEDBACK_REPLY' AND title LIKE ?
                    ORDER BY created_at DESC
                    LIMIT 1
                ");
                $st->execute(['%#'.$aid.'%']);
                $rep = $st->fetch(PDO::FETCH_ASSOC);
                if ($rep) $map[$aid] = $rep;
            }

            foreach ($rows as $r) {
                $aid = (int)$r['appointment_id'];
                $rep = $map[$aid] ?? null;
                $withReplies[] = [
                    'id' => (int)$r['id'],
                    'appointment_id' => $aid,
                    'stars' => (int)$r['stars'],
                    'comment' => (string)($r['comment'] ?? ''),
                    'created_at' => (string)$r['created_at'],
                    'reply_id' => $rep ? (int)$rep['id'] : null,
                    'reply_text' => $rep['reply_text'] ?? null,
                    'reply_created_at' => $rep['created_at'] ?? null,
                ];
            }
        } else {
            // No notifications table — just surface feedback
            foreach ($rows as $r) {
                $withReplies[] = [
                    'id' => (int)$r['id'],
                    'appointment_id' => (int)$r['appointment_id'],
                    'stars' => (int)$r['stars'],
                    'comment' => (string)($r['comment'] ?? ''),
                    'created_at' => (string)$r['created_at'],
                    'reply_id' => null, 'reply_text' => null, 'reply_created_at' => null,
                ];
            }
        }

        $items = $withReplies;
    }

    $this->render('admin/interactions/index.php', ['items' => $items]);
}

public function deleteFeedback($id)
{
    Auth::requireRole('ADMIN');
    $pdo = $this->pdo();
    $id = (int)$id;

    if ($id <= 0 || !$this->tableExists($pdo, 'ratings')) {
        header('Location: '.url('admin/interactions')); exit;
    }

    // Get appointment_id for cascade-like cleanup
    $st = $pdo->prepare("SELECT appointment_id FROM ratings WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $aid = (int)($st->fetchColumn() ?: 0);

    // Delete feedback
    $del = $pdo->prepare("DELETE FROM ratings WHERE id=?");
    $del->execute([$id]);

    // Delete related replies (notifications) if table exists
    if ($aid > 0 && $this->tableExists($pdo, 'notifications')) {
        $dn = $pdo->prepare("DELETE FROM notifications WHERE type='FEEDBACK_REPLY' AND title LIKE ?");
        $dn->execute(['%#'.$aid.'%']);
    }

    header('Location: '.url('admin/interactions')); exit;
}

public function deleteReply($id)
{
    Auth::requireRole('ADMIN');
    $pdo = $this->pdo();
    $id = (int)$id;

    if ($id > 0 && $this->tableExists($pdo, 'notifications')) {
        // Only delete replies
        $del = $pdo->prepare("DELETE FROM notifications WHERE id=? AND type='FEEDBACK_REPLY' LIMIT 1");
        $del->execute([$id]);
    }

    header('Location: '.url('admin/interactions')); exit;
}

}
