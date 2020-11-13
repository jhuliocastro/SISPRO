<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ClientesModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('clientes', [], 'id', true, 'interno');
    }

    public function lista(){
        return $this->find()->order("nomeCompleto ASC")->fetch(true);
    }

    public function bloqueados(){
        return $this->find("bloqueado=:bloqueado", "bloqueado=1")->order("nomeCompleto ASC")->fetch(true);
    }

    public function ativados(){
        return $this->find("ativo=:ativo", "ativo=1")->order("nomeCompleto ASC")->fetch(true);
    }

    public function dadosNome($cliente){
        return $this->find("nomeCompleto=:nome", "nome=$cliente")->fetch();
    }

    public function dadosID($id){
        return $this->find("id=:id", "id=$id")->fetch();
    }
}