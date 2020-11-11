<?php
namespace Controllers;

use Models\FornecedoresModel;
use Models\ProdutosModel;
use Models\HistoricoEstoqueModel;

class Produtos extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function cadastrar(){
        parent::render("produtos", "cadastrar", []);
    }

    public function cadastrarSender(){
        $produtos = new ProdutosModel();
        $existe = $produtos->existe($_POST["nome"]);
        if($existe > 0){
            parent::alerta("error", "JÁ EXISTE UM PRODUTO COM O NOME INFORMADO", "VERIFIQUE E TENTE NOVAMENTE", "/produtos/cadastrar");
        }else{
            $cadastrar = $produtos->cadastrar($_POST);
            if($cadastrar == "ok"){
                parent::alerta("success", "PRODUTO CADASTRADO COM SUCESSO", $_POST["nome"], "/produtos/relacao");
            }else{
                parent::alerta("error", "ERRO AO CADASTRAR PRODUTO", $cadastrar, "/produtos/cadastrar");
            }
        }
    }

    public function excluir($data){
        parent::alertaQuestion("DESEJA MESMO EXCLUIR ESTE PRODUTO?", $data["nome"], "/produtos/excluir/sender/".$data["id"], "/produtos/relacao");
    }

    public function excluirSender($data){
        $produtos = new ProdutosModel();
        $retorno = $produtos->excluir($data["id"]);
        if($retorno == "ok"){
            parent::alerta("success", "PRODUTO EXCLUÍDO COM SUCESSO", "", "/produtos/relacao");
        }else{
            parent::alerta("error", "ERRO AO EXCLUIR PRODUTO", $retorno, "/produtos/relacao");
        }
    }

    public function editar($data){
        $produtos = new ProdutosModel();
        $dados = $produtos->dados($data["id"]);
        $quantidade = null;
        $quantidadeMinima = null;
        if($dados->quantidade == 0){
            $quantidade = 0;
        }else{
            $quantidade = $dados->quantidade + 1;
        }
        if($dados->quantidadeMinima == 0){
            $quantidadeMinima = 0;
        }else{
            $quantidadeMinima = $dados->quantidadeMinima + 1;
        }
        parent::render("produtos", "editar", [
            "nome" => $dados->nome,
            "quantidade" => $quantidade,
            "quantidadeMinima" => $quantidadeMinima,
            "valor" => $dados->valorAtual,
            "descricao" => $dados->descricao,
            "id" => $data["id"]
        ]);
    }

    public function editarSender(){
        $produtos = new ProdutosModel();
        $retorno = $produtos->editar($_POST);
        if($retorno == "ok"){
            parent::alerta("success", "ALTERAÇÕES SALVAS COM SUCESSO", "", "/produtos/relacao");
        }else{
            parent::alerta("error", "ERRO AO SALVAR ALTERAÇÕES", $retorno, "/produtos/relacao");
        }
    }

    public function entrada(){
        $produtos = new ProdutosModel();
        $dados = $produtos->lista();
        $listaProdutos = null;
        foreach ($dados as $d){
            $listaProdutos .= "
                <option>$d->nome</option>
            ";
        }

        $fornecedores = new FornecedoresModel();
        $dados = $fornecedores->lista();
        $listaFornecedores = null;
        foreach ($dados as $d){
            $listaFornecedores .= "
                <option>$d->nomeFantasia</option>
            ";
        }

        parent::render("produtos", "entrada", [
            "produtos" => $listaProdutos,
            "fornecedores" => $listaFornecedores
        ]);
    }

    public function entradaSender(){
        $produtos = new ProdutosModel();
        $dadosProduto = $produtos->dadosNome($_POST["nome"]);
        $retorno = $produtos->atualizaValor($_POST["nome"], $_POST["valor"]);
        if($retorno == "ok"){
            $p = new ProdutosModel();
            $retorno = $p->atualizaQuantidade($_POST["nome"], $_POST["quantidade"], $dadosProduto->quantidade);
            if($retorno == "ok"){
                $historico = new HistoricoEstoqueModel();
                $retorno = $historico->cadastrar($_POST["nome"], "ENTRADA MANUAL", "ENTRADA", $_POST["quantidade"]);
                if($retorno == "ok"){
                    parent::alerta("success", "ENTRADA DE PRODUTO CADASTRADA COM SUCESSO", "", "/produtos/relacao");
                }else{
                    parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $retorno, "/produtos/entrada");
                }
            }else{
                parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $retorno, "/produtos/entrada");
            }
        }else{
            parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $retorno, "/produtos/entrada");
        }
    }

    public function relacao(){
        $produtos = new ProdutosModel();
        $dados = $produtos->lista();
        $tabela = null;
        foreach($dados as $d){
            $tabela .= "
                <tr>
                    <td>$d->id</td>
                    <td>$d->nome</td>
                    <td>$d->quantidade</td>
                    <td>$d->valorAtual</td>
                    <td>$d->ultimaCompra</td>
                    <td>
                        <a data-role='hint' data-hint-text='Editar' href='/produtos/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                        <a data-role='hint' data-hint-text='Excluir' href='/produtos/excluir/$d->id/$d->nome'><img class='img-tabela' src='/src/img/excluir.png'></a>
                    </td>
                </tr>
            ";
        }
        parent::render("produtos", "produtos", [
            "tabela" => $tabela
        ]);
    }
}