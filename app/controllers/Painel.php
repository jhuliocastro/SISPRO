<?php
namespace Controllers;

use Models\ClientesModel;

class Painel extends Controller
{
    public function home(){
        $clientes = new ClientesModel();
        $ativados = $clientes->find("ativo=:ativo", "ativo=1")->count();
        $bloqueados = $clientes->find("bloqueado=:bloqueado", "bloqueado=1")->count();
        $desativados = $clientes->find("desativado=:desativado", "desativado=1")->count();
        parent::render("", "painel", [
            "ativados" => $ativados,
            "bloqueados" => $bloqueados,
            "desativados" => $desativados
        ]);
    }
}