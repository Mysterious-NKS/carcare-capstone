<?php /* tarot-modal (no history/autofill, always cleared) */ ?>
<div id="tarot-modal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

  <div id="tarot-box"
       class="relative z-10 max-w-sm mx-auto mt-32 bg-white border rounded-2xl shadow-card p-6">
    <h2 class="text-xl font-bold mb-2">Choose an Tarot card</h2>
    <p class="text-gray-600 mb-4">To who shall we praise</p>

    <!-- Turn OFF browser autocomplete on the form -->
    <form id="tarot-form" class="space-y-3" autocomplete="off" novalidate>
      <input
        id="tarot-answer"
        name="tarot_answer"                    
        type="text"
        class="w-full border rounded-lg px-3 py-2"
        placeholder="tarot card..."
        inputmode="text"
        autocapitalize="off"
        spellcheck="false"
        autocomplete="off"                   
        data-lpignore="true"                  
        data-1p-ignore="true"
      >
      <div class="flex items-center justify-end gap-3">
        <button type="button" id="tarot-cancel" class="px-3 py-2 rounded-full border">Cancel</button>
        <button type="submit" class="px-3 py-2 rounded-full bg-black text-white">Reveal</button>
      </div>
      <div id="tarot-hint" class="text-xs text-gray-500">Hint: The Mysterious Ruler above the Gray Fog;
The King of Yellow and Black who wields good luck.</div>
    </form>
  </div>
</div>

<script>
  (function () {
    const modal  = document.getElementById('tarot-modal');
    const input  = document.getElementById('tarot-answer');
    const form   = document.getElementById('tarot-form');
    const cancel = document.getElementById('tarot-cancel');

    function clearField() {
      input.value = '';
      // Also clear any selection/caret to avoid weird autofill UIs
      input.setSelectionRange(0, 0);
    }

    // Ensure the field is cleared when the modal is shown/hidden
    const obs = new MutationObserver(() => {
      const isHidden = modal.classList.contains('hidden');
      if (!isHidden) {
        clearField();
        // Focus after clearing so the virtual keyboard shows without suggestions
        setTimeout(() => input.focus({ preventScroll: true }), 0);
      }
    });
    obs.observe(modal, { attributes: true, attributeFilter: ['class'] });

    // Cancel button closes modal (your code may toggle the class elsewhere)
    cancel.addEventListener('click', () => {
      clearField();
      modal.classList.add('hidden');
    });

    // Prevent the browser from trying to remember/submit anything
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      // Your existing “reveal” handler can read input.value here
      // e.g., window.onTarotReveal?.(input.value);
    });
  })();
</script>
