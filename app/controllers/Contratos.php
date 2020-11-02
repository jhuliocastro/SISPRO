<?php
namespace Controllers;

use Models\ClientesModel;
use Models\ContratosModel;
use Models\PlanosModel;

class Contratos extends Controller
{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $contratos = new ContratosModel();
        $dados = $contratos->find()->fetch(true);
        $tabela = null;
        foreach ($dados as $d){
            $opcoes = null;
            $dataHoje = date("Y-m-d");
            $dataInicio = date("d/m/Y", strtotime($d->dataInicio));
            $dataFinal = date("d/m/Y", strtotime($d->dataFinal));
            $dataFinalContrato = date("Y-m-d", strtotime($d->dataFinal));
            if(strtotime($dataHoje) < strtotime($dataFinalContrato)) {
                $status = "<span style='color: green; font-weight: bold;'>ATIVO</span>";
                $opcoes .= "<a data-role='hint' style='cursor: pointer;' data-hint-text='Imprimir' onclick='imprimir($d->id)'><img class='img-tabela' src='/src/img/imprimir.png'></a>";
            }else{
                $status = "<span style='color: gray; font-weight: bold;'>EXPIRADO</span>";
                $opcoes .= "<a data-role='hint' data-hint-text='Imprimir' href='#'><img class='img-tabela opcaoDesativada' src='/src/img/imprimir.png'></a>";
            }
            $tabela .= "
                <tr>
                    <td>$d->id</td>
                    <td>$status</td>
                    <td>$d->cliente</td>
                    <td>$dataInicio</td>
                    <td>$dataFinal</td>
                    <td>$opcoes</td>
                </tr>
            ";
        }
        parent::render("contratos", "contratos", [
            "tabela" => $tabela
        ]);
    }

    public function imprimir(){
        $id = $_POST["id"];
        $contrato = (new ContratosModel())->findById($id);
        $banco = new ClientesModel();
        $bancoPlano = new PlanosModel();
        $clientesResult = $banco->find("nomeCompleto=:nome", "nome=$contrato->cliente")->fetch();
        //DADOS DO PLANO
        $dadosPlano = $bancoPlano->find("descricao=:nome", "nome=$clientesResult->plano")->fetch();
        //FORMATAÃ‡Ã•ES NECESSÃRIAS PARA EXIBIÃ‡ÃƒO AO USUÃRIO
        $endereco = $clientesResult->endereco.", ".$clientesResult->numero.", ".$clientesResult->bairro.", ".$clientesResult->cidade.", ".$clientesResult->estado.", ".$clientesResult->cep;
        $dadosPlano->valor = "R$".$dadosPlano->valor;
        $dadosPlano->veldown .= "mbps";
        $dadosPlano->velup .= "mbps";
        //CRIA O ARRAY COM OS DADOS NECESSÁRIOS
        $dados = [
            "nomeCliente" => $clientesResult->nomeCompleto,
            "cpf/cnpj" => $clientesResult->cpf,
            "rg/ie" => $clientesResult->rg,
            "email" => $clientesResult->email,
            "telFixo" => $clientesResult->fone,
            "telCelular" => $clientesResult->celular,
            "outroContato" => $clientesResult->celular2,
            "nomeMae" => $clientesResult->nomeMae,
            "nomePai" => $clientesResult->nomePai,
            "dataNascimento" => $clientesResult->nascimento,
            "endereco" => $endereco,
            "razaoSocial" => "JHULIO C OLIVEIRA DE CASTRO ME",
            "siteEmpresa" => "www.provedornova.com.br",
            "emailEmpresa" => "contato@provedornova.com.br",
            "cnpjEmpresa" => "27.742.206/0001-43",
            "ie" => $dadosEmpresa->ie,
            "telEmpresa" => "81994688754",
            "enderecoEmpresa" => "RUA JOSÉ SALES, 29, BAIRRO NOVO, CARPINA - PE",
            "login" => $clientesResult->usuario,
            "senha" => $clientesResult->senha,
            "pacote" => $clientesResult->plano,
            "valor" => $dadosPlano->valor,
            "upload" => $dadosPlano->upload,
            "download" => $dadosPlano->download,
            "diaVencimentoMensalidade" => $clientesResult->dataVencimento,
            "dataInicio" => date("d/m/Y", strtotime($contrato->dataInicio)),
            "taxaAtivacao" => $clientesResult->valorAtivacao
        ];
        $geracao = file_get_contents(__DIR__."/../views/contratos/termoAdesao.html");
        $geracao .= file_get_contents(__DIR__."/../views/contratos/contratoPermanencia.html");
        foreach($dados as $indice => $dados){
            $indice = "{{".$indice."}}";
            $geracao = str_replace($indice, $dados, $geracao);
        }
        file_put_contents(__DIR__."/../views/contratos/dinamicos/contrato.html", $geracao);
    }

    public function cadastrar(){
        $clientes = new ClientesModel();
        $dadosClientes = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $opcoes = null;
        foreach($dadosClientes as $d){
            $opcoes .= "
                <option>$d->nomeCompleto</option>
            ";
        }
        parent::render("contratos", "cadastrar", [
            "clientes" => $opcoes
        ]);
    }

    public function cadastrarSender(){
        $contratos = new ContratosModel();
        $dados = $contratos->find("cliente=:cliente", "cliente=$_POST[cliente]")->fetch();
        $cliente = $_POST["cliente"];
        $dataInicio = $_POST["dataInicio"];
        $dataFinal = $_POST["dataFim"];
        if($dados != null){
            $dataHoje = date("Y-m-d");
            $dataFinalContrato = date("Y-m-d", strtotime($dados->dataFinal));
            if(strtotime($dataHoje) < strtotime($dataFinalContrato)){
                parent::alertaQuestion("Cliente tem contrato em vigência", "Deseja continuar mesmo assim?", "/contratos/cadastrar/sender/$cliente/$dataInicio/$dataFinal", "/contratos");
            }else{
                $this->router->redirect("/contratos/cadastrar/sender/$cliente/$dataInicio/$dataFinal");
            }
        }else{
            $this->router->redirect("/contratos/cadastrar/sender/$cliente/$dataInicio/$dataFinal");
        }
    }

    public function cadastrarSender2($data){
        $contratos = new ContratosModel();
        $contratos->cliente = $data["cliente"];
        $contratos->dataInicio = $data["dataInicio"];
        $contratos->dataFinal = $data["dataFinal"];
        $contratos->save();
        if($contratos->fail()){
            parent::alerta("error", "Erro ao processar requisição", $contratos->fail()->getMessage(), "/contratos");
            die();
        }
        parent::alerta("success", "Contrato cadastrado com sucesso", "", "/contratos");
    }
}