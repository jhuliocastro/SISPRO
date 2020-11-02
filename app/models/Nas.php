<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class Nas extends DataLayer{
    public function __construct()
    {
        parent::__construct('nas', [], 'id', false, 'radius');
    }
}