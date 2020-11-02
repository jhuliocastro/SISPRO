<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
//IMPORTACÃO
require_once __DIR__."/../vendor/autoload.php";
require_once "banco.php";
include __DIR__."/../app/controllers/GalaxPay.php";

//DADOS REMESSA
$query = $conn->prepare("SELECT idTitulo FROM status_remessa");
$query->execute();
if($query->rowCount() == 0){
    die();
}
$dadosRemessa = $query->fetch(PDO::FETCH_OBJ);

var_dump($dadosRemessa);

//DADOS TITULO
$query = $conn->prepare("SELECT * FROM financeiro WHERE id=:id");
$query->execute([
    "id" => $dadosRemessa->idTitulo
]);
$dadosTitulo = $query->fetch(PDO::FETCH_OBJ);

var_dump($dadosTitulo);

//DADOS CLIENTE
$query = $conn->prepare("SELECT * FROM clientes WHERE nomeCompleto=:nome");
$query->execute([
    "nome" => $dadosTitulo->cliente
]);
$dadosCliente = $query->fetch(PDO::FETCH_OBJ);

var_dump($dadosCliente);

//GALAXPAY
$galax = new \Controllers\GalaxPay();

//PESQUISA O CLIENTE NO GATEWAY
$pesquisarCliente = $galax->pesquisarCliente($dadosCliente->cpf);
var_dump($pesquisarCliente);
if($pesquisarCliente["type"] == false){

    //CADASTRA O CLIENTE
    $cadastrarCliente = $galax->cadastrarCliente($dadosCliente);
    var_dump($cadastrarCliente);
    if($cadastrarCliente["type"] == false){
        $query = $conn->prepare("UPDATE financeiro SET remessa=:remessa WHERE id=:id");
        $query->execute([
            "remessa" => "RECUSADO",
            "id" => $dadosRemessa->idTitulo
        ]);

        $query = $conn->prepare("DELETE FROM status_remessa WHERE idTitulo=:id");
        $query->execute([
            "id" => $dadosRemessa->idTitulo
        ]);
        die();
    }
}

$md5 = md5(uniqid(rand(), true)); #GERA UM HASH MD5

#CADASTRA A COBRANÇA
$cadastrarCobranca = $galax->cadastraCobranca($md5, $dadosCliente->id, $dadosTitulo->valor, date("Y-m-d", strtotime($dadosTitulo->dataVencimento)));
var_dump($cadastrarCobranca);
if($cadastrarCobranca["type"] == false){
    $query = $conn->prepare("UPDATE financeiro SET remessa=:remessa WHERE id=:id");
    $query->execute([
        "remessa" => "RECUSADO",
        "id" => $dadosRemessa->idTitulo
    ]);

    $query = $conn->prepare("DELETE FROM status_remessa WHERE idTitulo=:id");
    $query->execute([
        "id" => $dadosRemessa->idTitulo
    ]);
    die();
}


#PEGA OS DADOS DA COBRANÇA
$dadosCobranca = $galax->dadosCobranca($md5);

#PEGA O LINK DO BOLETO
$boleto = $dadosCobranca["paymentBill"]["transactions"]["0"]["boleto"];

#COPIA O CONTEUDO DO BOLETO
$b = file_get_contents($boleto);

#PEGA OS DADOS DA COBRANÇA
$dadosCobranca = $galax->dadosCobranca($md5);

var_dump($dadosCobranca);

#CADASTRA OS DADOS DO BOLETO NO BANCO DE DADOS
$query = $conn->prepare("UPDATE financeiro SET idIntegracao=:idIntegracao, favorecido=:favorecido, codigoBarras=:codigoBarras, nossoNumero=:nossoNumero, linkBoleto=:linkBoleto, remessa=:remessa WHERE id=:id");
$query->execute([
    "idIntegracao" => $md5,
    "favorecido" => $dadosCobranca["paymentBill"]["infoBoleto"],
    "codigoBarras" => $dadosCobranca["paymentBill"]["transactions"]["0"]["boletoBankLine"],
    "nossoNumero" => $dadosCobranca["paymentBill"]["transactions"]["0"]["boletoBankNumber"],
    "linkBoleto" => $dadosCobranca["paymentBill"]["transactions"]["0"]["boleto"],
    "remessa" => "APROVADO",
    "id" => $dadosRemessa->idTitulo
]);


$query = $conn->prepare("DELETE FROM status_remessa WHERE idTitulo=:id");
$query->execute([
    "id" => $dadosRemessa->idTitulo
]);

var_dump($pesquisarCliente);