<section class="bg-white">
  <div class="max-w-7xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <h1 class="text-5xl font-extrabold tracking-tight mb-4">title</h1>
      <p class="text-gray-600 mb-6">Premium automotive services—fast, transparent, customer-first.</p>
      <div class="flex gap-3">
        <a href="<?= url('register') ?>" class="px-5 py-3 rounded-full bg-black text-white">Get started</a>
        <a href="<?= url('login') ?>" class="px-5 py-3 rounded-full border">Sign in</a>
      </div>
    </div>
    <div class="aspect-[16/8] bg-gray-200 rounded-2xl shadow-card grid place-items-center">car image</div>
  </div>
</section>
<section class="bg-black text-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-2xl md:text-3xl font-semibold mb-6">premium ✦ bespoke ✦ precision</div>
    <div class="rounded-2xl overflow-hidden shadow-card"><div class="aspect-[16/7] bg-zinc-800 grid place-items-center">hero photo</div></div>
    <p class="text-zinc-300 mt-6 max-w-3xl">Modern, delightful maintenance experience with reminders and transparent pricing.</p>
    <a href="<?= url('register') ?>" class="inline-block mt-6 px-5 py-3 rounded-full bg-white text-black">Sign up</a>
  </div>
</section>
<section class="py-16"><div class="max-w-7xl mx-auto px-4">
  <h2 class="text-2xl font-semibold mb-6">featured services</h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach(['Engine Diagnostics','Brake Inspection','Transmission Service','Oil Change','Battery Testing','Tire Rotation'] as $svc): ?>
      <div class="rounded-xl border shadow-card overflow-hidden">
        <div class="aspect-[4/3] bg-gray-200"></div>
        <div class="p-4">
          <div class="font-semibold"><?= $svc ?></div>
          <p class="text-sm text-gray-600">Basic description to mirror the Figma cards.</p>
          <a href="<?= url('register') ?>" class="inline-block mt-3 text-sm underline">Learn more</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div></section>
