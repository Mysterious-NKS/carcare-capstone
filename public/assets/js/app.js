console.log("CarCare ready");

// System Log expand/collapse (no dependencies)
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.log-toggle');
  if (!btn) return;
  const container = btn.parentElement;
  const body = container.querySelector('.log-body');
  if (body) body.classList.toggle('hidden');
});

// Simple client-side filter (kept for any legacy lists)
window.filterList = function(inputEl, listSelector){
  const term = (inputEl.value || '').toLowerCase().trim();
  const list = document.querySelector(listSelector);
  if (!list) return;
  for (const li of list.children) {
    const text = li.textContent.toLowerCase();
    li.style.display = term === '' || text.includes(term) ? '' : 'none';
  }
};

// Minimal toggler for collapsible edit/view sections
document.addEventListener('click', function(e){
  const t = e.target.closest('[data-toggle]');
  if (!t) return;
  const sel = t.getAttribute('data-target');
  if (!sel) return;
  const el = document.querySelector(sel);
  if (!el) return;
  el.classList.toggle('hidden');
});
