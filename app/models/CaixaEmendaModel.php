<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class CaixaEmendaModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('caixaEmenda', [], 'id', true, 'interno');
    }
}