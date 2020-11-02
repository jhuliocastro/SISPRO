<?php
namespace Controllers;

use Controllers\Controller;
use Models\CaixaSituacaoModel;
use Models\CaixaModel;

class Caixa extends Controller
{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $retorno = self::situacaoDiaria();
        if($retorno == 0){
            parent::alertaQuestion("Caixa ainda não foi aberto", "Deseja abrir agora?", "/caixa/abrir", "/painel");
        }else{
            $dados = self::dados(date("Y-m-d"));
            if($dados->situacao == "FECHADO"){
                parent::alerta("warning", "Caixa do dia já foi fechado!", "", "/painel");
            }else{
                parent::render("caixa", "caixa", [
                    "tabela" => self::lista()
                ]);
            }
        }
    }

    public function excluir($data){
        extract($data);
        $caixa = new CaixaModel();
        $dados = $caixa->findById($id);
        parent::alertaQuestion("Confirma exclusão do lançamento?", $dados->descricao, "/caixa/excluir/sender/$id", "/caixa");
    }

    public function relatorioSelecionar(){
        parent::render("caixa", "relatorioSelecionar", []);
    }

    public function relatorio($data){
        $caixa = new CaixaModel();
        $lista = $caixa->find()->order("id ASC")->fetch(true);
        $tabela = null;
        foreach($lista as $d){
            if($data["data"] == date("Y-m-d", strtotime($d->created_at))){
                $valor = "R$ ".$d->valor;
                if($d->tipo == "Entrada"){
                    $valor = "<span style='color: darkgreen; font-weight: bold;'>$valor</span>";
                }else{
                    $valor = "<span style='color: red; font-weight: bold;'>$valor</span>";
                }
                $tabela .= "
                    <tr>
                        <td>$d->id</td>
                        <td>$d->descricao</td>
                        <td>$d->tipo</td>
                        <td>$valor</td>
                    </tr>
                ";
            }
        }

        parent::render("caixa", "relatorio", [
            "data" => date("d/m/Y", strtotime($data["data"])),
            "tabela" => $tabela
        ]);
    }

    public function excluirSender($data){
        extract($data);
        $caixa = (new CaixaModel())->findById($id);
        $caixa->destroy();
        if($caixa->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $caixa->fail()->getMessage(), "/caixa");
            die();
        }
        parent::alerta("success", "Lançamento excluído com sucesso!", "", "/caixa");
    }

    public function relatorioDiario($dados){
        parent::render("caixa", "relatorioCaixaDiario", [
            "data" => $dados["data"],
            "dataBR" => date("d/m/Y", strtotime($dados["data"]))
        ]);
    }

    public static function saldoDiario(){
        $caixa_model = new CaixaModel();
        $valor = str_replace(".", ",", $caixa_model->saldoDia(date("Y-m-d")));
        //$valor = number_format($valor, 2, '.', '');
        return $valor;
    }

    public function fechar(){
        $dataAtual = date("Y-m-d");
        $caixa = new CaixaSituacaoModel();
        $count = $caixa->find("dataCaixa=:data", "data=$dataAtual")->count();
        if($caixa->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $caixa->fail()->getMessage(), "/caixa");
            die();
        }
        if($count == 0){
            $this->router->redirect("/caixa");
        }
        $dados = $caixa->find("dataCaixa=:data", "data=$dataAtual")->fetch();
        if($dados->situacao == "FECHADO"){
            parent::alerta("warning", "Caixa do dia já foi fechado!", "Entre em contato com o administrador do sistema", "/painel");
            die();
        }
        parent::alertaQuestion("Confirma saldo em caixa?", "R$ ".self::saldoDiario(), "/caixa/fechar/sender", "/caixa");
    }

    public function fecharSender(){
        $dataAtual = date("Y-m-d");
        $caixa = (new CaixaSituacaoModel())->find("dataCaixa=:data", "data=$dataAtual")->fetch();
        $caixa->situacao = "FECHADO";
        $caixa->change()->save();
        if($caixa->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $caixa->fail()->getMessage(), "/caixa");
            die();
        }
        parent::alerta("success", "Caixa fechado com sucesso!", "", "/painel");
    }

    public function incluir(){
        $dataAtual = date("Y-m-d");
        $caixa = new CaixaSituacaoModel();
        $count = $caixa->find("dataCaixa=:data", "data=$dataAtual")->count();
        if($count == 0){
            $this->router->redirect("/caixa");
        }
        $dados = $caixa->find("dataCaixa=:data", "data=$dataAtual")->fetch();
        if($dados->situacao == "FECHADO"){
            parent::alerta("warning", "Caixa do dia já foi fechado!", "Entre em contato com o administrador do sistema", "/painel");
            die();
        }
        parent::render("caixa", "novoLancamento", []);
    }

    public function incluirSender(){
        $dados = (object) $_POST;
        $caixa = new CaixaModel();
        $caixa->descricao = $dados->descricao;
        $caixa->tipo = $dados->tipo;
        $caixa->valor = $dados->valor;
        $caixa->save();
        if($caixa->fail()){
            parent::alerta("error", "Erro ao processar requisição!", $caixa->fail()->getMessage(), "/caixa/incluir");
            die();
        }
        parent::alerta("success", "Lançamento incluído com sucesso!", "", "/caixa");
    }

    public function abrir(){
        $caixaModel = new CaixaSituacaoModel();
        $caixaModel->salvar();
        $dados = $caixaModel->dadosUltimo();
        $cc = new CaixaModel();
        $data = date("Y-m-d", strtotime('-1 days', strtotime($dados->dataCaixa)));
        $valor = $cc->saldoDia($data);
        $valor = str_replace(".", ",", $valor);
        $dados = [
            "valor" => $valor,
            "descricao" => "SALDO DIA ANTERIOR",
            "tipo" => "Entrada"
        ];
        $cc->inserir($dados);
        $this->router->redirect("/caixa");
    }

    public static function situacaoDiaria(){
        $caixaSituacao = new CaixaSituacaoModel();
        return $caixaSituacao->dadosData(date("Y-m-d"));
    }

    public static function dados($data){
        $caixaSituacao = new CaixaSituacaoModel();
        return $caixaSituacao->dados($data);
    }

    public static function lista(){
        $caixa = new CaixaModel();
        $lista = $caixa->find()->order("id ASC")->fetch(true);
        $tabela = null;
        $i = 0;
        foreach($lista as $d){
            if(date("Y-m-d") == date("Y-m-d", strtotime($d->created_at))){
                $valor = "R$ ".$d->valor;
                if($d->tipo == "Entrada"){
                    $valor = "<span style='color: darkgreen; font-weight: bold;'>$valor</span>";
                }else{
                    $valor = "<span style='color: red; font-weight: bold;'>$valor</span>";
                }
                if($i == 0){
                    $opcoes = "<a data-role='hint' disabled data-hint-text='Excluir' href='/caixa/excluir/$d->id'><img style='cursor: no-drop;' class='img-tabela' src='/src/img/excluir.png'></a>";
                }else{
                    $opcoes = "<a data-role='hint' data-hint-text='Excluir' href='/caixa/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>";
                }
                $i++;
                $tabela .= "
                    <tr>
                        <td>$d->id</td>
                        <td>$d->descricao</td>
                        <td>$d->tipo</td>
                        <td>$valor</td>
                        <td>
                            $opcoes
                        </td>
                    </tr>
                ";
            }
        }
        return $tabela;
    }
 /*
    public function listaDiario($data){
        $arquivos = new CaixaModel();
        $listaArquivos = $arquivos->lista();
        $dados["data"] = array();
        for($i=0;$i<sizeof($listaArquivos);$i++){
            if(date("Y-m-d", strtotime($data["data"])) == date("Y-m-d", strtotime($listaArquivos[$i]->created_at))){
                $id = $listaArquivos[$i]->id;
                $valor = "R$ ".$listaArquivos[$i]->valor;
                if($listaArquivos[$i]->tipo == "Entrada"){
                    $valor = "<span style='color: darkgreen; font-weight: bold;'>$valor</span>";
                }else{
                    $valor = "<span style='color: red; font-weight: bold;'>$valor</span>";
                }
                $provisorio = [
                    $t,
                    $id,
                    $listaArquivos[$i]->descricao,
                    $listaArquivos[$i]->tipo,
                    $valor
                ];
                $dados["data"][] = $provisorio;
            }
        }
        echo json_encode($dados);
    }*/
}