<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PontoEletronicoHorarios extends DataLayer{
    public function __construct()
    {
        parent::__construct('ponto_eletronico_horarios', [], 'id', true, 'interno');
    }

    public function alterar($dados){
        $dados = (object) $dados;
        $alterar = ($this)->find("funcionario=:funcionario", "funcionario=$dados->funcionario")->fetch();
        $alterar->horaChegada = $dados->horaChegada;
        $alterar->horaSaida = $dados->horaSaida;
        $alterar->horaAlmocoEntrada = $dados->horaChegadaAlmoco;
        $alterar->horaAlmocoSaida = $dados->horaSaidaAlmoco;
        $alterar->change()->save();
        if($alterar->fail()){
            return $alterar->fail()->getMessage();
        }else{
            return "ok";
        }
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

    public function horarios($funcionario){
        return $this->find("funcionario=:funcionario", "funcionario=$funcionario")->fetch();
    }
}