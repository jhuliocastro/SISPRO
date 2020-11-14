<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class DiagramasSplitter extends DataLayer{
    public function __construct()
    {
        parent::__construct('diagramas_splitter', [], 'id', true, 'interno');
    }

    public function splitter($dispositivo){
        return $this->find("dispositivo=:d", "d=$dispositivo")->fetch();
    }
}