<?php
namespace Controllers;

use Models\PostesModel;

class Postes extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function home(){
        $fornecedores = new PostesModel();
        $tabela = null;
        $dados = $fornecedores->find()->fetch(true);
        foreach($dados as $d){
            $tabela .= "
                <tr>
                    <td>$d->identificacao</td>
                    <td>$d->longitude</td>
                    <td>$d->latitude</td>
                    <td>
                        <a data-role='hint' data-hint-text='Editar' href='/ftth/postes/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                        <a data-role='hint' data-hint-text='Excluir' href='/ftth/postes/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                    </td>
                </tr>
            ";
        }
        parent::render("postes", "postes", [
            "tabela" => $tabela
        ]);
    }

    public function editar($data){
        $id = $data["id"];
        $postes = (new PostesModel())->findById($id);
        parent::render("postes", "editar", [
            "identificacao" => $postes->identificacao,
            "longitude" => $postes->longitude,
            "latitude" => $postes->latitude,
            "id" => $postes->id
        ]);
    }

    public function editarSender(){
        $dados = (object) $_POST;
        $postes = (new PostesModel())->findById($dados->id);
        $postes->identificacao = $dados->identificacao;
        $postes->longitude = $dados->longitude;
        $postes->latitude = $dados->latitude;
        $postes->change()->save();
        if($postes->fail()){
            parent::alerta("error", "Erro ao editar cadastro de poste", $postes->fail()->getMessage(), "/ftth/postes");
            die();
        }
        parent::alerta("success", "Cadastro atualizado com sucesso!", "", "/ftth/postes");
    }

    public function excluir($data){
        $id = $data["id"];
        $postes = new PostesModel();
        $dados = $postes->findById($id);
        parent::alertaQuestion("Confirma exclusão do poste?", $dados->identificacao, "/ftth/postes/excluir/sender/$dados->id", "/ftth/postes");
    }

    public function excluirSender($data){
        $id = $data["id"];
        $postes = (new PostesModel())->findById($id);
        $postes->destroy();
        if($postes->fail()){
            parent::alerta("error", "Erro ao excluir poste", $postes->fail()->getMessage(), "/ftth/postes");
            die();
        }
        parent::alerta("success", "Poste excluído com sucesso", "", "/ftth/postes");
    }

    public function cadastrar(){
        parent::render("postes", "cadastrar", []);
    }

    public function cadastrarSender(){
        $dados = (object) $_POST;
        $contagem = (new PostesModel)->find("identificacao=:id", "id=$dados->identificacao")->count();
        if($contagem == 1){
            parent::alerta("error", "Existe um poste cadastrado com essa identificação", "Verifique e tente novamente", "/ftth/postes/cadastrar");
            die();
        }
        $fornecedores = new PostesModel();
        $fornecedores->identificacao = $dados->identificacao;
        $fornecedores->longitude = $dados->longitude;
        $fornecedores->latitude = $dados->latitude;
        $fornecedores->save();
        if($fornecedores->fail()){
            parent::alerta("error", "Erro ao processar requisição", $fornecedores->fail()->getMessage(), "/ftth/postes/cadastrar");
            die();
        }
        parent::alerta("success", "Poste cadastrado com sucesso", $dados->identificacao, "/ftth/postes");
    }
}