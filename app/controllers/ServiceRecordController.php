<?php

class ServiceRecordController extends Controller
{
    private function pdo(): PDO { return DB::pdo(); }

    private function tableExists(PDO $pdo, string $table): bool {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1");
        $q->execute([$table]);
        return (bool)$q->fetchColumn();
    }
    private function colExists(PDO $pdo, string $table, string $col): bool {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1");
        $q->execute([$table,$col]);
        return (bool)$q->fetchColumn();
    }

    /** POST /service-records/save */
    public function save()
    {
        Auth::requireRole('STAFF');
        $pdo = $this->pdo();

        if (!$this->tableExists($pdo, 'service_records')) {
            $_SESSION['flash'] = ['err' => 'Service records table not found.'];
            return $this->redirect('staff/workflow');
        }

        $appointment_id  = (int)($_POST['appointment_id'] ?? 0);
        if ($appointment_id <= 0) {
            $_SESSION['flash'] = ['err' => 'Invalid appointment.'];
            return $this->redirect('staff/workflow');
        }

        // Ensure this appointment belongs to the logged-in staff (if staff_id column exists)
        if ($this->colExists($pdo, 'appointments', 'staff_id')) {
            $chk = $pdo->prepare("SELECT customer_id, staff_id FROM appointments WHERE id=?");
            $chk->execute([$appointment_id]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int)$row['staff_id'] !== (int)Auth::id()) {
                $_SESSION['flash'] = ['err' => 'You are not assigned to this appointment.'];
                return $this->redirect('staff/workflow');
            }
        }

        // Collect flexible columns
        $fields = [];
        $vals   = [];

        $map = [
            'odometer_km'       => (int)($_POST['odometer_km'] ?? 0),
            'work_done'         => trim($_POST['work_done'] ?? ''),
            'diagnostics_notes' => trim($_POST['diagnostics_notes'] ?? ''),
            'cost'              => ($_POST['cost'] ?? '') !== '' ? (float)$_POST['cost'] : null,
        ];

        foreach ($map as $col => $val) {
            if ($this->colExists($pdo, 'service_records', $col)) {
                $fields[$col] = $val;
            }
        }

        // Handle photo uploads -> store filenames in photos_json if such column exists
        $uploaded = [];
        if (!empty($_FILES['photos']['name']) && is_array($_FILES['photos']['name'])) {
            $dir = dirname(__DIR__,2) . "/public/uploads/appointments/$appointment_id";
            if (!is_dir($dir)) @mkdir($dir, 0777, true);

            $count = count($_FILES['photos']['name']);
            for ($i=0;$i<$count;$i++) {
                $name = $_FILES['photos']['name'][$i];
                $tmp  = $_FILES['photos']['tmp_name'][$i] ?? null;
                $err  = $_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

                if ($err !== UPLOAD_ERR_OK || !$tmp) continue;

                $finfo = @mime_content_type($tmp);
                if (!preg_match('~^image/(jpeg|png|gif|webp)$~i', (string)$finfo)) continue;

                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $fname = uniqid('img_', true) . '.' . strtolower($ext);
                if (@move_uploaded_file($tmp, "$dir/$fname")) {
                    $uploaded[] = $fname;
                }
            }
        }
        if ($uploaded && $this->colExists($pdo, 'service_records', 'photos_json')) {
            // merge with existing photos if present
            $st = $pdo->prepare("SELECT photos_json FROM service_records WHERE appointment_id=?");
            $st->execute([$appointment_id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $existing = [];
            if ($row && !empty($row['photos_json'])) {
                $parsed = json_decode($row['photos_json'], true);
                if (is_array($parsed)) $existing = $parsed;
            }
            $fields['photos_json'] = json_encode(array_values(array_unique(array_merge($existing, $uploaded))));
        }

        // Upsert (update if exists; else insert)
        $exists = $pdo->prepare("SELECT id FROM service_records WHERE appointment_id=?");
        $exists->execute([$appointment_id]);
        $rid = $exists->fetchColumn();

        if ($rid) {
            $set = [];
            $vals = [];
            foreach ($fields as $col => $val) {
                $set[] = "$col=?";
                $vals[] = $val;
            }
            if ($set) {
                $vals[] = $appointment_id;
                $sql = "UPDATE service_records SET ".implode(', ', $set)." WHERE appointment_id=?";
                $pdo->prepare($sql)->execute($vals);
            }
        } else {
            $cols = ['appointment_id']; $qs = ['?']; $vals = [$appointment_id];
            foreach ($fields as $col => $val) { $cols[] = $col; $qs[] = '?'; $vals[] = $val; }
            $sql = "INSERT INTO service_records (".implode(',', $cols).") VALUES (".implode(',', $qs).")";
            $pdo->prepare($sql)->execute($vals);
        }

        $_SESSION['flash'] = ['ok' => 'Service record saved.'];
        // Back to workflow on same appointment
        $this->redirect('staff/workflow?appointment_id='.$appointment_id);
    }
}
