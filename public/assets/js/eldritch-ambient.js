// One bouncing mantra (DVD-logo style) behind the admin card.
// Ramps Zalgo + glitch continuously; never blocks the form.

(function () {
  const card = document.getElementById('admin-card');
  if (!card) return;

  // Ensure a full-screen canvas BEHIND the card
  let field = document.getElementById('eldritch-field');
  if (!field) {
    field = document.createElement('div');
    field.id = 'eldritch-field';
    document.body.appendChild(field);
  }
  Object.assign(field.style, {
    position: 'fixed',
    inset: '0',
    pointerEvents: 'none',
    zIndex: '0',          // card has z-index:1 in your CSS
    overflow: 'hidden'
  });

  const BASE = 'I THINK THEREFORE I AM';

  // Combining marks (Latin base + these)
  const ZUP = ['\u030d','\u030e','\u0304','\u0305','\u0311','\u0306','\u0310','\u0352','\u0357','\u0351','\u0307','\u0308','\u030a','\u0342','\u0343','\u0344','\u034a','\u034b','\u034c','\u0303','\u0302','\u030c','\u0350','\u0300','\u0301','\u030b','\u030f','\u0312','\u0313','\u0314','\u0309','\u0363','\u0364','\u0365','\u0366','\u0367','\u0368','\u0369','\u036a','\u036b','\u036c','\u036d','\u036e','\u036f'];
  const ZMID= ['\u0315','\u031b','\u0340','\u0341','\u0358','\u0321','\u0322','\u0327','\u0328','\u0334','\u0335','\u0336','\u034f'];
  const ZDOWN=['\u0316','\u0317','\u0318','\u0319','\u031c','\u031d','\u031e','\u031f','\u0320','\u0324','\u0325','\u0326','\u0329','\u032a','\u032b','\u032c','\u032d','\u032e','\u032f','\u0330','\u0331','\u0332','\u0333'];

  const pick = a => a[Math.floor(Math.random() * a.length)];
  function zalgoify(text, strength = 1) {
    let out = '';
    for (const ch of text) {
      if (ch === ' ') { out += ch; continue; }
      out += ch;
      const up   = Math.floor(Math.random() * (1 + strength));
      const mid  = Math.floor(Math.random() * (1 + strength));
      const down = Math.floor(Math.random() * (1 + strength));
      for (let i = 0; i < up;   i++) out += pick(ZUP);
      for (let i = 0; i < mid;  i++) out += pick(ZMID);
      for (let i = 0; i < down; i++) out += pick(ZDOWN);
    }
    return out;
  }

  // Create the single mantra element (uses .whisper-line CSS)
  const el = document.createElement('div');
  el.className = 'whisper-line';
  el.textContent = BASE;
  el.dataset.text = BASE;
  // Make sure positioning uses left/top (no transform bouncing)
  el.style.position = 'absolute';
  field.appendChild(el);

  // Bounds helpers
  let VW = window.innerWidth;
  let VH = window.innerHeight;
  function refreshBounds() { VW = window.innerWidth; VH = window.innerHeight; }
  window.addEventListener('resize', refreshBounds);

  // Random start inside viewport
  function rand(min, max) { return Math.random() * (max - min) + min; }
  let x = rand(0, Math.max(0, VW - (el.offsetWidth || 200)));
  let y = rand(0, Math.max(0, VH - (el.offsetHeight || 16)));

  // Velocity (px/s)
  let speed = 75;                       // initial
  let angle = rand(0, Math.PI * 2);
  let vx = Math.cos(angle) * speed;
  let vy = Math.sin(angle) * speed;

  // Zalgo / glitch ramp
  let level = 0;               // grows over time
  const levelMax = 10;
  let accum = 0;
  const TEXT_INTERVAL = 180;   // ms

  function updateText() {
    const strength = Math.min(levelMax, 1 + Math.floor(level));
    const doGlitch = level > 0.6 || Math.random() < (level / levelMax) * 0.6;

    const content = zalgoify(BASE, strength);
    el.textContent = content;
    el.dataset.text = content;

    if (doGlitch) el.classList.add('glitchy');
    else          el.classList.remove('glitchy');
  }

  function place() {
    el.style.left = Math.round(x) + 'px';
    el.style.top  = Math.round(y) + 'px';
  }

  // Make sure we’re not “stuck” with a near-zero velocity
  function nudgeIfStuck() {
    if (Math.abs(vx) + Math.abs(vy) < 1) {
      angle = rand(0, Math.PI * 2);
      vx = Math.cos(angle) * speed;
      vy = Math.sin(angle) * speed;
    }
  }

  let last = performance.now();
  function tick(now) {
    const dtMs = now - last;
    const dt   = dtMs / 1000;
    last = now;

    // Ramp intensity + speed slightly
    level = Math.min(levelMax, level + dt * 0.5);
    speed = Math.min(260, speed + dt * 2);

    // Normalize vx,vy to new speed (keeps direction)
    const len = Math.hypot(vx, vy) || 1;
    vx = (vx / len) * speed;
    vy = (vy / len) * speed;

    // Move
    x += vx * dt;
    y += vy * dt;

    // Measure size (changes as Zalgo grows)
    const ew = el.offsetWidth  || 200;
    const eh = el.offsetHeight || 16;

    // Bounce
    if (x <= 0)      { x = 0;        vx = Math.abs(vx); }
    if (y <= 0)      { y = 0;        vy = Math.abs(vy); }
    if (x + ew >= VW){ x = VW - ew;  vx = -Math.abs(vx); }
    if (y + eh >= VH){ y = VH - eh;  vy = -Math.abs(vy); }

    place();
    nudgeIfStuck();

    // Periodic text update (more corruption over time)
    accum += dtMs;
    if (accum >= TEXT_INTERVAL) {
      accum = 0;
      updateText();
    }

    requestAnimationFrame(tick);
  }

  // Initial paint + run
  place();
  updateText();
  requestAnimationFrame(tick);
})();
