<?php

namespace Source\Web;

class Site extends Controller
{
    public function __construct()
    {
        parent::__construct("web");
    }

    public function home(): void
    {
        //echo "Home Page...";
        echo $this->view->render("index",[]);
    }

    public function checkout(): void
    {
        echo $this->view->render("checkout",[]);
    }

    public function login(): void
    {
        echo $this->view->render("login",[]);
    }

    public function error (array $data): void
    {
        echo "Error {$data["errcode"]}...";
    }

}