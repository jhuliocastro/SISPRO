<?php
namespace Controllers;

use Models\DiagramasSplitter;

class Diagramas{
    private $dispositivo;

    public function __construct($dispositivo){
        $this->dispositivo = $dispositivo;
    }

    public function backboneExiste(){

    }

    public function numeroSplitter(){
        $splitter = new DiagramasSplitter();
        return $splitter->find("dispositivo=:d", "d=$this->dispositivo")->count();
    }

    public function splitter(){
        $splitter = new DiagramasSplitter();
        return $splitter->find("dispositivo=:d", "d=$this->dispositivo")->fetch(true);
    }

    public function splitterCadastrar(){
        var_dump($_POST);
    }
}