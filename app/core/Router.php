<?php
class Router{
  private array $r=['GET'=>[],'POST'=>[]];
  function get($p,$h){$this->map('GET',$p,$h);} function post($p,$h){$this->map('POST',$p,$h);}
  private function map($m,$p,$h){$rx='#^'.preg_replace('#\{[^/]+\}#','([^/]+)',rtrim($p,'/')).'/?$#'; $this->r[$m][]=[$rx,$h];}
  function dispatch(){
    $uri=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)??'/';
    $path='/'.ltrim(preg_replace('#^'.preg_quote(BASE_URL,'#').'#','',$uri),'/');
    $m=$_SERVER['REQUEST_METHOD']??'GET';
    foreach($this->r[$m]??[] as [$rx,$h]) if(preg_match($rx,rtrim($path,'/'),$mm)){array_shift($mm);return $this->invoke($h,$mm);}
    http_response_code(404); include view('partials/head.php'); echo "<div class='min-h-screen grid place-items-center'><p class='text-gray-600'>404 Not Found</p></div>"; include view('partials/footer.php');
  }
  private function invoke($h,$p){ if(is_callable($h)) return call_user_func_array($h,$p); if(is_array($h)){[$c,$m]=$h; $o=new $c; return call_user_func_array([$o,$m],$p);} throw new Exception('bad handler');}
}
