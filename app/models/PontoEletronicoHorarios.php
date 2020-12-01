<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class PontoEletronicoHorarios extends DataLayer{
    public function __construct()
    {
        parent::__construct('ponto_eletronico_horarios', [], 'id', true, 'interno');
    }
}