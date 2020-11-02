<?php
namespace Models;

use Models\LogModel;
use Stonks\DataLayer\DataLayer;

class PlanosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('planos', [], 'id', true, 'interno');
    }

    public function cadastrar($dados){
        $dados = (object) $dados;
        $this->descricao = $dados->nome;
        $this->download = $dados->download;
        $this->upload = $dados->upload;
        $this->valor = $dados->valor;
        $retorno = $this->save();
        if($this->fail()){
            $log = new LogModel();
            $log->add($this->fail()->getMessage());
        }
        return $retorno;
    }

    public function dadosID($id){
        return $this->findById($id);
    }

    public function editar($dados){
        $dados = (object)$dados;
        $query = $this->findById($dados->id);
        $query->descricao = $dados->nome;
        $query->download = $dados->download;
        $query->upload = $dados->upload;
        $query->valor = $dados->valor;
        $retorno = $query->change()->save();
        if($query->fail()){
            $log = new LogModel();
            $log->add($query->fail()->getMessage());
        }
        return $retorno;
    }

    public function lista(){
        $retorno = $this->find()->fetch(true);
        if($this->fail()){
            $log = new LogModel();
            $log->add($this->fail()->getMessage());
        }
        return $retorno;
    }

    public function excluir($id){
        $query = ($this)->findById($id);
        if($this->fail()){
            $log = new LogModel();
            $log->add($this->fail()->getMessage());
        }
        return $query->destroy();
    }
}