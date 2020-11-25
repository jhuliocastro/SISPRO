<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class FuncionariosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('funcionarios', [], 'id', true, 'interno');
    }

    public function lista(){
        return $this->find()->fetch(true);
    }

    public function cadastrar($dados){
        $dados = (object) $dados;
        $this->nome = $dados->nome;
        $this->dataNascimento = $dados->dataNascimento;
        $this->cpf = $dados->cpf;
        $this->rg = $dados->rg;
        $this->telefone = $dados->telefone;
        $this->cep = $dados->cep;
        $this->email = $dados->email;
        $this->sexo = $dados->sexo;
        $this->endereco = $dados->endereco;
        $this->numero = $dados->numero;
        $this->bairro = $dados->bairro;
        $this->cidade = $dados->cidade;
        $this->estado = $dados->estado;
        $this->dataAdmissao = $dados->dataAdmissao;
        $this->cargo = $dados->cargo;
        $this->save();
        if($this->fail()){
            return $this->fail()->getMessage();
        }else{
            return "ok";
        }
    }
}