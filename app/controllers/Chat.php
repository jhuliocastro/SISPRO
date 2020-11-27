<?php
namespace Controllers;

class Chat extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function chat(){
        parent::render("chat", "chat", []);
    }
}