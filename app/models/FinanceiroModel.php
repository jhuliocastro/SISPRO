<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class FinanceiroModel extends DataLayer{
    public function __construct()
    {
        parent::__construct('financeiro', [], 'id', true, 'interno');
    }
}