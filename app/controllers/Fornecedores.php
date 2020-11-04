<?php
namespace Controllers;

use Models\FornecedoresModel;

class Fornecedores extends Controller{
    public function __construct($router)
    {   
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
       parent::render("fornecedores", "cadastrar", []);
    }

    public function editar($data){
        $id = $data["id"];
        $fornecedores = new FornecedoresModel();
        $dados = $fornecedores->findById($id);
        parent::render("fornecedores", "editar", [
            "nomeFantasia" => $dados->nomeFantasia,
            "razaoSocial" => $dados->razaoSocial,
            "cnpj" => $dados->cpfCnpj,
            "cep" => $dados->cep,
            "ie" => $dados->rgIE,
            "endereco" => $dados->endereco,
            "numero" => $dados->numero,
            "cidade" => $dados->cidade,
            "bairro" => $dados->bairro,
            "estado" => $dados->estado,
            "telefone" => $dados->telefone,
            "email" => $dados->email,
            "obs" => $dados->obs,
            "id" => $id
        ]);
    }

    public function editarSender(){
        $dados = (object) $_POST;
        $fornecedores = (new FornecedoresModel())->findById($dados->id);
        $fornecedores->razaoSocial = $dados->razaoSocial;
        $fornecedores->nomeFantasia = $dados->nomeFantasia;
        $fornecedores->cpfCnpj = $dados->cpfCnpj;
        $fornecedores->rgIE = $dados->rgIE;
        $fornecedores->email = $dados->email;
        $fornecedores->telefone = $dados->telefone;
        $fornecedores->cep = $dados->cep;
        $fornecedores->endereco = $dados->endereco;
        $fornecedores->numero = $dados->numero;
        $fornecedores->bairro = $dados->bairro;
        $fornecedores->estado = $dados->estado;
        $fornecedores->cidade = $dados->cidade;
        $fornecedores->obs = $dados->observacoes;
        $fornecedores->change()->save();
        if($fornecedores->fail()){
            parent::alerta("error", "Erro ao processar requisição", $fornecedores->fail()->getMessage(), "/fornecedores/relacao");
            die();
        }
        parent::alerta("success", "Dados de fornecedor alterados com sucesso!", $dados->nomeFantasia, "/fornecedores/relacao");
    }

    public function excluir($data){
        $id = $data["id"];
        $fornecedores = new FornecedoresModel();
        $dados = $fornecedores->findById($id);
        parent::alertaQuestion("Confirma a exclusão do fornecedor?", $dados->nomeFantasia, "/fornecedores/excluir/sender/$dados->id", "/fornecedores/relacao");
    }

    public function excluirSender($data){
        $id = $data["id"];
        $fornecedores = (new FornecedoresModel())->findById($id);
        $fornecedores->destroy();
        if($fornecedores->fail()){
            parent::alerta("error", "Erro ao processar requisição", $fornecedores->fail()->getMessage(), "/fornecedores/relacao");
            die();
        }
        parent::alerta("success", "Fornecedor excluído com sucesso!", "", "/fornecedores/relacao");
    }

    public function relacao(){
        $fornecedores = new FornecedoresModel();
        $tabela = null;
        $dados = $fornecedores->find()->fetch(true);
        foreach($dados as $d){
            $tabela .= "
                <tr>
                    <td>$d->id</td>
                    <td>$d->nomeFantasia</td>
                    <td>$d->telefone</td>
                    <td>$d->email</td>
                    <td>
                        <a data-role='hint' data-hint-text='Editar' href='/fornecedores/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                        <a data-role='hint' data-hint-text='Dados' href='#' onClick='dados($d->id)'><img class='img-tabela' src='/src/img/detalhes.png'></a>
                        <a data-role='hint' data-hint-text='Excluir' href='/fornecedores/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                    </td>
                </tr>
            ";
        }
        parent::render("fornecedores", "fornecedores", [
            "tabela" => $tabela
        ]);
    }

    public function dados($data){
        $id = $data["id"];
        $fornecedores = new FornecedoresModel();
        $dados = $fornecedores->find("id=:id", "id=$id")->fetch();
        parent::render("fornecedores", "dados", [
            "razaoSocial" => $dados->razaoSocial,
            "nomeFantasia" => $dados->nomeFantasia,
            "cnpj" => $dados->cpfCnpj,
            "ie" => $dados->rgIE,
            "email" => $dados->email,
            "telefone" => $dados->telefone
        ]);
    }

    public function cadastrarSender(){
        var_dump($_POST);
        $dados = (object) $_POST;
        
        $fornecedores = new FornecedoresModel();
        $fornecedores->razaoSocial = $dados->razaoSocial;
        $fornecedores->nomeFantasia = $dados->nomeFantasia;
        $fornecedores->cpfCnpj = $dados->cpfCnpj;
        $fornecedores->rgIE = $dados->rgIE;
        $fornecedores->email = $dados->email;
        $fornecedores->telefone = $dados->telefone;
        $fornecedores->cep = $dados->cep;
        $fornecedores->endereco = $dados->endereco;
        $fornecedores->numero = $dados->numero;
        $fornecedores->bairro = $dados->bairro;
        $fornecedores->estado = $dados->estado;
        $fornecedores->cidade = $dados->cidade;
        $fornecedores->obs = $dados->observacoes;
        $fornecedores->save();
        if($fornecedores->fail()){
            parent::alerta("error", "Erro ao cadastrar o fornecedor!", $fornecedores->fail()->getMessage(), "/fornecedores/cadastrar");
            die();
        }
        parent::alerta("success", "Fornecedor cadastrado com sucesso!", $dados->razaoSocial, "/fornecedores/relacao");
    }
}