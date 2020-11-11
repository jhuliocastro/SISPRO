<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class HistoricoEstoqueModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('historico_estoque', [], 'id', true, 'interno');
    }

    public function cadastrar($produto, $descricao, $tipo, $quantidade){
        $this->descricao = $descricao;
        $this->tipo = $tipo;
        $this->produto = $produto;
        $this->quantidade = $quantidade;
        $this->save();
        if($this->fail()){
            return $this->fail()->getMessage();
        }else{
            return "ok";
        }
    }

    public function lista(){
        return $this->find()->fetch(true);
    }
}