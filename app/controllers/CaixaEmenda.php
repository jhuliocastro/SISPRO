<?php
namespace Controllers;

use Models\CaixaEmendaModel;

class CaixaEmenda extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
        parent::render("caixaEmenda", "cadastrar", []);
    }
}