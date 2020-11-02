<?php
namespace Models;

use Stonks\DataLayer\DataLayer;
use Models\LogModel;

class Radgroupcheck extends DataLayer
{
    public function __construct()
    {
        parent::__construct('radgroupcheck', [], 'id', false, 'radius');
    }
}