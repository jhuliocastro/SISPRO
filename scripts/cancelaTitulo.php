<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

//IMPORTACÃO
require_once __DIR__."/../vendor/autoload.php";
require_once "banco.php";

$query = $conn->prepare("SELECT * FROM financeiro");
$query->execute();

while ($dados = $query->fetch(PDO::FETCH_OBJ)){
    if($dados->idIntegracao != ""){
        $dataAtual = date('Y-m-d');
        $dataTitulo = date('Y-m-d', strtotime($dados->dataVencimento));
        $diferenca = strtotime($dataTitulo) - strtotime($dataAtual);
        $dias = floor($diferenca / (60 * 60 * 24));
        if($dias < -59){
            if($dados->status != "PAGO"){
                $query1 = $conn->prepare("UPDATE financeiro SET idIntegracao=:idIntegracao, codigoBarras=:codigoBarras, favorecido=:favorecido, nossoNumero=:nossoNumero, linkBoleto=:linkBoleto, remessa=:remessa WHERE id=:id");
                $query1->execute([
                    "idIntegracao" => "",
                    "codigoBarras" => "",
                    "favorecido" => "",
                    "nossoNumero" => "",
                    "linkBoleto" => "",
                    "remessa" => "CANCELADO",
                    "id" => $dados->id
                ]);
                var_dump($query1);
            }
        }
    }
}

echo "CONCLUIDO";