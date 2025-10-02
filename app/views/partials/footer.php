<?php /* footer (shared, full-bleed, no bottom gap) */ ?>
<footer class="mt-16 relative text-gray-300">
  <!-- Sticky-bottom page layout (doesn't affect horizontal width) -->
  <style>
    html, body { height: 100%; }
    /* Make the page a flex column so the footer sits at the bottom when content is short */
    body { min-height: 100vh; display: flex; flex-direction: column; }
    /* Your <main> is opened in head.php; let it grow to fill remaining space */
    body > main { flex: 1 0 auto; }

    /* Avoid tiny 1px horizontal scrollbars caused by fractional widths */
    html, body { overflow-x: hidden; }
  </style>

  <!-- Full-bleed black background (keeps your horizontal behavior) -->
  <div class="absolute inset-0 bg-black"></div>

  <!-- Content container (aligned to site width) -->
  <div class="relative max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-10">
    <div>
      <div class="text-white logo-wordmark text-xl mb-3">pitstop</div>
      <div class="text-sm text-gray-400">© <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</div>
    </div>

    <div>
      <div class="text-white font-semibold mb-3">COMPANY</div>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:text-white" href="<?= url('about') ?>">About</a></li>
        <li><a class="hover:text-white" href="<?= url('contact') ?>">Contact</a></li>
      </ul>
    </div>

    <div>
      <div class="text-white font-semibold mb-3">CUSTOMER SERVICE</div>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:text-white" href="#">Support</a></li>
        <li><a class="hover:text-white" href="#">Warranty</a></li>
        <li><a class="hover:text-white" href="#">FAQ</a></li>
      </ul>
    </div>
  </div>
</footer>
