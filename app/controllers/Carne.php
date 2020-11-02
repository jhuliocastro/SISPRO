<?php
namespace Controllers;

use Models\ClientesModel;
use Models\FinanceiroModel;

class Carne extends Controller{

    private $html;
    private $codigoBarras;

    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function selecionar(){
        $clientes = new ClientesModel();
        $dados = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $listaClientes = [];
        foreach($dados as $d){
            $listaClientes[$d->nomeCompleto] = "$d->nomeCompleto";
        }
        parent::render("carne", "selecionar", [
            "opcoes" => json_encode($listaClientes)
        ]);
    }

    public function capa(){
        $cliente = $_POST["cliente"];
        $clientes = new ClientesModel();
        $dadosCliente = $clientes->find("nomeCompleto=:nome", "nome=$cliente")->fetch();
        $temp = file_get_contents(__DIR__."/../views/carne/capa.html");
        $dados = array(
            "cliente" => $dadosCliente->nomeCompleto,
            "endereco" => $dadosCliente->endereco,
            "numero" => $dadosCliente->numero,
            "cidade" => $dadosCliente->cidade,
            "bairro" => $dadosCliente->bairro,
            "estado" => $dadosCliente->estado,
            "cep" => $dadosCliente->cep
        );
        foreach($dados as $indice => $dados){
            $indice = "{{".$indice."}}";
            $temp = str_replace($indice, $dados, $temp);
        }
        var_dump(file_put_contents(__DIR__."/../views/carne/dinamicos/capa.html", $temp));
    }

    public function imprimir(){
        $geral = file_get_contents(__DIR__."/../views/carne/topo.html");
        $dados = explode("&", $_POST["dados"]);
        $titulos = [];
        for($i=0;$i<sizeof($dados);$i++){
            $t = explode("=", $dados[$i]);
            $titulos[] = $t[0];
        }
        $count = 1;
        for($i=0;$i<sizeof($titulos);$i++){
            $financeiro = new FinanceiroModel();
            $clientes = new ClientesModel();
            $dadosTitulo = $financeiro->find("id=:titulo", "titulo=$titulos[$i]")->fetch();
            $dadosCliente = $clientes->find("nomeCompleto=:nome", "nome=$dadosTitulo->cliente")->fetch();
            self::codigo($dadosTitulo->codigoBarras);
            $temp = file_get_contents(__DIR__."/../views/carne/titulo.html");
            $dados = array(
                "nossoNumero" => $dadosTitulo->nossoNumero,
                "codigoBarras" => self::formataCodigoBarras($dadosTitulo->codigoBarras),
                "numeroDocumento" => $dadosTitulo->id,
                "valor" => $dadosTitulo->valor,
                "dataVencimento" => date("d/m/Y", strtotime($dadosTitulo->dataVencimento)),
                "dataDocumento" => date("d/m/Y", strtotime($dadosTitulo->dataEmissao)),
                "dataProcessamento" => date("d/m/Y", strtotime($dadosTitulo->dataEmissao)),
                "agencia" => "1004",
                "codigoCedente" => "0220060",
                "carteira" => "101",
                "nomeCliente" => $dadosCliente->nomeCompleto,
                "cpfCliente" => $dadosCliente->cpf,
                "endereco" => $dadosCliente->endereco,
                "numero" => $dadosCliente->numero,
                "bairro" => $dadosCliente->bairro,
                "estado" => $dadosCliente->estado,
                "cep" => $dadosCliente->cep,
                "sacadoAvalista" => "NOVA NET TELECOM",
                "barcode" => substr($this->codigoBarras, "0"),
                "base" => URL_BASE
            );
            foreach($dados as $indice => $dados){
                $indice = "{{".$indice."}}";
                $temp = str_replace($indice, $dados, $temp);
            }
            $geral .= $temp;
            echo $titulos[$i];
            if($count == 3){
                $geral .= "<div style=\"page-break-after: always\"></div>";
                $count = 1;
            }else{
                $count++;
            }
        }

        var_dump(file_put_contents(__DIR__."/../views/carne/dinamicos/titulo.html", $geral));
    }

