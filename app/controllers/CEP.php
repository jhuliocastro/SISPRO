<?php
namespace Controllers;

use FlyingLuscas\Correios\Client;

class CEP{
    public function pesquisar(){
        $correios = new Client();
        $return = $correios->zipcode()->find($_POST["cep"]);
        echo json_encode($return);
    }
}