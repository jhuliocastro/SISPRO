<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

//IMPORTACÃO
require_once __DIR__."/../vendor/autoload.php";
require_once "banco.php";
include __DIR__."/../app/controllers/GalaxPay.php";

$query = $conn->prepare("SELECT * FROM status_baixa_titulo");
$query->execute();

$dados = $query->fetch(PDO::FETCH_OBJ);

var_dump($dados);

$query = $conn->prepare("SELECT * FROM financeiro WHERE id=:id");
$query->execute([
    "id" => $dados->titulo
]);

$dadosTitulo = $query->fetch(PDO::FETCH_OBJ);

var_dump($dadosTitulo);

$galax = new \Controllers\GalaxPay();

$retorno = $galax->cancelar($dadosTitulo->idIntegracao);

var_dump($retorno);

$query = $conn->prepare("DELETE FROM status_baixa_titulo WHERE titulo=:titulo");
$retorno = $query->execute([
    "titulo" => $dados->titulo
]);

var_dump($retorno);