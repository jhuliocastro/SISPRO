<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class BaixasModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('baixas', [], 'id', true, 'interno');
    }
}