<div class="max-w-7xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-6">welcome, <?= htmlspecialchars($name ?? 'friend') ?>.</h1>
  <div class="grid md:grid-cols-3 gap-6 mb-10">
    <div class="rounded-xl border shadow-card p-5"><div class="text-sm text-gray-500 mb-1">Upcoming Appointments</div><div class="text-3xl font-bold">2</div></div>
    <div class="rounded-xl border shadow-card p-5"><div class="text-sm text-gray-500 mb-1">Registered Vehicles</div><div class="text-3xl font-bold">2</div></div>
    <div class="rounded-xl border shadow-card p-5"><div class="text-sm text-gray-500 mb-1">Maintenance Due</div><div class="text-3xl font-bold">1</div></div>
  </div>
  <h2 class="text-xl font-semibold mb-3">Recent Activity</h2>
  <div class="space-y-3">
    <div class="rounded-xl border p-4 flex items-center justify-between"><div><div class="font-semibold">Brake Inspection</div><div class="text-sm text-gray-600">Scheduled • 2025-05-26</div></div><span class="px-3 py-1 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">PENDING</span></div>
    <div class="rounded-xl border p-4 flex items-center justify-between"><div><div class="font-semibold">Tire Rotation & Balancing</div><div class="text-sm text-gray-600">Completed • 2025-03-18</div></div><span class="px-3 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">COMPLETED</span></div>
  </div>
</div>
