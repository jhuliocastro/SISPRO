<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ProdutosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('produtos', [], 'id', true, 'interno');
    }

    public function existe($nome){
        return $this->find("nome=:nome", "nome=$nome")->count();
    }

    public function lista(){
        return $this->find()->fetch(true);
    }

    public function dados($id){
        return $this->findById($id);
    }

    public function dadosNome($nome){
        return $this->find("nome=:nome", "nome=$nome")->fetch();
    }

    public function atualizaValor($produto, $valor){
        $query = ($this)->find("nome=:nome", "nome=$produto")->fetch();
        $query->valorAntigo = $valor;
        $query->change()->save();
        if($query->fail()){
            return $query->fail()->getMessage();
        }else{
            return "ok";
        }
    }

    public function atualizaQuantidade($produto, $quantidadeNova, $quantidadeAtual){
        $quantidade = $quantidadeAtual + $quantidadeNova;
        $query = ($this)->find("nome=:nome", "nome=$produto")->fetch();
        $query->quantidade = $quantidade;
        $query->change()->save();
        if($query->fail()){
            return $query->fail()->getMessage();
        }else{
            return "ok";
        }
    }

    public function editar($dados){
        $dados = (object) $dados;
        $query = ($this)->findById($dados->id);
        $query->nome = $dados->nome;
        $query->valorAtual = $dados->valor;
        $query->quantidade = $dados->quantidade;
        $query->quantidadeMinima = $dados->quantidadeMinima;
        $query->descricao = $dados->descricao;
        $query->change()->save();
        if($query->fail()){
            return $query->fail()->getMessage();
        }else{
            return "ok";
        }
    }

    public function excluir($id){
        $retorno = ($this)->findById($id);
        $retorno->destroy();
        if($retorno->fail()){
            return $retorno->fail()->getMessage();
        }else{
            return "ok";
        }
    }

    public function cadastrar($dados){
        $dados = (object) $dados;
        $this->nome = $dados->nome;
        $this->quantidade = $dados->quantidade;
        $this->quantidadeMinima = $dados->quantidadeMinima;
        $this->valorAtual = $dados->valor;
        $this->descricao = $dados->descricao;
        if($this->save() == true){
            return "ok";
        }else{
            return $this->fail()->getMessage();
        }
    }
}