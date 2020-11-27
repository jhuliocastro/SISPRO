<?php
namespace Controllers;

use Models\LoginModel;

class Chat extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function chat(){
        parent::render("chat", "chat", [
        	"usuarios" => self::listaUsuarios()
        ]);
    }

    private static function listaUsuarios(){
    	$usuarios = new LoginModel();
    	$dados = $usuarios->lista();
    	$lista = null;
    	foreach($dados as $d){
    		$lista .= "
    			<li><a onclick='chat($d->nome)' href=\"#\"><span class=\"icon mif-local-service\"></span>$d->nome</a></li>
    		";
    	}
    	return $lista;
    }

    public function exibirChat($remetente, $destinatario){

    }
}