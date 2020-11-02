<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class ArquivosModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('arquivos', [], 'id', true, 'interno');
    }
}