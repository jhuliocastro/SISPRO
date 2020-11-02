<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ContratosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('contratos', [], 'id', true, 'interno');
    }
}