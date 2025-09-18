<?php class CustomerController extends Controller{ function dashboard(){ Auth::requireAuth(); $name=Auth::user()['name']??'friend'; $this->render('customer/dashboard.php',['name'=>$name]); } }
