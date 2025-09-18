<?php
class Auth{ static function user(){return $_SESSION['user']??null;} static function check(){return isset($_SESSION['user']);}
static function logout(){$_SESSION=[];session_destroy();}
static function requireAuth(){ if(!self::check()){ header('Location: '.url('login')); exit; } } }
