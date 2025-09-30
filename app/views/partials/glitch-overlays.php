<?php
/* glitch overlays for wrong admin PIN
   Only render when explicitly told to (query-string or one-time session flash).
   Usage examples to trigger:
   - $_SESSION['glitch'] = 'banish'; redirect('login');  // will show once
   - /login?glitch=banish
*/
$show = false;
if (isset($_GET['glitch']) && $_GET['glitch'] === 'banish') {
    $show = true;
}
if (isset($_SESSION['glitch']) && $_SESSION['glitch'] === 'banish') {
    $show = true;
    unset($_SESSION['glitch']);  // one-time flash
}

if (!$show) {
    // Do not output anything unless the flag is present
    return;
}
?>
<div class="overlay-center">
  <div class="overlay-text scream">WHO ARE YOU???</div>
</div>
<script>
  // quick kick sequence: flash BEGONE! on any interaction and redirect
  (function(){
    const go = () => {
      const o = document.querySelector('.overlay-center .overlay-text');
      if (o) { o.textContent = 'BEGONE!'; o.classList.add('banish'); }
      setTimeout(()=>{ window.location.href = '<?= url('login') ?>'; }, 700);
    };
    ['mousemove','keydown','click','touchstart'].forEach(evt=>window.addEventListener(evt, go, {once:true}));
    setTimeout(go, 2500); // or auto after short time
  })();
</script>
