<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PostesModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('postes', [], 'id', true, 'interno');
    }

    public function listaIdentificacao(){
        $dados = $this->find()->fetch(true);
        $select = null;
        foreach($dados as $d){
            $select .= "
                <option>$d->identificacao</option>
            ";
        }
        return $select;
    }
}