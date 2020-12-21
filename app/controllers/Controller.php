<?php
namespace Controllers;

use Models\LoginModel;
use Models\ClientesModel;

class Controller
{
    public function __construct()
    {
        self::usuarioAtivo();
    }

    public static function usuarioAtivo(){
        session_start();
        if(!isset($_SESSION["usuario"])){
            header('Location: /');
        }
    }

    public function alerta(string $tipo, string $titulo, string $mensagem, string $acao){
        $this->render("", "alerta", array(
            "tipo" => $tipo,
            "titulo" => $titulo,
            "mensagem" => $mensagem,
            "url" => $acao
        ));
    }

    public function erros(){
        ini_set('display_errors',1);
        ini_set('display_startup_erros',1);
        error_reporting(E_ALL);
    }

    public function alertaQuestion(string $titulo, string $mensagem, string $acaoSim, string $acaoNao){
        $this->render("", "alerta2", array(
            "titulo" => $titulo,
            "mensagem" => $mensagem,
            "urlSim" => $acaoSim,
            "urlNao" => $acaoNao
        ));
    }

    public function md5Ale(){
        return md5(uniqid(rand(), true));
    }

    public function listaNomeClientes(){
        $clientes = new ClientesModel();
        $dadosClientes = $clientes->lista();
        $lista = null;
        foreach($dadosClientes as $dados){
            $lista .= "<option>$dados->nomeCompleto</option>";
        }
        return $lista;
    }

    public function render($bloco, $pagina, $dados){
        //PEGA O CONTEUDO DOS ARQUIVOS NECESSÁRIOS
        $url = __DIR__."/../views/".$bloco."/".$pagina.".html";
        $urlNavbar = __DIR__."/../views/navbar.html";
        $urlMenu = __DIR__ . "/../views/menu.html";
        $conteudo = file_get_contents($url);
        $navbar = file_get_contents($urlNavbar);
        $menu = file_get_contents($urlMenu);
        foreach($dados as $indice => $valor){//SUBSTITUI OS DADOS NECESSÁRIOS REQUERIDOS PELO USUÁRIO
            $indice = "{{".$indice."}}";
            $conteudo = str_replace($indice, $valor, $conteudo);
        }
        //$login = new Login_Model();
        //$dadosUsuario = $login->dadosUsuario();
        $dadosFixos = array(//ARRAY COM DADOS GERAIS PARA SUBSTITUICAO
            "empresa" => NOME_EMPRESA,
            "base" => URL_BASE,
            "imagens" => URL_IMAGES,
            "navbar" => $navbar,
            "menu" => $menu,
            "versao" => VERSAO,
            "copyright" => COPYRIGHT,
            //"nomeCompleto" => $dadosUsuario->nome_completo,
            //"usuario" => $dadosUsuario->nomeAbreviado,
            //"imagemUsuario" => $dadosUsuario->foto,
            "clientesNome" => self::listaNomeClientes(),
            //"ips" => self::listaIPsDisponivel(),
            //"planos" => self::listaPlanos()
        );
        foreach($dadosFixos as $indice => $valor){
            $indice = "{{".$indice."}}";
            $menu = str_replace($indice, $valor, $menu);
        }
        foreach($dadosFixos as $indice => $valor){
            $indice = "{{".$indice."}}";
            $conteudo = str_replace($indice, $valor, $conteudo);
        }
        echo $conteudo;//EXIBE A VIEW
    }
}