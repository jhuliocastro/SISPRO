<?php
namespace Controllers;

use Models\PlanosModel;
use Models\PoolModel;
use Models\Radgroupreply;
use Models\Radgroupcheck;

class Planos extends Controller{

    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        parent::render("planos", "planos", [
            "tabela" => self::tabela()
        ]);
    }

    public function cadastrar(){
        $pool = new PoolModel();
        $dadosPool = $pool->find()->fetch(true);
        $pools = null;
        foreach($dadosPool as $d){
            $pools .= "<option>$d->nome</option>";
        }
        parent::render("planos", "cadastrar", [
            "pools" => $pools
        ]);
    }

    public function cadastrarSender(){
        $nomePlano = $_POST["nome"];
        $planos = new PlanosModel();
        if($planos->find("descricao=:nome", "nome=$nomePlano")->count() > 0){
            parent::alerta("warning", "Já existe um plano com o mesmo nome cadastrado!", "Verifique e tente novamente", "/planos/cadastrar");
        }else{
            $cPLanos = new PlanosModel();
            $cPLanos->descricao = $_POST["nome"];
            $cPLanos->download = $_POST["download"];
            $cPLanos->upload = $_POST["upload"];
            $cPLanos->valor = $_POST["valor"];
            $cPLanos->pool = $_POST["pool"];
            $cPLanos->save();
            if($cPLanos->fail()){
                parent::alerta("error", "Falha ao processar requisição", $cPLanos->fail()->getMessage(), "/planos/cadastrar");
            }else{
                    $velocidade = $_POST["upload"]."M/".$_POST["download"]."M"." 0/0 0/0 0/0 8 0/0";

                    $reply2 = new Radgroupreply();
                    $reply2->groupname = $_POST["nome"];
                    $reply2->attribute = "Mikrotik-Rate-Limit";
                    $reply2->op = "=";
                    $reply2->value = $velocidade;
                    $reply2->save();

                    if($reply2->fail()){
                        parent::alerta("error", "Falha ao processar requisição", $reply2->fail()->getMessage(), "/planos/cadastrar");
                    }else{
                        parent::alerta("success", "Plano cadastrado com sucesso!", "", "/planos/lista");
                    }
            }
        }
    }

    public function editar($data){
        $planos = new PlanosModel();
        $dados = $planos->dadosID($data["id"]);
        parent::render("planos", "editar", [
            "id" => $data["id"],
            "nome" => $dados->descricao,
            "download" => $dados->download,
            "upload" => $dados->upload,
            "valor" => $dados->valor
        ]);
    }

    public function editarSender(){
        $dados = (object)$_POST;
        $planosT = new PlanosModel();
        $planos = $planosT->find("descricao=:nome", "nome=$dados->nomeAntigo")->fetch();
        $planos->download = $dados->download;
        $planos->upload = $dados->upload;
        $planos->valor = $dados->valor;
        $planos->change()->save();
        if($planos->fail()){
            parent::alerta("error", "Erro no processamento da requisição", $planos->fail()->getMessage(), "/planos/lista");
            die();
        }
        $reply = (new Radgroupreply())->find("groupname=:name AND attribute=:atributo", "name=$dados->nomeAntigo&atributo=Mikrotik-Rate-Limit")->fetch();
        $velocidade = $dados->upload."M/".$dados->download."M";
        $reply->value = $velocidade;
        $reply->change()->save();
        if($reply->fail()){
            parent::alerta("error", "Erro no processamento da requisição", $reply->fail()->getMessage(), "/planos/lista");
            die();
        }
        parent::alerta("success", "Plano editado com sucesso!", "", "/planos/lista");
    }

    public function excluir($data){
        $id = $data["id"];
        parent::alertaQuestion("Deseja realmente excluir o plano desejado?", "", "/planos/excluir/sender/$id", "/planos/lista");
    }

    public function excluirSender($data){
        $planos = new PlanosModel();
        $dadosPlano = $planos->findById($data["id"]);

        $reply = new Radgroupreply();
        $count = $reply->find("groupname=:name", "name=$dadosPlano->descricao")->count();
        while ($count > 0){
            $reply1 = (new Radgroupreply())->find("groupname=:name", "name=$dadosPlano->descricao")->fetch();
            $reply1->destroy();
            $reply = new Radgroupreply();
            $count = $reply->find("groupname=:name", "name=$dadosPlano->descricao")->count();
            if($reply1->fail()){
                parent::alerta("error", "Erro na solicitação da requisição da solicitação", $reply1->fail()->getMessage(), "/planos/lista");
                die();
            }
        }

        $check = new Radgroupcheck();
        $count = $check->find("groupname=:name", "name=$dadosPlano->descricao")->count();
        while($count > 0){
            $check1 = (new Radgroupcheck())->find("groupname=:name", "name=$dadosPlano->descricao")->fetch();
            $check1->destroy();
            $check = new Radgroupcheck();
            $count = $check->find("groupname=:name", "name=$dadosPlano->descricao")->count();
            if($check1->fail()){
                parent::alerta("error", "Erro na solicitação da requisição da solicitação", $check1->fail()->getMessage(), "/planos/lista");
                die();
            }
        }

        $delPlano = (new PlanosModel())->findById($data["id"]);
        $delPlano->destroy();

        //tratamento de erros
        if($delPlano->fail()){
            parent::alerta("error", "Erro na solicitação da requisição da solicitação", $delPlano->fail()->getMessage(), "/planos/lista");
        }else{
            parent::alerta("success", "Plano excluído com sucesso", "", "/planos/lista");
        }
    }

    private function tabela(){
        $planos = new PlanosModel();
        $dados = $planos->lista();
        $tabela = null;
        foreach($dados as $d){
            $tabela .= "
            <tr>
                <td>$d->descricao</td>
                <td>$d->download M</td>
                <td>$d->upload M</td>
                <td>R$ $d->valor</td>
                <td>
                    <a data-role='hint' data-hint-text='Editar' href='/planos/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                    <a data-role='hint' data-hint-text='Excluir' href='/planos/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                </td>
            </tr>
            ";
        }
        return $tabela;
    }
}