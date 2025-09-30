<?php /* footer (shared) */ ?>
<footer class="bg-black text-gray-300 mt-16">
  <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-10">
    <div>
      <div class="text-white font-extrabold text-xl mb-3">Logo</div>
      <div class="text-sm text-gray-400">© <?= date('Y') ?> [title]. All rights reserved.</div>
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
