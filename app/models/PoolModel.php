<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PoolModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('pool', [], 'id', true, 'interno');
    }
}