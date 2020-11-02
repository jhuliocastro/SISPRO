<?php
namespace Controllers;

use Models\Radacct;

class ConexoesAcesso extends Controller{
    public function __construct($router)
    {   
        $this->router = $router;
        parent::__construct();
    }

    public function conexoes(){
        $lista = $this->lista();
        $tabela = null;
        foreach($lista as $d){
            $tabela .= "
                <tr>
                    <td>$d[id]</td>
                    <td>$d[usuario]</td>
                    <td>$d[horaInicial]</td>
                    <td>$d[dataFinal]</td>
                    <td>$d[trafego]</td>
                </tr>
            ";
        }
        parent::render("relatorios", "conexoes", [
            "tabela" => $tabela
        ]);
    }

    private static function By2M($size){
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    public function lista(){
        $conexoes = new Radacct();
        $listaConexoes = $conexoes->find()->order("radacctid DESC")->fetch(true);
        $tabela = null;
        foreach($listaConexoes as $dados){
            $arrayPro = null;
            $arrayPro["id"] = $dados->radacctid;
            $arrayPro["usuario"] = $dados->username;
            $dataInicial = date("d/m/Y H:i:s", strtotime($dados->acctstarttime));
            $arrayPro["horaInicial"] = $dataInicial;
            $dataFinal = date("d/m/Y H:i:s", strtotime($dados->acctstoptime));
            if($dados->acctstoptime == ""){
                $arrayPro["dataFinal"] = "";
            }else{
                $arrayPro["dataFinal"] = $dataFinal;
            }
            $download = self::By2M($dados->acctoutputoctets);
            $upload = self::By2M($dados->acctinputoctets);
            $arrayPro["trafego"] = "$upload / $download";
            $tabela[] = $arrayPro;
        }
        return $tabela;
    }
}