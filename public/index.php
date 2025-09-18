<?php
require_once dirname(__DIR__).'/app/config/app.php';
$router = new Router();
require_once dirname(__DIR__).'/routes/web.php';
$router->dispatch();
