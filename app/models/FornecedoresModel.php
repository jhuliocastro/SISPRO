<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class FornecedoresModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('fornecedores', [], 'id', true, 'interno');
    }
}