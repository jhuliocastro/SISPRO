<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class Radcheck extends DataLayer
{
    public function __construct()
    {
        parent::__construct('radcheck', [], 'id', false, 'radius');
    }
}