<?php
namespace Controllers;

class Sair extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
    }

    public function sair(){
        session_start();
        unset($_SESSION["usuario"]);
        $this->router->redirect("/");
    }
}