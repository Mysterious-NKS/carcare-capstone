<?php
class Controller{ protected function render($v,$d=[]){extract($d); include view('partials/head.php'); include view($v); include view('partials/footer.php');}
protected function redirect($p){ header('Location: '.url($p)); exit; } }
