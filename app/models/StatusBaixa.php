<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class StatusBaixa extends DataLayer{
    public function __construct(){
        parent::__construct("status_baixa_titulo", [], "id", true, 'interno');
    }
}