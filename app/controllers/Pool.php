<?php
namespace Controllers;

use Models\PoolModel;
use Models\RadIpPool;

class Pool extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
        parent::render("pool", "cadastrar", []);
    }

    public function lista(){
        parent::render("pool", "lista", [
            "tabela" => self::tabela()
        ]);
    }

    public function excluir($data){
        //pega os dados da pool
        $pool = new PoolModel();
        $dadosPool = $pool->findById($data["id"]);
        $ipInicio = explode(".", $dadosPool->ipInicio);
        $ipFinal = explode(".", $dadosPool->ipFim);
        for($a=$ipInicio[0];$a<=$ipFinal[0];$a++){
            for($b=$ipInicio[1];$b<=$ipFinal[1];$b++){
                for($c=$ipInicio[2];$c<=$ipFinal[2];$c++){
                    for($d=$ipInicio[3];$d<=$ipFinal[3];$d++){
                        $ip = $a.".".$b.".".$c.".".$d;
                        $radippool = (new RadIpPool())->find("framedipaddress=:ip", "ip=$ip")->fetch();
                        $radippool->destroy();
                        if($radippool->fail()){
                            parent::alerta("error", "Erro ao processar requisição", $radippool->fail()->getMessage(), "/pool/lista");
                            die();
                        }
                    }
                }
            }
        }
        $pool2 = (new PoolModel())->findById($data["id"])->destroy();
        parent::alerta("success", "Pool excluída com sucesso!", "", "/pool/lista");
        /*
        if($pool2->f){
            parent::alerta("error", "Erro ao processar requisição", $dadosPool->fail()->getMessage(), "/pool/lista");
        }else{

        }*/
    }

    public function cadastrarSender(){
        //MONTAGEM DE IP INTEIRO
        $ipInicio = $_POST["inicioFaixa1"].".".$_POST["inicioFaixa2"].".".$_POST["inicioFaixa3"].".".$_POST["inicioFaixa4"];
        $ipFim = $_POST["fimFaixa1"].".".$_POST["fimFaixa2"].".".$_POST["fimFaixa3"].".".$_POST["fimFaixa4"];

        //grava a pool no banco do sistema
        $pool = new PoolModel();
        $pool->nome = $_POST["nome"];
        $pool->ipInicio = $ipInicio;
        $pool->ipFim = $ipFim;
        if($pool->save() == true){
            for($a=$_POST["inicioFaixa1"];$a<=$_POST["fimFaixa1"];$a++){
                for($b=$_POST["inicioFaixa2"];$b<=$_POST["fimFaixa2"];$b++){
                    for($c=$_POST["inicioFaixa3"];$c<=$_POST["fimFaixa3"];$c++){
                        for($d=$_POST["inicioFaixa4"];$d<=$_POST["fimFaixa4"];$d++){
                            $ip = $a.".".$b.".".$c.".".$d;
                            $radippool = new RadIpPool();
                            $radippool->pool_name = $_POST["nome"];
                            $radippool->framedipaddress = $ip;
                            if($radippool->save() == false){
                                parent::alerta("error", "Erro ao processar requisição", $radippool->fail()->getMessage(), "/pool/cadastrar");
                                die();
                            }
                        }
                    }
                }
            }
            parent::alerta("success", "Pool cadastrada com sucesso!", "", "/pool/lista");
        }else{
            parent::alerta("error", "Erro na solicitação da requisição", $pool->fail()->getMessage(), "/pool/cadastrar");
        }
    }

    private static function tabela(){
        $pool = new PoolModel();
        $dados = $pool->find()->fetch(true);
        $tabela = null;
        foreach($dados as $d){
            $tabela .= "
            <tr>
                <td>$d->nome</td>
                <td>$d->ipInicio</td>
                <td>$d->ipFim</td>
                <td>
                    <a data-role='hint' data-hint-text='Excluir' href='/pool/excluir/$d->id'><img class='img-tabela' src='/src/img/excluir.png'></a>
                </td>
            </tr>
            ";
        }
        return $tabela;
    }
}