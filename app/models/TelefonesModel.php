<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class TelefonesModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('telefones', [], 'id', true, 'interno');
    }
}