<?php
namespace Controllers;

class Fornecedores extends Controller{
    public function __construct($router)
    {   
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
        var_dump("ok");
    }
}