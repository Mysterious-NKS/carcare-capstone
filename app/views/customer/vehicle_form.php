<?php
// $mode = 'create'|'add'|'edit' ; $vehicle (array|null) ; $photos (array|null)
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
$isEdit = isset($mode) && $mode === 'edit';
$v = $vehicle ?? [];
$action = $isEdit ? url('customer/vehicles/edit?id=' . $v['id']) : url('customer/vehicles');

$lsd = $v['last_service_date'] ?? '';
if ($lsd) $lsd = substr($lsd, 0, 10); // yyyy-mm-dd for <input type="date">
?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <a href="<?= url('customer/vehicles') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← go back</a>

  <div class="bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-6"><?= $isEdit ? 'Edit Vehicle' : 'Add Vehicle' ?></h1>

    <?php if(isset($_GET['e']) && $_GET['e']==='invalid'): ?>
      <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
        Please fill in the basics: Make, Model, and Year.
      </div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>" enctype="multipart/form-data">

      <!-- Vehicle Information -->
      <section>
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <span class="inline-block w-5 h-5 rounded-full bg-gray-900"></span>
          Vehicle Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="text-sm text-gray-600">Make</label>
            <input name="make" class="mt-1 w-full border rounded-lg px-3 py-2" required
                   value="<?= $e($v['make'] ?? '') ?>">
          </div>
          <div>
            <label class="text-sm text-gray-600">Model</label>
            <input name="model" class="mt-1 w-full border rounded-lg px-3 py-2" required
                   value="<?= $e($v['model'] ?? '') ?>">
          </div>

          <div>
            <label class="text-sm text-gray-600">Year</label>
            <input name="year" type="number" min="1900" max="2100" class="mt-1 w-full border rounded-lg px-3 py-2" required
                   value="<?= $e($v['year'] ?? '') ?>">
          </div>
          <div>
            <label class="text-sm text-gray-600">License Plate</label>
            <input name="license_plate" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['plate_no'] ?? '') ?>">
          </div>

          <div>
            <label class="text-sm text-gray-600">Color (optional)</label>
            <input name="color" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['color'] ?? '') ?>">
          </div>
          <div>
            <label class="text-sm text-gray-600">VIN</label>
            <input name="vin" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['vin'] ?? '') ?>">
          </div>

          <div>
            <label class="text-sm text-gray-600">Current Mileage (km)</label>
            <input name="mileage" type="number" min="0" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['mileage'] ?? 0) ?>">
          </div>
          <div>
            <label class="text-sm text-gray-600">Last Service Date</label>
            <input name="last_service_date" type="date" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($lsd) ?>">
          </div>
        </div>
      </section>

      <!-- Insurance (Optional) -->
      <section class="mt-6">
        <h2 class="text-lg font-semibold mb-4">Insurance Information (Optional)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="text-sm text-gray-600">Insurance Provider</label>
            <input name="insurance_provider" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['insurance_provider'] ?? '') ?>">
          </div>
          <div>
            <label class="text-sm text-gray-600">Policy Number</label>
            <input name="policy_number" class="mt-1 w-full border rounded-lg px-3 py-2"
                   value="<?= $e($v['policy_number'] ?? '') ?>">
          </div>
        </div>
      </section>

      <!-- Notes -->
      <section class="mt-6">
        <h2 class="text-lg font-semibold mb-4">Additional Notes</h2>
        <textarea name="notes" rows="5" class="w-full border rounded-lg px-3 py-2"
                  placeholder="Mods, special requirements, etc."><?= $e($v['notes'] ?? '') ?></textarea>
      </section>

      <!-- Existing photos (edit only) -->
      <?php if ($isEdit && !empty($photos)): ?>
        <section class="mt-6">
          <h2 class="text-lg font-semibold mb-4">Existing Photos</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <?php foreach ($photos as $p): ?>
              <div class="overflow-hidden rounded-xl border bg-white">
                <img src="<?= $e($p['file']) ?>" class="w-full h-32 object-cover" alt="">
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Vehicle Photos (upload new) -->
      <section class="mt-6">
        <h2 class="text-lg font-semibold mb-4">Vehicle Photos</h2>
        <div id="dropArea"
             class="border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center bg-gray-50 hover:bg-gray-100 transition">
          <div class="text-5xl mb-2">📷</div>
          <p class="text-gray-700 mb-2">Drag and drop photos here, or click to browse.</p>
          <p class="text-xs text-gray-500 mb-4">JPEG, PNG, WEBP, GIF • up to 5MB each</p>

          <input id="photosInput" name="photos[]" type="file" accept="image/*" multiple class="hidden">
          <button type="button" id="chooseBtn" class="px-4 py-2 rounded-full border">Choose Files</button>

          <div id="preview" class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 w-full"></div>
        </div>
      </section>

      <div class="flex items-center gap-3 mt-8">
        <a href="<?= url('customer/vehicles') ?>" class="px-5 py-2 rounded-full border">Cancel</a>
        <button class="px-6 py-3 rounded-full bg-black text-white">
          <?= $isEdit ? 'Save Changes' : 'Add Vehicle' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const dropArea = document.getElementById('dropArea');
const input    = document.getElementById('photosInput');
const choose   = document.getElementById('chooseBtn');
const preview  = document.getElementById('preview');

choose?.addEventListener('click', () => input.click());
dropArea?.addEventListener('click', () => input.click());

['dragenter','dragover'].forEach(evt =>
  dropArea?.addEventListener(evt, e => { e.preventDefault(); dropArea.classList.add('bg-gray-100'); })
);
['dragleave','drop'].forEach(evt =>
  dropArea?.addEventListener(evt, e => { e.preventDefault(); dropArea.classList.remove('bg-gray-100'); })
);

dropArea?.addEventListener('drop', e => {
  if (!e.dataTransfer.files?.length) return;
  input.files = e.dataTransfer.files;
  renderPreviews(input.files);
});
input?.addEventListener('change', () => renderPreviews(input.files));

function renderPreviews(files) {
  preview.innerHTML = '';
  [...files].forEach(file => {
    if (!file.type.startsWith('image/')) return;
    const url = URL.createObjectURL(file);
    const card = document.createElement('div');
    card.className = 'overflow-hidden rounded-xl border bg-white';
    card.innerHTML = `<img src="${url}" class="w-full h-32 object-cover" alt="">`;
    preview.appendChild(card);
  });
}
</script>
