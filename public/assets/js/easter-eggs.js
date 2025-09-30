/* public/assets/js/easter-eggs.js
   Handles easter eggs only — forms stay clean.

   Triggers:
   1) KONAMI code on email field → /register/staff
   2) "Reverend Insanity" + blank password → tarot modal
      - Correct answer = "The Fool" → /register/admin
*/

(function () {
  const form  = document.getElementById('login-form');
  const email = document.getElementById('login-email');
  const pass  = document.getElementById('login-password');
  if (!form || !email) return;

  // Base URL from the <form data-base="...">
  const BASE = (form.dataset.base || '').replace(/\/+$/, '');

  const STAFF_URL = BASE + '/register/staff';
  const ADMIN_URL = BASE + '/register/admin';

  /* ------------------ KONAMI ------------------ */
  const seq = [
    'ArrowUp','ArrowUp','ArrowDown','ArrowDown',
    'ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','b','a','Enter'
  ];
  let idx = 0;

  const rings = [
    'ring-blue-500','ring-red-500','ring-green-500','ring-violet-500',
    'ring-amber-500','ring-teal-500','ring-fuchsia-500','ring-indigo-500',
    'ring-rose-500','ring-emerald-500','ring-cyan-500'
  ];
  const bgs = [
    'bg-blue-50','bg-red-50','bg-green-50','bg-violet-50',
    'bg-amber-50','bg-teal-50','bg-fuchsia-50','bg-indigo-50',
    'bg-rose-50','bg-emerald-50','bg-cyan-50'
  ];

  function resetKonamiVisual() {
    email.classList.remove('ring-2', ...rings, ...bgs);
  }
  function stepVisual(step) {
    resetKonamiVisual();
    email.classList.add('ring-2', rings[Math.min(step, rings.length-1)], bgs[Math.min(step, bgs.length-1)]);
  }

  email.addEventListener('keydown', (e) => {
    let key = e.key;
    if (key.length === 1) key = key.toLowerCase();

    const expected = seq[idx];
    const ok =
      key === expected ||
      (expected === 'b' && (key === 'b' || key === 'B')) ||
      (expected === 'a' && (key === 'a' || key === 'A'));

    if (ok) {
      stepVisual(idx);
      idx++;

      if (idx === seq.length) {
        e.preventDefault();
        e.stopPropagation();
        resetKonamiVisual();
        idx = 0;
        window.location.href = STAFF_URL;
      }
      return;
    }

    if (idx > 0) resetKonamiVisual();
    idx = 0;
  });

  email.addEventListener('blur', resetKonamiVisual);
  email.setAttribute('autocomplete', 'off');

  /* ------------------ ADMIN TRIGGER ------------------ */
  form.addEventListener('submit', (e) => {
    const val = (email.value || '').trim();
    const pw  = (pass ? pass.value : '').trim();

    if (val === 'Reverend Insanity' && pw === '') {
      e.preventDefault();

      const card   = document.getElementById('login-card');
      const shards = document.getElementById('shards');
      if (card)  card.classList.add('crack');
      if (shards) shards.classList.remove('hidden');

      const modal = document.getElementById('tarot-modal');
      if (modal) modal.classList.remove('hidden');

      const input = document.getElementById('tarot-answer');
      if (input) setTimeout(() => input.focus(), 60);
    }
  });

  /* ------------------ TAROT MODAL ------------------ */
  const tarotForm   = document.getElementById('tarot-form');
  const tarotCancel = document.getElementById('tarot-cancel');

  if (tarotCancel) {
    tarotCancel.addEventListener('click', () => {
      const modal = document.getElementById('tarot-modal');
      if (modal) modal.classList.add('hidden');
      const card   = document.getElementById('login-card');
      const shards = document.getElementById('shards');
      if (card)  card.classList.remove('crack');
      if (shards) shards.classList.add('hidden');
    });
  }

  if (tarotForm) {
    tarotForm.addEventListener('submit', (ev) => {
      ev.preventDefault();
      const ans = (document.getElementById('tarot-answer').value || '').trim().toLowerCase();
      if (ans === 'the fool') {
        window.location.href = ADMIN_URL;
      } else {
        const modal = document.getElementById('tarot-modal');
        if (modal) {
          modal.classList.remove('shake');
          void modal.offsetWidth;
          modal.classList.add('shake');
        }
      }
    });
  }
})();
