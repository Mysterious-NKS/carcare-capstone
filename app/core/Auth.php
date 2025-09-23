<?php
/**
 * Auth helpers — small and readable on purpose.
 * We store the logged in user in $_SESSION['user'].
 */
class Auth {
  /** return the whole user array or null */
  public static function user(){ return $_SESSION['user'] ?? null; }

  /** return user id or null */
  public static function id(){ return $_SESSION['user']['id'] ?? null; }

  /** true if someone is logged in */
  public static function check(){ return isset($_SESSION['user']); }

  /** stop anonymous users — send them to /login */
  public static function requireAuth(){
    if (!self::check()){
      header("Location: ".url('login')); exit;
    }
  }

  /**
   * stop users without the right role.
   * example: Auth::requireRole('STAFF');  // only staff can pass
   */
  public static function requireRole(string $role){
    self::requireAuth();
    if (($_SESSION['user']['role'] ?? '') !== $role){
      header("Location: ".url('dashboard')); exit;
    }
  }

  /** forget the session — used by /logout */
  public static function logout(){
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
  }
}
