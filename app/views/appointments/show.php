<?php
/** app/views/appointments/show.php */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);

/** @var array $a  Appointment row (service/vehicle/status/etc.) */
/** @var ?array $record  service_records row for this appointment, or null */

$badge = [
  'PENDING'=>'border-amber-300 text-amber-700 bg-amber-50',
  'APPROVED'=>'border-blue-300 text-blue-700 bg-blue-50',
  'CONFIRMED'=>'border-sky-300 text-sky-700 bg-sky-50',
  'IN_PROGRESS'=>'border-indigo-300 text-indigo-700 bg-indigo-50',
  'WAITING_PARTS'=>'border-orange-300 text-orange-700 bg-orange-50',
  'DELAYED'=>'border-yellow-300 text-yellow-700 bg-yellow-50',
  'COMPLETED'=>'border-emerald-300 text-emerald-700 bg-emerald-50',
  'CANCELLED'=>'border-rose-300 text-rose-700 bg-rose-50',
][$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';

/* ---------- pull fields from service_records ---------- */
$workDone  = '';
$diagNotes = '';
$odometer  = null;
$cost      = null;

/** normalize whatever is stored in service_records.photos into [ "file1.jpg", ... ] */
function normalize_photo_list_from_db($photosField, $fallbackField = null): array {
  $raw = $photosField;

  // if photos is empty/null, try fallback (photos_json)
  if ($raw === null || $raw === '' || $raw === [] ) {
    $raw = $fallbackField;
  }

  $out = [];

  // if it's a JSON string, decode; if CSV, split; if array, iterate; if plain string, accept
  if (is_string($raw)) {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      $raw = $decoded;
    } else {
      // CSV or single filename
      $parts = array_filter(array_map('trim', explode(',', $raw)));
      if ($parts) {
        $raw = $parts;
      }
    }
  }

  if (is_array($raw)) {
    foreach ($raw as $item) {
      if (is_string($item) && $item !== '') {
        $out[] = basename($item);
        continue;
      }
      if (is_array($item)) {
        foreach (['name','file','filename','path'] as $k) {
          if (!empty($item[$k]) && is_string($item[$k])) {
            $out[] = basename($item[$k]);
            break;
          }
        }
      }
    }
  } elseif (is_string($raw) && $raw !== '') {
    $out[] = basename($raw);
  }

  // unique + non-empty
  $out = array_values(array_unique(array_filter($out, fn($x)=>$x !== '')));
  return $out;
}

$photos = [];
if (is_array($record)) {
  if (isset($record['work_done']) && is_scalar($record['work_done']))                 $workDone  = (string)$record['work_done'];
  if (isset($record['diagnostics_notes']) && is_scalar($record['diagnostics_notes'])) $diagNotes = (string)$record['diagnostics_notes'];
  if (isset($record['odometer_km']) && is_scalar($record['odometer_km']))             $odometer  = (string)$record['odometer_km'];
  if (isset($record['cost']) && (is_scalar($record['cost']) || $record['cost']===0))  $cost      = (string)$record['cost'];

  // ✅ primary: photos column; fallback: photos_json
  $photos = normalize_photo_list_from_db($record['photos'] ?? null, $record['photos_json'] ?? null);
}

