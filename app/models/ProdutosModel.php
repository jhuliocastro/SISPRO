<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ProdutosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('produtos', [], 'id', true, 'interno');
    }
}