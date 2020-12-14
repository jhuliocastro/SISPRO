<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ArquivosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('arquivos', [], 'id', true, 'interno');
    }

    public function excluir($arquivo){
        return ($this)->find("arquivo=:arquivo", "arquivo=$arquivo")->destroy();
    }
}