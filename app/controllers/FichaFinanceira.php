<?php
namespace Controllers;

use Models\ClientesModel;
use Models\FinanceiroModel;
use Controllers\GalaxPay;
use DateTime;
use Models\StatusRemessa;
use Models\StatusBaixa;

class FichaFinanceira extends Controller{

    private $codigoBarras;

    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function faturasAtrasadas(){
        $financeiro = new FinanceiroModel();
        $dados = $financeiro->find("status=:status", "status=EM ABERTO")->fetch(true);
        $tabela = null;
        $valorTotal = 0;
        foreach($dados as $d){
            $dataHoje = date("Y-m-d");
            if(strtotime($d->dataVencimento) < strtotime($dataHoje)){
                $valorTotal = $valorTotal + $d->valor;
                $valor = "R$ ".$d->valor;
                $valor = str_replace(".", ",", $valor);
                $vencimento = date("d/m/Y", strtotime($d->dataVencimento));
                $tabela .= "
                <tr>
                    <td>$d->id</td>
                    <td>$d->cliente</td>
                    <td>$vencimento</td>
                    <td>$d->descricao</td>
                    <td>$valor</td>
                </tr>
            ";
            }
        }
        parent::render("relatorios", "faturasAtrasadas", [
            "tabela" => $tabela,
            "valorTotal" => $valorTotal
        ]);
    }

    public function receber($dados){
        $financeiro = new FinanceiroModel();
        #dados do titulo
        $dadosTitulo = $financeiro->findById($dados["id"]);
        #calculo de multa e juros
        $dadosMulta = self::multa_juros($dadosTitulo->dataVencimento, $dadosTitulo->valor);

        parent::render("fichaFinanceira", "receber", [
            "cliente" => $dadosTitulo->cliente,
            "dataVencimento" => date("d/m/Y", strtotime($dadosTitulo->dataVencimento)),
            "tipo" => $dadosTitulo->descricao,
            "valor" => $dadosTitulo->valor,
            "mora" => $dadosMulta->mora,
            "valorTotal" => $dadosMulta->valorTotal,
            "id" => $dados["id"]
        ]);
    }

    public function receberSender(){
        ini_set('display_errors',1);
        ini_set('display_startup_erros',1);
        error_reporting(E_ALL);
        session_start();
        $dados = (object) $_POST;

        $dados->juros = str_replace(",", ".", $dados->juros);
        $dados->valor = str_replace(",", ".", $dados->valor);
        $dados->desconto = str_replace(",", ".", $dados->desconto);

        $financeiro = (new FinanceiroModel())->findById($dados->id);
        $financeiro->juros = $dados->juros;
        $financeiro->valorPago = $dados->valor;
        $financeiro->desconto = $dados->desconto;
        $financeiro->coletor = $_SESSION["usuario"];
        $financeiro->formaPagamento = $dados->modoPagamento;
        $financeiro->dataPagamento = $dados->dataRecebimento;
        $financeiro->status = "PAGO";
        $financeiro->numeroCartao = $dados->numeroCartao;
        $financeiro->bandeiraCartao = $dados->bandeiraCartao;
        $financeiro->change()->save();
        if($financeiro->fail()){
            parent::alerta("error", "Erro ao processar requisição", $financeiro->fail()->getMessage(), "/ficha/cliente/$dados->cliente");
            die();
        }

        $dadosTitulo = (new FinanceiroModel())->findById($dados->id);
        if($dadosTitulo->nossoNumero != ""){
            $baixa = new StatusBaixa();
            $baixa->titulo = $dados->id;
            $baixa->save();
            if($baixa->fail()){
                parent::alerta("error", "Erro ao processar requisição", $baixa->fail()->getMessage(), "/ficha/cliente/$dados->cliente");
                die();
            }
        }

        parent::alerta("success", "Título recebido com sucesso!", $dados->cliente, "/ficha/cliente/$dados->cliente");
    }

