<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class Radusergroup extends DataLayer
{
    public function __construct()
    {
        parent::__construct('radusergroup', [], 'username', false, 'radius');
    }
}