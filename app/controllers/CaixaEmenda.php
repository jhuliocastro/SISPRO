<?php
namespace Controllers;

use Models\CaixaEmendaModel;
use Models\DiagramasSplitter;
use Models\PostesModel;

class CaixaEmenda extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $caixa = new CaixaEmendaModel();
        $dados = $caixa->lista();
        $tabela = null;
        foreach($dados as $d){
            $tabela .= "
                <tr>
                    <td>$d->identificacao</td>
                    <td>$d->longitude</td>
                    <td>$d->latitude</td>
                    <td>$d->poste</td>
                    <td>
                        <a data-role='hint' data-hint-text='Editar' href='/ftth/postes/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                        <a data-role='hint' data-hint-text='Excluir' href='/ftth/caixa/emenda/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                    </td>
                </tr>
            ";
        }
        parent::render("caixaEmenda", "caixaEmenda", [
            "tabela" => $tabela
        ]);
    }

    public function excluir($data){
        $caixa = new CaixaEmendaModel();
        $dados = $caixa->dados($data["id"]);
        parent::alertaQuestion("Confirma Exclusão Da Caixa de Emenda?", $dados->identificacao, "/ftth/caixa/emenda/excluir/sender/$dados->id", "/ftth/caixa/emenda");
    }

    public function excluirSender($data){
        $caixa = new CaixaEmendaModel();
        $retorno = $caixa->excluir($data["id"]);
        if($retorno == true){
            parent::alerta("success", "Caixa de Emenda Excluída com Sucesso", "", "/ftth/caixa/emenda");
        }else{
            parent::alerta("error", "Erro ao Processar Requisição", $retorno, "/ftth/caixa/emenda");
        }
    }

    public function cadastrar(){
        $postes = new PostesModel();
        parent::render("caixaEmenda", "cadastrar", [
            "postes" => $postes->listaIdentificacao()
        ]);
    }

    public function cadastrarSender(){
        $caixa = new CaixaEmendaModel();
        $retorno = $caixa->cadastrar($_POST);
        switch($retorno){
            case "existe":
                parent::alerta("warning", "Caixa de emenda não cadastrada", "Já existe uma caixa cadastrada com a identificação informada", "/ftth/caixa/emenda/cadastrar");
                break;
            case "salvou":
                parent::alerta("success", "Caixa de emenda cadastrada com sucesso", "", "/ftth/caixa/emenda");
                break;
            default:
                parent::alerta("error", "Erro ao processar requisição", $retorno, "/ftth/caixa/emenda/cadastrar");
                break;
        }
    }

    public function diagrama(){
        $caixa = new CaixaEmendaModel();
        $dados = $caixa->lista();
        $lista = [];
        foreach($dados as $d){
            $lista[$d->identificacao] = "$d->identificacao";
        }
        parent::render("caixaEmenda", "selecionar", [
            "opcoes" => json_encode($lista)
        ]);
    }

    public function diagramaSender($data){
        //SPLITTERS
        $splitter = new DiagramasSplitter();
        $splitters = $splitter->splitter($data["caixa"]);

        parent::render("caixaEmenda", "diagrama", [
            "caixa" => $data["caixa"]
        ]);
    }
}