    private function multa_juros($dataVencimento, $valor){
        $dateStart = new \DateTime($dataVencimento);
        $dateNow   = new \DateTime(date('Y-m-d'));
        if($dataVencimento > date("Y-m-d")){
            $array = [
                "mora" => "0.00",
                "valorTotal" => $valor
            ];
        }else{
            $diasAtraso = $dateStart->diff($dateNow);

            $multa = (2 / 100) * $valor;

            $juros = ($valor * (0.03333333 * $diasAtraso->days)) / 100;

            $mora = $juros + $multa;
            $mora = number_format($mora, 2, '.', '');

            $valorTotal = $valor + $mora;
            $valorTotal = number_format($valorTotal, 2, '.', '');

            $array = [
                "mora" => $mora,
                "valorTotal" => $valorTotal
            ];
        }

        $array = (object) $array;

        return $array;
    }

    public function recibo($dados){
        $titulo = $dados["id"];
        $financeiro = new FinanceiroModel();
        $dadosTitulo = $financeiro->findById($titulo);
        $clientes = new ClientesModel();
        $dadosCliente = $clientes->find("nomeCompleto=:nome", "nome=$dadosTitulo->cliente")->fetch();
        $recibo = file_get_contents(__DIR__."/../views/fichaFinanceira/recibo.html");
        if($dadosTitulo->juros == ""){
          $juros = "R$ 0,00";
        }else{
          $juros = "R$ ".$dadosTitulo->juros;
        }
        if($dadosTitulo->desconto == ""){
            $desconto = "R$ 0,00";
        }else{
            $desconto = "R$ ".$dadosTitulo->desconto;
        }
        if($dadosTitulo->descricao == "MENSALIDADE"){
            $mensalidade = "R$ ".$dadosTitulo->valor;
            $servicos = "R$ 0,00";
            $outros = "R$ 0,00";
        }else if($dadosTitulo->descricao == "SERVIÇOS"){
            $mensalidade = "R$ 0,00";
            $outros = "R$ 0,00";
            $servicos = "R$ ".$dadosTitulo->valor;
        }else if($dadosTitulo->descricao == "OUTROS"){
            $mensalidade = "R$ 0,00";
            $servicos = "R$ 0,00";
            $outros = "R$ ".$dadosTitulo->valor;
        }
        $dados = array(
          "valorFinal" => "R$ ".str_replace(".", ",", $dadosTitulo->valorPago),
          "cobranca" => $dadosTitulo->id,
          "vencimento" => date("d/m/Y", strtotime($dadosTitulo->dataVencimento)),
          "juros" => $juros,
          "desconto" => $desconto,
          "mensalidade" => $mensalidade,
          "servicos" => $servicos,
          "outros" => $outros,
          "formaPagamento" => $dadosTitulo->formaPagamento,
          "cpf" => $dadosCliente->cpf,
          "nomeCliente" => $dadosTitulo->cliente,
          "dataPagamento" => date("d/m/Y", strtotime($dadosTitulo->dataPagamento))
        );
        foreach($dados as $indice => $dados){
          $indice = "{{".$indice."}}";
          $recibo = str_replace($indice, $dados, $recibo);
        }
        if(file_put_contents(__DIR__."/../views/fichaFinanceira/dinamicos/recibo.html", $recibo) == false){
            parent::alerta("error", "Erro ao gerar recibo", "Entre em contato com o administrador", "/ficha/cliente/$dadosTitulo->cliente");
            die();
        }
        echo '<script>window.open("/app/views/fichaFinanceira/dinamicos/recibo.html", "janela2","width=800,height=600, directories=no, location=no, menubar=no, scrollbars=no, status=no, toolbar=no, resizable=no");</script>';
        echo "<script>window.location.href='/ficha/cliente/$dadosTitulo->cliente'</script>";
        //$this->router->redirect("/ficha/cliente/$dadosTitulo->cliente");
    }

