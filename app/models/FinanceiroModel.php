<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class FinanceiroModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('financeiro', [], 'id', true, 'interno');
    }

    public function excluir($id){
        return $this->findById($id)->destroy();
    }

    public function dados($id){
        return $this->findById($id);
    }

    public function quantFinanceiro($cliente){
        return $this->find("cliente=:cliente", "cliente=$cliente")->count();
    }

    public function estorno($id){
        $f = (new FinanceiroModel())->findById($id); 
        $f->status = "EM ABERTO";
        $f->juros = "";
        $f->desconto = "";
        $f->valorPago = "";
        $f->formaPagamento = "";
        $f->coletor = "";
        $f->favorecido = "";
        $f->nossoNumero = "";
        $f->idIntegracao = "";
        $f->codigoBarras = "";
        $f->remessa = "";
        $f->dataPagamento = "";
        $f->change()->save();
        if($f->fail()){
            return $f->fail()->getMessage();
        }else{
            return true;
        }
    }
}