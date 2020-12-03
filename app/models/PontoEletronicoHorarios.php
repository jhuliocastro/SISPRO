<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PontoEletronicoHorarios extends DataLayer{
    public function __construct()
    {
        parent::__construct('ponto_eletronico_horarios', [], 'id', true, 'interno');
    }

    public function existe($funcionario){
        return $this->find("funcionario=:funcionario", "funcionario=$funcionario")->count();
    }

    public function cadastrar($funcionario){
        $this->funcionario = $funcionario;
        $this->save();
        if($this->fail()){
            return $this->fail()->getMessage();
        }else{
            return true;
        }
    }
}