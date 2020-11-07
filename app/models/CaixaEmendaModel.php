<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class CaixaEmendaModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('caixa_emenda', [], 'id', true, 'interno');
    }

    public function cadastrar($dados){        
        $dados = (object) $dados;

        $contagem = $this->find("identificacao=:i", "i=$dados->identificacao")->count();

        if($contagem > 0){
            return "existe";
            die();
        }

        $this->identificacao = $dados->identificacao;
        $this->longitude = $dados->longitude;
        $this->latitude = $dados->latitude;
        $this->poste = $dados->poste;
        if($this->save() == true){
            return "salvou";
        }else{
            return $this->fail()->getMessage();
        }
        
    }
}