<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class RadIpPool extends DataLayer{
    public function __construct()
    {
        parent::__construct('radippool', [], 'id', false, 'radius');
    }
}