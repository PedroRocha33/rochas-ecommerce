<?php

namespace Source\Web;

class App extends Controller
{
    public function __construct()
    {
        parent::__construct("app");
    }

    public function home (): void
    {
        echo $this->view->render("index", []);
        //echo "Olá, Admin!";
    }

     public function profile (): void
    {
        echo $this->view->render("profile", []);
        //echo "Olá, Admin!";
    }

    public function editprofile (): void
    {
        echo $this->view->render("edit-profile", []);
        //echo "Olá, Admin!";
    }
    // public function clients (): void
    // {
    //     echo $this->view->render("clients", []);
    // }

}