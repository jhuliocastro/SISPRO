<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PostesModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('postes', [], 'id', true, 'interno');
    }
}