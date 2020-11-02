<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

//IMPORTACÃO
require_once __DIR__."/../vendor/autoload.php";
require_once "banco.php";
include __DIR__."/../app/controllers/GalaxPay.php";

$query = $conn->prepare("SELECT * FROM financeiro WHERE status=:status AND remessa=:remessa ORDER BY cliente");
$query->execute([
    "status" => "EM ABERTO",
    "remessa" => "APROVADO"
]);
while ($dados = $query->fetch(PDO::FETCH_OBJ)){
    $galax = new \Controllers\GalaxPay();
    $retorno = $galax->statusCobranca($dados->idIntegracao);
    if($retorno["paymentBill"]["statusDescription"] != "Ativa"){
        $query2 = $conn->prepare("INSERT INTO baixas(cliente, titulo, valor) VALUES(:cliente, :titulo, :valor)");
        $query2->execute([
            "cliente" => $dados->cliente,
            "titulo" => $dados->id,
            "valor" => $retorno["paymentBill"]["value"]
        ]);

        $query3 = $conn->prepare("UPDATE financeiro SET status=:status, valorPago=:valorPago, dataPagamento=:data, formaPagamento=:forma WHERE id=:id");
        $query3->execute([
            "status" => "PAGO",
            "valorPago" => $retorno["paymentBill"]["value"],
            "data" => date("Y-m-d"),
            "forma" => "BOLETO",
            "id" => $dados->id
        ]);
    }
    sleep(5);
}