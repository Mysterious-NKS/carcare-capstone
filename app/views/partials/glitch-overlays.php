<?php
/* Full-screen banish overlay.
   Shows once when $_SESSION['glitch'] === 'banish', then auto-clears. */

$show = false;
if (isset($_SESSION['glitch']) && $_SESSION['glitch'] === 'banish') {
    $show = true;
    unset($_SESSION['glitch']); // one-time flash
}
if (!$show) return;
?>
<div id="banish-overlay">
  <div id="banish-text">WHO ARE YOU???</div>
</div>

<style>
  #banish-overlay{
    position:fixed; inset:0; z-index:999999;
    display:flex; align-items:center; justify-content:center;
    background: radial-gradient(ellipse at center, rgba(8,10,16,.98) 0%, rgba(0,0,0,1) 70%);
    pointer-events:auto;
  }
  #banish-text{
    color:#fff; font-weight:800; text-transform:uppercase;
    letter-spacing:.12em; text-align:center;
    font-size:clamp(40px, 8vw, 120px);
    text-shadow:
      0 0 8px rgba(255,0,80,.6),
      0 0 28px rgba(0,255,255,.25);
    animation:flicker .14s infinite alternate;
    user-select:none;
  }
  @keyframes flicker{
    from { opacity:.9; filter:hue-rotate(0deg); }
    to   { opacity:1;  filter:hue-rotate(6deg); }
  }
  #banish-text.banish{
    animation:none;
    text-shadow:
      0 0 12px rgba(255,255,255,.9),
      0 0 60px rgba(255,255,255,.35);
  }
</style>

<script>
(function(){
  const overlay = document.getElementById('banish-overlay');
  const text    = document.getElementById('banish-text');
  if(!overlay || !text) return;

  // 1) ARMED DELAY — ignore any events fired during initial load
  let armed = false;
  // double rAF arms after paint, or use a small timeout — either works
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      setTimeout(() => { armed = true; }, 200); // ~200ms grace
    });
  });

  // 2) Prevent immediate trigger from tiny synthetic mousemove on load
  let lastX = null, lastY = null;
  function movedEnough(e) {
    if (lastX === null || lastY === null) { lastX = e.clientX; lastY = e.clientY; return false; }
    const dx = Math.abs(e.clientX - lastX), dy = Math.abs(e.clientY - lastY);
    lastX = e.clientX; lastY = e.clientY;
    return (dx + dy) > 4; // require a real movement
  }

  let fired = false;
  function banish() {
    if (!armed || fired) return;
    fired = true;
    text.textContent = 'BEGONE!';
    text.classList.add('banish');
    setTimeout(function(){
      window.location.href = '<?= url('login') ?>';
    }, 1000); // show BEGONE! for ~2s
  }

  // Only trigger on real user actions AFTER we're armed
  window.addEventListener('keydown', () => armed && banish(), { once:true });
  window.addEventListener('mousedown', () => armed && banish(), { once:true });
  window.addEventListener('click',    () => armed && banish(), { once:true });
  window.addEventListener('touchstart', () => armed && banish(), { once:true });
  // mousemove: require actual movement (no phantom move)
  window.addEventListener('mousemove', (e) => {
    if (!armed || fired) return;
    if (movedEnough(e)) banish();
  });

  // Clicking the overlay itself also counts (after arming)
  overlay.addEventListener('click', () => armed && banish(), { once:true });
})();
</script>
