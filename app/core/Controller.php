<?php
class Controller{
  protected function render($v,$d=[]){
    extract($d);
    include view('partials/head.php');
    include view($v);
    include view('partials/footer.php');
  }

  protected function redirect($p){
    header('Location: '.url($p)); exit;
  }

  // NEW: Try a list of candidate view paths and render the first that exists.
  // Keeps your current folder layout without breaking older paths.
  protected function renderAny(array $candidates, array $data = []): void {
    $base = dirname(__DIR__) . '/views/';
    foreach ($candidates as $rel) {
      if (is_file($base . $rel)) {
        $this->render($rel, $data);
        return;
      }
    }
    // Fallback to first candidate (preserves old behavior if misconfigured)
    $this->render($candidates[0], $data);
  }
}
