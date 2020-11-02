<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class StatusRemessa extends DataLayer{
    public function __construct(){
        parent::__construct("status_remessa", [], "id", true, 'interno');
    }
}