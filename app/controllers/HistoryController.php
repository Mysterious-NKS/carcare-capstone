<?php

class HistoryController extends Controller
{
    // GET /history
    public function index()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $sql = "SELECT r.id, r.completed_at, r.cost,
                       a.id AS appointment_id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.make, v.model, v.year, v.plate_no
                FROM service_records r
                JOIN appointments a ON a.id = r.appointment_id
                JOIN services s     ON s.id = a.service_id
                JOIN vehicles v     ON v.id = a.vehicle_id
                WHERE a.customer_id = ?
                ORDER BY COALESCE(r.completed_at, a.scheduled_at) DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$uid]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        // Your folder first
        $this->renderAny(['history/history.php', 'customer/history.php'], ['items' => $items]);
    }

    // GET /history/detail?id=123  (id = service_record id)
    public function detail()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();
        $id  = (int)($_GET['id'] ?? 0);

        $sql = "SELECT r.*, a.status, a.scheduled_at,
                       s.name AS service_name,
                       v.make, v.model, v.year, v.plate_no,
                       rt.stars AS rating_stars, rt.comment AS rating_comment
                FROM service_records r
                JOIN appointments a ON a.id = r.appointment_id
                JOIN services s     ON s.id = a.service_id
                JOIN vehicles v     ON v.id = a.vehicle_id
                LEFT JOIN ratings rt ON rt.appointment_id = r.appointment_id
                WHERE r.id=? AND a.customer_id=? LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([$id, $uid]);
        $rec = $st->fetch(PDO::FETCH_ASSOC);

        if (!$rec) return $this->redirect('history');

        // decode photos JSON if present
        $photos = [];
        if (!empty($rec['photos'])) {
            try {
                $decoded = json_decode($rec['photos'], true);
                if (is_array($decoded)) {
                    // normalise to an array of strings (paths)
                    foreach ($decoded as $p) {
                        if (is_string($p) && $p !== '') $photos[] = $p;
                        elseif (is_array($p) && isset($p['file'])) $photos[] = $p['file'];
                    }
                }
            } catch (Throwable $e) { /* ignore */ }
        }

        $this->renderAny(
            ['history/history-detail.php', 'customer/history-detail.php'],
            [
                'rec'    => $rec,
                'photos' => $photos,
                'stars'  => $rec['rating_stars'] ?? null,
                'comment'=> $rec['rating_comment'] ?? null
            ]
        );
    }
}
