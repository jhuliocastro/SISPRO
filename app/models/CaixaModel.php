<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class CaixaModel extends DataLayer{
    public function __construct(){
        parent::__construct("caixa", [], "id", true, 'interno');
    }

    public function inserir($dados){
        $dados = (object)$dados;
        $this->valor = $dados->valor;
        $this->descricao = $dados->descricao;
        $this->tipo = $dados->tipo;
        return $this->save();
    }

    public function saldoDia($dia){
        $saldo = 0;
        $retorno = $this->find()->fetch(true);
        foreach($retorno as $dados){
            if(date("Y-m-d", strtotime($dados->created_at)) == $dia){
                $dados->valor = str_replace(",", ".", $dados->valor);
                if($dados->tipo == "Entrada"){
                    $saldo = $saldo + $dados->valor;
                }else{
                    $saldo = $saldo - $dados->valor;
                }
            }
        }
        return $saldo;
    }
}