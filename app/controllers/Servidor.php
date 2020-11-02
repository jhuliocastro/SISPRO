<?php
namespace Controllers;

use Models\Nas;

class Servidor extends Controller{

    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $radius = new Nas();
        $dados = $radius->find("id=:id", "id=1")->fetch();
        if($radius->fail()){
            parent::alerta("error", "Erro ao acessar banco de dados", $radius->fail()->getMessage(), "");
        }
        parent::render("servidor", "dados", [
            "ip" => $dados->nasname,
            "nome" => $dados->shortname,
            "maxClientes" => $dados->maxclientes,
            "megas" => $dados->mb_instalados,
            "secret" => $dados->secret,
            "community" => $dados->community,
            "cep" => $dados->cep,
            "endereco" => $dados->endereco,
            "numero" => $dados->numero,
            "bairro" => $dados->bairro,
            "cidade" => $dados->cidade,
            "estado" => $dados->estado,
            "complemento" => $dados->complemento,
            "portaSSH" => $dados->portassh,
            "id" => $dados->id,
            "senha" => $dados->senha
        ]);
    }

    public function alterar(){
        $dados = (object)$_POST;
        $radius = (new Nas())->findById(1);
        $radius->shortname = $dados->nome;
        $radius->nasname = $dados->ipMK;
        $radius->maxclientes = $dados->maxClientes;
        $radius->mb_instalados = $dados->megas;
        $radius->secret = $dados->secretRadius;
        $radius->community = $dados->community;
        $radius->portassh = $dados->portaSSH;
        $radius->endereco = $dados->endereco;
        $radius->numero = $dados->numero;
        $radius->cidade = $dados->cidade;
        $radius->estado = $dados->estado;
        $radius->complemento = $dados->complemento;
        $radius->cep = $dados->cep;
        $radius->senha = $dados->senhaSSH;
        if($radius->change()->save() == true){
            parent::alerta("success", "Dados do servidor salvos com sucesso!", "", "/servidor/dados");
        }else{
            parent::alerta("error", "Erro ao processar requisição", $radius->fail()->getMessage(), "/servidor/dados");
        }
    }
}