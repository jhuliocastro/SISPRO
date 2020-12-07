<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PontoModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('ponto', [], 'id', true, 'interno');
    }

    public function pontosPorFuncionario($funcionario){
        return $this->find("funcionario=:funcionario", "funcionario=$funcionario")->fetch(true);
    }

    public function cadastrar($dados){
        $dados = (object) $dados;
        $this->funcionario = $dados->funcionario;
        $this->usuarioResponsavel = $dados->usuarioResponsavel;
        $this->obs = $dados->motivoAtraso;
        $this->es = $dados->es;
        $this->save();
        if($this->fail()){
            return $this->fail()->getMessage();
        }else{
            return "ok";
        }
    }
}