    public function gerar($dados){
        $cliente = $dados["cliente"];
        $financeiro = new FinanceiroModel();
        $dados = $financeiro->find("cliente=:cliente", "cliente=$cliente")->order("dataVencimento ASC")->fetch(true);
        $titulos = null;
        foreach($dados as $d){
            $texto = date("d/m/Y", strtotime($d->dataVencimento))." (".$d->descricao.")";
            $titulos .= "<input type=\"checkbox\" data-role=\"checkbox\" name='$d->id' id='$d->id' data-caption=\"$texto\">";
        }
        parent::render("carne", "gerar", [
            "cliente" => $cliente,
            "titulos" => $titulos
        ]);
    }

    private function formataCodigoBarras($codigo){
        $codigoFormatado = null;
        $quantidadeNumeros = strlen($codigo);
        $codigoFormatado = $codigo[0].$codigo[1].$codigo[2].$codigo[3].$codigo[4].".".$codigo[5].$codigo[6].$codigo[7].$codigo[8].
            $codigo[9]." ".$codigo[10].$codigo[11].$codigo[12].$codigo[13].$codigo[14].".".$codigo[15].$codigo[16].$codigo[17].
            $codigo[18].$codigo[19].$codigo[20]." ".$codigo[21].$codigo[22].$codigo[23].$codigo[24].$codigo[25].".".$codigo[26].
            $codigo[27].$codigo[28].$codigo[29].$codigo[30].$codigo[31]." ".$codigo[32]." ".$codigo[33].$codigo[34].$codigo[35].
            $codigo[36].$codigo[37].$codigo[38].$codigo[39].$codigo[40].$codigo[41].$codigo[42].$codigo[43].$codigo[44].$codigo[45].
            $codigo[46];
        return $codigoFormatado;
    }

    private function codigo($codigo){
        $this->codigoBarras = "";
        $fino = 1;
        $largo = 3;
        $altura = 50;

        $barcodes[0] = '00110';
        $barcodes[1] = '10001';
        $barcodes[2] = '01001';
        $barcodes[3] = '11000';
        $barcodes[4] = '00101';
        $barcodes[5] = '10100';
        $barcodes[6] = '01100';
        $barcodes[7] = '00011';
        $barcodes[8] = '10010';
        $barcodes[9] = '01010';

        for($f1 = 9; $f1 >= 0; $f1--){
            for($f2 = 9; $f2 >= 0; $f2--){
                $f = ($f1*10)+$f2;
                $texto = '';
                for($i = 1; $i < 6; $i++){
                    $texto .= substr($barcodes[$f1], ($i-1), 1).substr($barcodes[$f2] ,($i-1), 1);
                }
                $barcodes[$f] = $texto;
            }
        }

        $this->codigoBarras .= '<img src="/src/img/p.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
        $this->codigoBarras .= '<img src="/src/img/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
        $this->codigoBarras .= '<img src="/src/img/p.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
        $this->codigoBarras .= '<img src="/src/img/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';

        $this->codigoBarras .= '<img ';

        $texto = $codigo;

        if((strlen($texto) % 2) <> 0){
            //$texto = '0'.$texto;
        }

        while(strlen($texto) > 0){
            $i = round(substr($texto, 0, 2));
            $texto = substr($texto, strlen($texto)-(strlen($texto)-2), (strlen($texto)-2));

            if(isset($barcodes[$i])){
                $f = $barcodes[$i];
            }

            for($i = 1; $i < 11; $i+=2){
                if(substr($f, ($i-1), 1) == '0'){
                    $f1 = $fino ;
                }else{
                    $f1 = $largo ;
                }

                $this->codigoBarras .= 'src="/src/img/p.gif" width="'.$f1.'" height="'.$altura.'" border="0">';
                $this->codigoBarras .= '<img ';

                if(substr($f, $i, 1) == '0'){
                    $f2 = $fino ;
                }else{
                    $f2 = $largo ;
                }

                $this->codigoBarras .= 'src="/src/img/b.gif" width="'.$f2.'" height="'.$altura.'" border="0">';
                $this->codigoBarras .= '<img ';
            }
        }
        $this->codigoBarras .= 'src="/src/img/p.gif" width="'.$largo.'" height="'.$altura.'" border="0" />';
        $this->codigoBarras .= '<img src="/src/img/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
        $this->codigoBarras .= '<img src="/src/img/p.gif" width="1" height="'.$altura.'" border="0" />';
    }
}