    public function selecionar(){
        $clientes = new ClientesModel();
        $dados = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $listaClientes = [];
        foreach($dados as $d){
            $listaClientes[$d->nomeCompleto] = "$d->nomeCompleto";
        }
        parent::render("fichaFinanceira", "selecionar", [
            "opcoes" => json_encode($listaClientes)
        ]);
    }

    public function cadastrarTitulos(){
        $clientes = new ClientesModel();
        $clientes2 = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $listaClientes = null;
        foreach($clientes2 as $d){
            $listaClientes .= "
                <option>$d->nomeCompleto</option>
            ";
        }
        parent::render("fichaFinanceira", "cadastrarTitulos", [
            "clientes" => $listaClientes
        ]);
    }

    public function cadastrarTitulosSender(){
        var_dump($_POST);
        $data = $_POST["dataVencimento"];
        $valor = str_replace(",", ".", $_POST["valor"]);
        $diaOrig = date("d", strtotime($data));  
        for($i=0;$i<$_POST["quantidadeParcelas"];$i++){
            $ano = date("Y", strtotime($data));
            $mes = date("m", strtotime($data));
            $dia = date("d", strtotime($data));            

            session_start();

            $financeiro = new FinanceiroModel();
            $financeiro->cliente = $_POST["cliente"];
            $financeiro->valor = $valor;
            $financeiro->status = "EM ABERTO";
            $financeiro->dataVencimento = $data;
            $financeiro->contaBancaria = "1";
            $financeiro->descricao = $_POST["tipo"];
            $financeiro->gerador = $_SESSION["usuario"];
            $financeiro->save();
            if($financeiro->fail()){
                parent::alerta("error", "Erro ao processar requisição!", $financeiro->fail()->getMessage(), "/ficha/cadastrar/titulos");
                die();
            }

            $dt = new DateTime();
            $dt->setDate($ano, $mes, $dia);
            $data2 = $dt->format('Y-m-d');            
            $day = $dt->format('j');
            $dt->modify('first day of +1 month');
            $dt->modify('+' . (min($day, $dt->format('t')) - 1) . ' days');
            $data = $dt->format('Y-m-d');

            parent::alerta("success", "Títulos cadastrados com sucesso!", "", "/ficha/cliente/$_POST[cliente]");
        }
    }

