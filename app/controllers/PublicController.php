<?php

class PublicController extends Controller
{
    // GET /
    public function home()
    {
        $this->render('public/home.php');
    }

    // GET /about
    public function about()
    {
        $this->render('public/about.php');
    }

    // GET /contact
    public function contact()
    {
        $this->render('public/contact.php');
    }
}
