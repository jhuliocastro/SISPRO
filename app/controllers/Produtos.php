<?php
namespace Controllers;

use Models\ProdutosModel;

class Produtos extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
        parent::render("produtos", "cadastrar", []);
    }
}