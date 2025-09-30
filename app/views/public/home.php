<?php
// Static homepage mirroring Figma.
// Hero uses the same Unsplash supercar as before.
// Featured services use your local images in /public/assets/img/services/.
?>
<div class="max-w-7xl mx-auto px-4">

  <!-- Hero -->
  <section class="grid md:grid-cols-2 gap-8 items-center py-16 md:py-20">
    <div>
      <h1 class="text-6xl md:text-7xl font-extrabold leading-none mb-6">title</h1>

      <p class="text-gray-700 leading-relaxed max-w-xl">
        Our mission is to deliver premium automotive services that are purely made from expertise and precision.
        Our products reflect modern engineering excellence.
      </p>

      <div class="mt-6">
        <a href="<?= url('appointments/create') ?>"
           class="inline-flex items-center gap-2 px-5 h-11 rounded-full bg-black text-white hover:shadow-md">
          Our Services
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 5l5 5-5 5"/></svg>
        </a>
      </div>
    </div>

    <div class="relative">
      <img
        src="https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1600&q=80"
        alt="Black sports car side profile"
        class="w-full rounded-2xl shadow-card object-cover"
      />
    </div>
  </section>

  <!-- Black band with hero photo & copy -->
  <section class="relative rounded-[24px] bg-black overflow-hidden">
    <div class="px-6 md:px-10 py-12 md:py-14">
      <h2 class="text-white text-3xl md:text-4xl font-semibold">
        premium ✦ bespoke ✦ precision
      </h2>

      <div class="mt-6">
        <img
          src="https://images.unsplash.com/photo-1494905998402-395d579af36f?auto=format&fit=crop&w=1600&q=80"
          alt="Close-up of a classic car hood and grille"
          class="w-full rounded-xl object-cover"
        />
      </div>

      <p class="text-gray-200 leading-relaxed max-w-4xl mt-6">
        At [name], we are dedicated to crafting premium automotive experiences made exclusively from
        high-quality service and precision materials. We embrace modern engineering while incorporating
        unique creative elements that bring warmth and personality to every vehicle.
      </p>

      <p class="text-gray-200 leading-relaxed mt-4">
        We embrace quality, minimalism and comfort.
      </p>

      <div class="mt-6">
        <a href="<?= url('register') ?>"
           class="inline-flex items-center gap-2 px-5 h-11 rounded-full bg-white text-black hover:shadow-md">
          Sign Up Now
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 5l5 5-5 5"/></svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Featured services -->
<section id="services" class="py-14 md:py-16">
  <h3 class="text-3xl md:text-4xl font-bold mb-8">featured services</h3>

  <div class="grid md:grid-cols-3 gap-6">

    <!-- 1 Engine Diagnostics -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/engine-diagnostics.jpg') ?>" alt="Engine diagnostics in bay"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Engine Diagnostics</h4>
        <p class="text-sm text-gray-600 mt-2">Identify and fix performance issues using diagnostic tools.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

    <!-- 2 Brake Inspection & Replacement -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/brake-inspection.avif') ?>" alt="Brake inspection close-up"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Brake Inspection &amp; Replacement</h4>
        <p class="text-sm text-gray-600 mt-2">Safety check and replacement of brake pads/discs if needed.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

    <!-- 3 Transmission Service -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/transmission-service.jpg') ?>" alt="Manual transmission components"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Transmission Service</h4>
        <p class="text-sm text-gray-600 mt-2">Fluid replacement and system check for smooth gear shifting.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

    <!-- 4 Oil Change -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/oil-change.jpg') ?>" alt="Pouring engine oil with funnel"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Oil Change Service</h4>
        <p class="text-sm text-gray-600 mt-2">Replacement of engine oil and filter to ensure smooth engine operation.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

    <!-- 5 Battery Testing -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/battery-testing.jpg') ?>" alt="Installing / testing a car battery"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Battery Testing &amp; Replacement</h4>
        <p class="text-sm text-gray-600 mt-2">Ensure reliable vehicle starting power.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

    <!-- 6 Tire Rotation -->
    <article class="bg-white border rounded-2xl shadow-card overflow-hidden">
      <img src="<?= url('assets/img/services/tire-rotation.jpg') ?>" alt="Tire mounting and balancing"
           class="w-full h-48 object-cover" />
      <div class="p-5">
        <h4 class="font-semibold">Tire Rotation &amp; Balancing</h4>
        <p class="text-sm text-gray-600 mt-2">Improve tire lifespan and ensure even wear.</p>
        <a href="<?= url('appointments/create') ?>" class="inline-block mt-3 text-sm font-medium underline">
          Book appointment
        </a>
      </div>
    </article>

  </div>

  <p class="text-gray-600 mt-6">… and more. Just name it and we’ll do it for you.</p>
</section>

</div>
