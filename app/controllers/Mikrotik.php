<?php
namespace Controllers;

use Controllers\RouterOS;
use RouterOS\Client;
use RouterOS\Query;

class Mikrotik extends Controller
{
    private $ip;
    private $usuario;
    private $senha;

    public function __construct()
    {
        $this->ip = "45.164.81.42";
        $this->usuario = "jhuliocastro";
        $this->senha = "25072018";
    }

    public function pesquisarQueue($parametro, $valor)
    {
        $client = new Client([
            'host' => $this->ip,
            'user' => $this->usuario,
            'pass' => $this->senha
        ]);
        $query = (new Query('/queue/simple/print'))
            ->where($parametro, $valor);
        return $client->query($query)->read();
    }

    public function addQueue($nome, $target, $download, $upload)
    {
        $client = new Client([
            'host' => $this->ip,
            'user' => $this->usuario,
            'pass' => $this->senha
        ]);
        $query = new Query('/queue/simple/add');
        $query->equal('name', $nome);
        $query->equal('target', $target);
        $query->equal("max-limit", "$upload/$download");

        return $client->query($query)->read();
    }

    public function removeQueue($id)
    {
        $client = new Client([
            'host' => $this->ip,
            'user' => $this->usuario,
            'pass' => $this->senha
        ]);
        $query =
            (new Query('/queue/simple/remove'))
                ->equal('.id', $id);
        return $client->query($query)->read();
    }

    public function pesquisarPPPOE($usuario)
    {
        $client = new Client([
            'host' => $this->ip,
            'user' => $this->usuario,
            'pass' => $this->senha
        ]);
        $query = (new Query('/ppp/active/print'))
            ->where("name", $usuario);
        return $client->query($query)->read();
    }

    public function removerPPPOE($id){
        $client = new Client([
            'host' => $this->ip,
            'user' => $this->usuario,
            'pass' => $this->senha
        ]);
        $query =
            (new Query('/ppp/active/remove'))
                ->equal('.id', "$id");

        return $client->query($query)->read();
    }
}