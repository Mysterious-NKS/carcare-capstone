<?php /* tarot-modal */ ?>
<div id="tarot-modal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
  <div id="tarot-box" class="relative z-10 max-w-sm mx-auto mt-32 bg-white border rounded-2xl shadow-card p-6">
    <h2 class="text-xl font-bold mb-2">Choose your card</h2>
    <p class="text-gray-600 mb-4">Whisper the arcana and prove you’re worthy.</p>
    <form id="tarot-form" class="space-y-3">
      <input id="tarot-answer" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="tarot card..." autofocus>
      <div class="flex items-center justify-end gap-3">
        <button type="button" id="tarot-cancel" class="px-3 py-2 rounded-full border">Cancel</button>
        <button class="px-3 py-2 rounded-full bg-black text-white">Reveal</button>
      </div>
      <div id="tarot-hint" class="text-xs text-gray-500">Hint: a traveler with zero.</div>
    </form>
  </div>
</div>
