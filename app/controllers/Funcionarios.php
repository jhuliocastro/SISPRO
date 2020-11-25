<?php
namespace Controllers;

use Models\FuncionariosModel;

class Funcionarios extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function relacao(){
        $func = new FuncionariosModel();
        $dados = $func->lista();
        $tabela = null;
        foreach($dados as $d){
            $tabela .= "
            <tr>
                <td>$d->nome</td>
                <td>$d->cargo</td>
                <td>$d->telefone</td>
                <td>$d->email</td>
                <td>
                    <a data-role='hint' data-hint-text='Editar' href='/clientes/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                    <a data-role='hint' data-hint-text='Dados' href='#' onClick='dados($d->id)'><img class='img-tabela' src='/src/img/detalhes.png'></a>
                    <a data-role='hint' data-hint-text='Excluir' href='/clientes/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                </td>
            </tr>
            ";
        }
        parent::render("funcionarios", "funcionarios", [
            "tabela" => $tabela
        ]);
    }

    public function cadastrar(){
        parent::render("funcionarios", "cadastrar", []);
    }

    public function cadastrarSender(){
        $func = new FuncionariosModel();
        $retorno = $func->cadastrar($_POST);
        if($retorno == "ok"){
            parent::alerta("success", "FUNCIONÁRIO CADASTRADO COM SUCESSO", $_POST["nome"], "/funcionarios/relacao");
        }else{
            parent::alerta("error", "ERRO AO CADASTRAR FUNCIONÁRIO", $retorno, "/funcionarios/cadastrar");
        }
    }
}