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

    public function relacao(){
        
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