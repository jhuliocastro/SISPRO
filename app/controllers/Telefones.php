<?php
namespace Controllers;

use Models\TelefonesModel;
use Models\ClientesModel;

class Telefones extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $telefones = new TelefonesModel();
        $dados = $telefones->find()->order("cliente ASC")->fetch(true);
        $tabela = null;
        foreach ($dados as $d){
            $tabela .= "
                <tr>
                    <td>$d->id</td>
                    <td>$d->cliente</td>
                    <td>$d->telefone</td>
                    <td>
                        <a data-role='hint' data-hint-text='Excluir' href='/telefones/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                    </td>
                </tr>
            ";
        }
        parent::render("telefones", "telefones", [
            "tabela" => $tabela
        ]);
    }

    public function excluir($data){
        parent::alertaQuestion("Deseja realmente excluir este contato?", "", "/telefones/excluir/sender/$data[id]", "/telefones");
    }

    public function excluirSender($data){
        $telefones = (new TelefonesModel())->findById($data["id"]);
        $telefones->destroy();
        if($telefones->fail()){
            parent::alerta("error", "Erro ao processar requisição", $telefones->fail()->getMessage(), "/telefones/add");
            die();
        }
        parent::alerta("success", "Telefone Excluído com Sucesso!", "", "/telefones");
    }

    public function add(){
        $clientes = new ClientesModel();
        $dadosClientes = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $opcoes = null;
        foreach($dadosClientes as $d){
            $opcoes .= "
                <option>$d->nomeCompleto</option>
            ";
        }
        parent::render("telefones", "cadastrar", [
            "clientes" => $opcoes
        ]);
    }

    public function cadastrar(){
        $telefones = new TelefonesModel();
        $telefones->cliente = $_POST["cliente"];
        $telefones->telefone = $_POST["telefone"];
        $telefones->save();
        if($telefones->fail()){
            parent::alerta("error", "Erro ao processar requisição", $telefones->fail()->getMessage(), "/telefones/add");
            die();
        }
        parent::alerta("success", "Telefone cadastrado com sucesso!", "", "/telefones");
    }
}