<?php
class AuthController extends Controller{
  function showLogin(){ $this->render('public/login.php'); }
  function showRegister(){ $this->render('public/register.php'); }
  function login(){ $_SESSION['user']=['name'=>($_POST['name']??'Customer'),'role'=>'CUSTOMER']; $this->redirect('dashboard'); }
  function register(){ $_SESSION['user']=['name'=>($_POST['name']??'New User'),'role'=>'CUSTOMER']; $this->redirect('dashboard'); }
  function logout(){ Auth::logout(); $this->redirect(''); }
}
