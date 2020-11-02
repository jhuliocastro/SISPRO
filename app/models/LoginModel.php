<?php
namespace Models;

use Stonks\DataLayer\DataLayer;

class LoginModel extends DataLayer
{
    public function __construct()
    {
        parent::__construct('usuarios', [], 'id', true, 'interno');
    }

    public function pesquisaUsuario(string $usuario, string $senha){
        $query = $this->find("usuario=:usuario AND senha=:senha", "usuario=$usuario&senha=$senha")->count();
        if($this->fail()){
            return $this->fail()->getMessage();
        }else {
            return $query;
        }
    }
}