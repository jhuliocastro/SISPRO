<?php
namespace Controllers;

use Models\ClientesModel;
use Models\ArquivosModel;

class Dossie extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function upload(){
        $clientes = new ClientesModel();
        $dadosClientes = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $opcoes = null;
        foreach($dadosClientes as $d){
            $opcoes .= "
                <option>$d->nomeCompleto</option>
            ";
        }
        parent::render("dossie", "upload", [
            "clientes" => $opcoes
        ]);
    }

    public function uploadSender(){
        $cliente = $_POST["cliente"];
        $clientes = new ClientesModel();
        $arquivos = new ArquivosModel();
        $retorno = $clientes->find("nomeCompleto=:nome", "nome=$cliente")->count();
        if($retorno > 0){
            try{
                $nomeArquivo = parent::md5Ale().".pdf";
                try{
                    move_uploaded_file($_FILES["arquivo"]["tmp_name"], __DIR__."/../../arquivos/".$nomeArquivo);
                    $arquivos->cliente = $cliente;
                    $arquivos->nomeArquivo = $_POST["nomeArquivo"];
                    $arquivos->arquivo = $nomeArquivo;
                    $retorno = $arquivos->save();
                    if($retorno == true){
                        parent::alerta("success", "Upload realizado com sucesso!","", "/dossie/upload");
                    }else{
                        parent::alerta("error", "Erro ao incluir no banco de dados!", $arquivos->fail()->getMessage(), "/dossie/upload");
                    }
                }catch(Exception $e){
                    parent::alerta("error", $e->getMessage(),"", "/dossie/upload");
                }
            }catch(Exception $e){
                var_dump($e->getMessage());
            }
        }else{
            parent::alerta("error", "Cliente não existe!", "/dossie/upload");
        }
    }

    public function cliente($data){
        $cliente = $data["cliente"];
        $arquivos = new ArquivosModel();
        $listaArquivos = $arquivos->find("cliente=:cliente", "cliente=$cliente")->fetch(true);
        $lista = null;
        foreach($listaArquivos as $dados) {
            $lista .= "<li ondblclick=\"vizualizar('$dados->arquivo')\" data-icon=\"<span class='mif-file-empty'>\"";
            $lista .= "data-caption=\"$dados->nomeArquivo\"";
            $lista .= "</li>";
        }
        parent::render("dossie", "dossie", [
            "diretorios" => $lista
        ]);
    }

    public function arquivos(){
        $clientes = new ClientesModel();
        $clientes = $clientes->find()->order("nomeCompleto ASC")->fetch(true);
        $lista = null;
        foreach($clientes as $dados){
            $lista .= "<li ondblclick=\"diretorio('$dados->nomeCompleto')\" data-icon=\"<span class='mif-folder fg-cyan'>\"";
            $lista .= "data-caption=\"$dados->nomeCompleto\"";
            $lista .= "</li>";
        }
        parent::render("dossie", "dossie", [
            "diretorios" => $lista
        ]);
    }
}