/* keep only files that actually exist */
$fsBase  = dirname(__DIR__, 2) . '/public/uploads/appointments/' . (int)$a['id'] . '/';
$webBase = url('uploads/appointments/' . (int)$a['id'] . '/');
$photos  = array_values(array_filter($photos, fn($f) => is_file($fsBase . $f)));
?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-6 md:p-8">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-extrabold mb-1"><?= $e($a['service_name']) ?></h1>
        <div class="text-sm text-gray-600">
          <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
        </div>
      </div>
      <span class="px-3 py-1 rounded-full text-xs border <?= $badge ?>">
        <?= $e($a['status']) ?>
      </span>
    </div>

    <!-- Info tiles -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Service Date</div>
        <div class="mt-1 font-semibold"><?= date('M d, Y • H:i', strtotime($a['scheduled_at'])) ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Technician</div>
        <div class="mt-1 font-semibold"><?= $e($a['staff_name'] ?: 'TBA') ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Vehicle</div>
        <div class="mt-1 font-semibold"><?= $e($a['model']) ?> (<?= $e($a['make']) ?>) • <?= $e($a['plate_no']) ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Estimated Cost</div>
        <div class="mt-1 font-semibold">RM <?= number_format((float)($a['service_price'] ?? 0), 2) ?></div>
        <?php if (!empty($a['est_hours'] ?? null)): ?>
          <div class="text-xs text-gray-500 mt-1"><?= $e($a['est_hours']) ?> hours</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Service record quick facts (if any) -->
    <?php if (is_array($record)): ?>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="border rounded-xl p-4">
          <div class="text-xs text-gray-500">Odometer</div>
          <div class="mt-1 font-semibold"><?= ($odometer !== null && $odometer !== '') ? $e($odometer).' km' : '—' ?></div>
        </div>
        <div class="border rounded-xl p-4">
          <div class="text-xs text-gray-500">Final Cost</div>
          <div class="mt-1 font-semibold"><?= ($cost !== null && $cost !== '') ? ('RM '.number_format((float)$cost,2)) : '—' ?></div>
        </div>
        <div class="border rounded-xl p-4">
          <div class="text-xs text-gray-500">Record Status</div>
          <div class="mt-1 font-semibold"><?= ($workDone!=='' || $diagNotes!=='' || $photos) ? 'Updated' : '—' ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Work & Diagnostics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
      <div class="border rounded-xl p-4">
        <div class="font-semibold mb-2">Work Performed</div>
        <div class="text-sm text-gray-800 whitespace-pre-line">
          <?= ($workDone !== '') ? $e($workDone) : '—' ?>
        </div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="font-semibold mb-2">Diagnostics Notes</div>
        <div class="text-sm text-gray-800 whitespace-pre-line">
          <?= ($diagNotes !== '') ? $e($diagNotes) : '—' ?>
        </div>
      </div>
    </div>

    <?php
// --- Photos (from service_records.photos) ---
$photos = [];
if (!empty($record) && array_key_exists('photos', $record)) {
    // $record['photos'] can be JSON or already an array, depending on earlier code paths
    if (is_string($record['photos'])) {
        $decoded = json_decode($record['photos'], true);
        if (is_array($decoded)) $photos = $decoded;
    } elseif (is_array($record['photos'])) {
        $photos = $record['photos'];
    }
}

// Base web path where ServiceRecordController saves files:
// .../public/uploads/appointments/{appointment_id}/{filename}
$webBase = url('uploads/appointments/'.(int)$a['id'].'/');

// Render
?>
<div class="mt-6">
  <div class="font-semibold mb-2">Photos</div>
  <?php if (!empty($photos)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <?php foreach ($photos as $img): ?>
        <?php
          // Be defensive: ensure it’s just a filename (no path parts)
          $file = is_string($img) ? basename($img) : '';
          if ($file === '') continue;
          $href = $webBase . rawurlencode($file);
        ?>
        <a href="<?= $href ?>" target="_blank" class="block group">
          <img
            src="<?= $href ?>"
            alt="photo"
            class="w-full h-32 object-cover rounded-lg border group-hover:opacity-90 transition"
            loading="lazy"
          >
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-sm text-gray-600">—</div>
  <?php endif; ?>
</div>


    <?php if (!in_array($a['status'], ['CANCELLED','COMPLETED'], true)): ?>
      <div class="mt-8 flex gap-3">
        <a href="<?= url('appointments/'.$a['id'].'/reschedule') ?>"
           class="px-5 py-2 rounded-full border hover:bg-gray-50">Reschedule</a>

        <form method="post" action="<?= url('appointments/cancel') ?>"
              onsubmit="return confirm('Cancel this appointment?');">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="px-5 py-2 rounded-full border text-rose-700 hover:bg-rose-50">Cancel Appointment</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
