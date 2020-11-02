<?php
namespace Controllers;

use Models\LoginModel;

class Login extends Controller
{
    public function __construct($router){
        $this->router = $router;
    }

    public function home(){
        parent::render("login", "login2", []);
    }

    public function login(){
        $usuario = $_POST["usuario"];
        $senha = md5($_POST["senha"]);
        $login = new LoginModel();
        $retorno = $login->pesquisaUsuario($usuario, $senha);

        if(gettype($retorno) == string){
            parent::alerta("error", "ERRO AO PROCESSAR DADOS", $retorno, "/");
        }else if(gettype($retorno) == integer){
            if($retorno == 0){
                parent::alerta("warning", "USUÁRIO OU SENHA INCORRETOS", "VERIRIQUE OS DADOS E TENTE NOVAMENTE", "/");
            }else{
                session_start();
                $_SESSION["usuario"] = $usuario;
                header('Location: /painel');
            }
        }
    }
}