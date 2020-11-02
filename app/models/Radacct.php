<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class Radacct extends DataLayer
{
    public function __construct()
    {
        parent::__construct('radacct', [], 'id', false, 'radius');
    }
}