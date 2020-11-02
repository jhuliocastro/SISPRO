<?php
namespace Models;

use Stonks\DataLayer\DataLayer;
use Models\LogModel;

class Radgroupreply extends DataLayer
{
    public function __construct()
    {
        parent::__construct('radgroupreply', [], 'id', false, 'radius');
    }

    public function cadastrar($dados){
        $dados = (object)$dados;
        $valor = $dados->upload."k/".$dados->download."k 0/0 0/0 0/0 8 0/0";
        $this->groupname = $dados->nome;
        $this->attribute = "Mikrotik-Rate-Limit";
        $this->op = "=";
        $this->value = $valor;
        $query = $this->save();
        if($this->fail()){
            $log = new LogModel();
            $log->add($this->fail()->getMessage());
        }
        return $query;
    }

    public function excluir($plano){
        $query = $this->find("groupname=:nome", "nome=$plano")->fetch();
        $retorno = $query->destroy();
        if($query->fail()){
            $log = new LogModel();
            $log->add($query->fail()->getMessage());
        }
        return $retorno;
    }
}