    public function enviarRemessa($data){
        $data = (object) $data;
        $remessa = new StatusRemessa();
        $remessa->idTitulo = $data->id;
        $remessa->save();
        if($remessa->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $remessa->fail()->getMessage(), "/ficha/cliente/$data->cliente");
            die();
        }
        $financeiro = (new FinanceiroModel())->findById($data->id);
        $financeiro->remessa = "ENVIADO";
        $financeiro->change()->save();
        if($financeiro->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $financeiro->fail()->getMessage(), "/ficha/cliente/$data->cliente");
            die();
        }
        parent::alerta("success", "Remessa enviada com sucesso!", "Aguarde alguns minutos e verifique o status do título", "/ficha/cliente/$data->cliente");
        /*
        $financeiro = new FinanceiroModel();
        $dadosTitulo = $financeiro->findById($data["id"]);
        $clientes = new ClientesModel();
        $dadosCliente = $clientes->find("nomeCompleto=:cliente", "cliente=$dadosTitulo->cliente")->fetch();
        $galax = new GalaxPay();
        $pesquisarCliente = $galax->pesquisarCliente($dadosCliente->cpf);
        if($pesquisarCliente["type"] == false){
            $cadastrarCliente = $galax->cadastrarCliente($dadosCliente);
            if($cadastrarCliente["type"] == false){
                parent::alerta("error", "Erro ao processar requisição!", $cadastrarCliente["message"], "/ficha/cliente/$dadosCliente->nomeCompleto");
                die();
            }
        }
        $md5 = parent::md5Ale();
        $cadastrarCobranca = $galax->cadastraCobranca($md5, $dadosCliente->id, $dadosTitulo->valor, date("Y-m-d", strtotime($dadosTitulo->dataVencimento)));
        if($cadastrarCobranca["type"] == false){
            parent::alerta("error", "Erro ao processar requisição!", $cadastrarCobranca["message"], "/ficha/cliente/$dadosCliente->nomeCompleto");
            die();
        }
        $dadosCobranca = $galax->dadosCobranca($md5);
        $boleto = $dadosCobranca["paymentBill"]["transactions"]["0"]["boleto"];
        $b = file_get_contents($boleto);
        $dadosCobranca = $galax->dadosCobranca($md5);
        dump($dadosCobranca);
        $atualizarBanco = (new FinanceiroModel())->findById($data["id"]);
        $atualizarBanco->idIntegracao = $md5;
        $atualizarBanco->favorecido = $dadosCobranca["paymentBill"]["infoBoleto"];
        $atualizarBanco->codigoBarras = $dadosCobranca["paymentBill"]["transactions"]["0"]["boletoBankLine"];
        $atualizarBanco->nossoNumero = $dadosCobranca["paymentBill"]["transactions"]["0"]["boletoBankNumber"];
        $atualizarBanco->linkBoleto = $dadosCobranca["paymentBill"]["transactions"]["0"]["boleto"];
        $atualizarBanco->change()->save();
        if($atualizarBanco->fail()){
            parent::alerta("error", "Erro ao receber retorno do gateway!", $atualizarBanco->fail()->getMessage(), "/ficha/cliente/$dadosCliente->nomeCompleto");
            die();
        }
        parent::alerta("success", "Remessa aceita com sucesso!", "", "/ficha/cliente/$dadosCliente->nomeCompleto");*/
    }

    public function ficha($data){
        parent::render("fichaFinanceira", "ficha", [
            "cliente" => $data["cliente"],
            "valorPago" => self::valorPago($data["cliente"]),
            "valorVencido" => self::valorVencido($data["cliente"]),
            "valorVencer" => self::valorVencer($data["cliente"]),
            "tabela" => self::tabela($data["cliente"])
        ]);
    }

    private static function tabela(string $cliente){
        $financeiro = new FinanceiroModel();
        $dados = $financeiro->find("cliente=:cliente", "cliente=$cliente")->fetch(true);
        $tabela = null;
        foreach($dados as $d){
            $status = null;
            if($d->status == "PAGO"){
                $status = "<span style='color: green; font-weight: bold;'>PAGO</span>";
            }else{
                if($d->dataVencimento > date("Y-m-d")){
                    $status = "<span style='color: gray; font-weight: bold;'>À VENCER</span>";
                }else{
                    $status = "<span style='color: red; font-weight: bold;'>VENCIDO</span>";
                }
            }
            $dataVencimento = date("d/m/Y", strtotime($d->dataVencimento));
            $valor = "R$ ".$d->valor;
            $valor = str_replace(".", ",", $valor);
            if($d->valorPago == ""){
                $valorPago = "";
            }else{
                $valorPago = "R$ ".$d->valorPago;
                $valorPago = str_replace(".", ",", $valorPago);
            }
            if($d->dataPagamento == ""){
                $dataPagamento = "";
            }else{
                $dataPagamento = date("d/m/Y", strtotime($d->dataPagamento));
            }
            $opcoes = null;

            if($d->status == "PAGO"){
                $opcoes .= "<a data-role='hint' data-hint-text='Enviar Remessa' style='cursor: pointer;'><img src='/src/img/enviar.png' class='img-tabela opcaoDesativada'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Recibo' style='cursor: pointer;' onclick='recibo($d->id)'><img src='/src/img/recibo.png' class='img-tabela'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Receber'><img src='/src/img/receber.png' class='img-tabela opcaoDesativada'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Boleto'><img src='/src/img/boleto.png' class='img-tabela opcaoDesativada'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Estornar' href='/ficha/estorno/$d->id/$cliente'><img src='/src/img/estorno.png' class='img-tabela'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Excluir'><img src='/src/img/excluir.png' class='img-tabela opcaoDesativada'></a>";
            }else{
                if($d->remessa == "APROVADO" || $d->remessa == "ENVIADO"){
                    $opcoes .= "<a data-role='hint' data-hint-text='Enviar Remessa'><img src='/src/img/enviar.png' class='img-tabela opcaoDesativada'></a>";
                }else{
                    if($d->nossoNumero == ""){
                        $opcoes .= "<a data-role='hint' data-hint-text='Enviar Remessa' href='/ficha/enviar/remessa/$d->cliente/$d->id'><img src='/src/img/enviar.png' class='img-tabela'></a>";
                    }else{
                        $opcoes .= "<a data-role='hint' data-hint-text='Enviar Remessa'><img src='/src/img/enviar.png' class='img-tabela opcaoDesativada'></a>";
                    }
                }
                $opcoes .= "<a data-role='hint' data-hint-text='Recibo' style='cursor: not-allowed;'><img src='/src/img/recibo.png' class='img-tabela opcaoDesativada'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Receber' href='/ficha/receber/$d->id'><img src='/src/img/receber.png' class='img-tabela'></a>";
                if($d->nossoNumero == ""){
                    $opcoes .= "<a data-role='hint' data-hint-text='Boleto'><img src='/src/img/boleto.png' class='img-tabela opcaoDesativada'></a>";
                }else{
                    $opcoes .= "<a data-role='hint' data-hint-text='Boleto' style='cursor:pointer;' onclick='boleto($d->id)'><img src='/src/img/boleto.png' class='img-tabela'></a>";
                }
                $opcoes .= "<a data-role='hint' data-hint-text='Estornar'><img src='/src/img/estorno.png' class='img-tabela opcaoDesativada'></a>";
                $opcoes .= "<a data-role='hint' data-hint-text='Excluir' href='/ficha/excluir/$d->id/$cliente'><img src='/src/img/excluir.png' class='img-tabela'></a>";
            }
            switch ($d->remessa){
                case 'ENVIADO':
                    $remessaStatus = "<img data-role='hint' data-hint-text='Aguardando Confirmação' src='/src/img/circuloAmarelo.png' class='img-tabela'>";
                    break;
                case 'RECUSADO':
                    $remessaStatus = "<img data-role='hint' data-hint-text='Remessa Recusada' src='/src/img/circuloVermelho.png' class='img-tabela'>";
                    break;
                case 'APROVADO':
                    $remessaStatus = "<img data-role='hint' data-hint-text='Remessa Aceita' src='/src/img/circuloVerde.png' class='img-tabela'>";
                    break;
                default:
                    $remessaStatus = "<img data-role='hint' data-hint-text='Remessa Não Enviada' src='/src/img/circuloTransparente.png' class='img-tabela'>";
            }
            $tabela .= "
                <tr>
                    <td>$remessaStatus</td>
                    <td>$d->id</td>
                    <td>$status</td>
                    <td>$dataVencimento</td>
                    <td>$d->nossoNumero</td>
                    <td>$valor</td>
                    <td>$valorPago</td>
                    <td>$dataPagamento</td>
                    <td>$opcoes</td>
                </tr>
            ";
        }
        return $tabela;
    }

    public function boleto($data){
        $id = $data["id"];
        $financeiro = new FinanceiroModel();
        $dadosBoleto = $financeiro->findById($id);

        self::codigo($dadosBoleto->codigoBarras);

        $clientes = new ClientesModel();
        $dadosCliente = $clientes->dadosNome($dadosBoleto->cliente);

        $boleto = file_get_contents(__DIR__."/../views/fichaFinanceira/boleto.html");
        $dados = [
            "valor" => $dadosBoleto->valor,
            "codigoBarras" => $dadosBoleto->codigoBarras,
            "nossoNumero" => $dadosBoleto->nossoNumero,
            "id" => $id,
            "dataEmissao" => date('d/m/Y', strtotime($dadosBoleto->dataEmissao)),
            "dataVencimento" => date('d/m/Y', strtotime($dadosBoleto->dataVencimento)),
            "cliente" => $dadosCliente->nomeCompleto,
            "endereco" => $dadosCliente->endereco,
            "numero" => $dadosCliente->numero,
            "cidade" => $dadosCliente->cidade,
            "cep" => $dadosCliente->cep,
            "estado" => $dadosCliente->estado,
            "bairro" => $dadosCliente->bairro,
            "cpf" => $dadosCliente->cpf,
            "dataAtual" => date('d/m/Y'),
            "barcode" => substr($this->codigoBarras, "0"),
        ];
        foreach($dados as $indice => $dados){
            $indice = "{{".$indice."}}";
            $boleto = str_replace($indice, $dados, $boleto);
        }
        if(file_put_contents(__DIR__."/../views/fichaFinanceira/dinamicos/boleto.html", $boleto) == false){
            parent::alerta("error", "Erro ao gerar recibo", "Entre em contato com o administrador", "/ficha/cliente/$dadosTitulo->cliente");
            die();
        }
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

    public function excluir($data){
        $cliente = $data["cliente"];
        $id = $data["id"];
        parent::alertaQuestion("Confirma Exclusão do Título?", "Essa ação não tem volta", "/ficha/excluir/sender/$id/$cliente", "/ficha/cliente/$cliente");
    }

    public function excluirSender($data){
        $cliente = $data["cliente"];
        $fi = new FinanceiroModel();
        $dados = $fi->dados($data["id"]);
        if($dados->remessa == "APROVADO"){
            $galax = new GalaxPay();
            $galax->cancelar($dados->idIntegracao);
        }
        $financeiro = new FinanceiroModel();
        $retorno = $financeiro->excluir($data["id"]);
        if($retorno == false){
            parent::alerta("error", "Erro ao excluir título", "Contate o administrador do sistema", "/ficha/cliente/$cliente");
            die();
        }
        parent::alerta("success", "Título excluído com sucesso!", "", "/ficha/cliente/$cliente");
    }

    public function estorno($data){
        parent::alertaQuestion("Confirma estorno do título?", $data["id"], "/ficha/estorno/sender/".$data["id"]."/".$data["cliente"], "/ficha/cliente/".$data["cliente"]);
    }

    public function estornoSender($data){
        $financeiro = new FinanceiroModel();
        $retorno = $financeiro->estorno($data["id"]);
        if($retorno != true){
            parent::alerta("error", "Erro ao estornar título", "Contate o administrador do sistema", "/ficha/cliente/".$data["cliente"]);
        }else{
            parent::alerta("success", "Título estornado com sucesso", "", "/ficha/cliente/".$data["cliente"]);
        }
    }

    private static function valorPago(string $cliente){
        $financeiro = new FinanceiroModel();
        $dadosCliente = $financeiro->find("cliente=:cliente", "cliente=$cliente")->fetch(true);
        $valor = 0;
        foreach($dadosCliente as $d){
            if($d->status == "PAGO"){
                $valor = $valor + $d->valorPago;
            }
        }
        $valor = str_replace(".", ",", $valor);
        return $valor;
    }

    private static function valorVencido(string $cliente){
        $financeiro = new FinanceiroModel();
        $dadosCliente = $financeiro->find("cliente=:cliente", "cliente=$cliente")->fetch(true);
        $valor = 0;
        foreach($dadosCliente as $d){
            if($d->status == "EM ABERTO"){
                if($d->dataVencimento < date("Y-m-d")){
                    $valor = $valor + $d->valor;
                }
            }
        }
        $valor = str_replace(".", ",", $valor);
        return $valor;
    }

    private static function valorVencer(string $cliente){
        $financeiro = new FinanceiroModel();
        $dadosCliente = $financeiro->find("cliente=:cliente", "cliente=$cliente")->fetch(true);
        $valor = 0;
        foreach($dadosCliente as $d){
            if($d->status == "EM ABERTO"){
                if($d->dataVencimento > date("Y-m-d")){
                    $valor = $valor + $d->valor;
                }
            }
        }
        $valor = str_replace(".", ",", $valor);
        return $valor;
    }
}