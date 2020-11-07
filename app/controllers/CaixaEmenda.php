<?php
namespace Controllers;

use Models\CaixaEmendaModel;
use Models\PostesModel;

class CaixaEmenda extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
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
}