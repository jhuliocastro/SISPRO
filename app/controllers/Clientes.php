<?php
namespace Controllers;

use Controllers\Mikrotik;
use Models\ClientesModel;
use Models\Nas;
use Models\PlanosModel;
use Models\Radcheck;
use Models\Radusergroup;

class Clientes extends Controller
{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public static function cadastrar(){
        parent::render("clientes", "cadastrar", [
            "planos" => self::planosSelect()
        ]);
    }

    public function bloqueados(){
        $clientes = new ClientesModel();
        $dados = $clientes->bloqueados();
        $tabela = null;
        foreach($dados as $d){
            $data = date("d/m/Y", strtotime($d->dataCadastro));
            $tabela .= "
            <tr>
                <td>$d->nomeCompleto</td>
                <td>$d->plano</td>
                <td>$data</td>
                <td>
                    <a data-role='hint' data-hint-text='Editar' href='/clientes/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                    <a data-role='hint' data-hint-text='Dados' href='#' onClick='dados($d->id)'><img class='img-tabela' src='/src/img/detalhes.png'></a>
                    <a data-role='hint' data-hint-text='Desbloquear' href='/clientes/desbloquear/$d->id'><img class='img-tabela' src='/src/img/desbloquear.png'></a>
                </td>
            </tr>
            ";
        }
        parent::render("clientes", "bloqueados", [
            "tabela" => $tabela
        ]);
    }

    public function verificaCPF(){
        $cpf = $_POST["cpf"];
        $clientes = new ClientesModel();
        $retorno = $clientes->find("cpf=:cpf", "cpf=$cpf")->count();
        echo $retorno;
    }

    public function cadastrarSender(){
        $dados = (object)$_POST;
        $clientes = new ClientesModel();
        $clientes->nomeCompleto = $dados->nome;
        $clientes->email = $dados->email;
        $clientes->dataCadastro = $dados->dataCadastro;
        $clientes->dataNascimento = $dados->dataNascimento;
        $clientes->rg = $dados->rg;
        $clientes->cpf = $dados->cpf;
        $clientes->nomePai = $dados->nomePai;
        $clientes->nomeMae = $dados->nomeMae;
        $clientes->cep = $dados->cep;
        $clientes->endereco = $dados->endereco;
        $clientes->numero = $dados->numero;
        $clientes->cidade = $dados->cidade;
        $clientes->bairro = $dados->bairro;
        $clientes->estado = $dados->estado;
        $clientes->complemento = $dados->complemento;
        $clientes->coordenadas = $dados->coordenadas;
        $clientes->plano = $dados->plano;
        $clientes->usuario = $dados->usuario;
        $clientes->senha = $dados->senha;
        $clientes->dataVencimento = $dados->dataVencimento;
        if($clientes->save() == true){
            $servidor = new Nas();
            $dadosServidor = $servidor->find("id=:id", "id=1")->fetch();

            $nas = new Radcheck();
            $nas->value = $dadosServidor->nasname;
            $nas->op = "==";
            $nas->attribute = "NAS-IP-Address";
            $nas->username = $dados->usuario;
            $nas->login = $dados->usuario;
            $nas->save();

            $senha = new Radcheck();
            $senha->value = $dados->senha;
            $senha->op = ":=";
            $senha->attribute = "Cleartext-Password";
            $senha->username = $dados->usuario;
            $senha->login = $dados->usuario;
            $senha->save();

            $planoRadius = new Radusergroup();
            $planoRadius->username = $dados->usuario;
            $planoRadius->login = $dados->usuario;
            $planoRadius->groupname = $dados->plano;
            $planoRadius->save();

            parent::alerta("success", "Cliente cadastrado com sucesso", $dados->nome, "/clientes/lista/ativados");
        }else{
            parent::alerta("error", "Ocorreu um erro ao processar requisição", $clientes->fail()->getMessage(), "/clientes/cadastrar");
        }
    }

    public function editar($data){
        $clientes = new ClientesModel();
        parent::render("clientes", "editar", []);
    }

    public function bloquear($data){
        $id = $data["id"];
        $clientes = new ClientesModel();
        $dados = $clientes->dadosID($id);
        parent::alertaQuestion("CONFIRMA BLOQUEIO DESTE CLIENTE?", $dados->nomeCompleto, "/clientes/bloquear/sender/$id", "/clientes/lista/ativados");
    }

    public function bloquearSender($data){
        $id = $data["id"];
        $alteracao = (new ClientesModel())->findById($id);
        $alteracao->bloqueado = true;
        $retorno = $alteracao->change()->save();
        if($retorno == true){
            $clientes = new ClientesModel();
            $dadosCliente = $clientes->findById($id);
            $mudaPlano = (new Radusergroup())->find("username=:user", "user=$dadosCliente->usuario")->fetch();
            $mudaPlano->groupname = "PLANO BLOQUEADO";
            $retorno = $mudaPlano->change()->save();
            if($retorno == true){
                $mk = new Mikrotik();
                $retornoPPPOE = $mk->pesquisarPPPOE($dadosCliente->usuario);
                $mk->removerPPPOE($retornoPPPOE[0][".id"]);
                parent::alerta("success", "Cliente bloqueado com sucesso!", $dadosCliente->nomeCompleto, "/clientes/lista/ativados");
            }else{
                parent::alerta("error", "Erro ao processar requisição", $mudaPlano->fail()->getMessage(), "/clientes/lista/ativados");
            }
        }else{
            parent::alerta("error", "Erro ao processar requisição", $alteracao->fail()->getMessage(), "/clientes/lista/ativados");
        }
    }

    public function desbloquear($data){
        $id = $data["id"];
        $clientes = new ClientesModel();
        $dados = $clientes->dadosID($id);
        parent::alertaQuestion("CONFIRMA DESBLOQUEIO DESTE CLIENTE?", $dados->nomeCompleto, "/clientes/desbloquear/sender/$id", "/clientes/lista/ativados");
    }

    public function desbloquearSender($data){
        $id = $data["id"];
        $alteracao = (new ClientesModel())->findById($id);
        $alteracao->bloqueado = 0;
        $retorno = $alteracao->change()->save();
        if($retorno == true){
            $clientes = new ClientesModel();
            $dadosCliente = $clientes->findById($id);
            $mudaPlano = (new Radusergroup())->find("username=:user", "user=$dadosCliente->usuario")->fetch();
            $mudaPlano->groupname = $dadosCliente->plano;
            $retorno = $mudaPlano->change()->save();
            if($retorno == true){
                $mk = new Mikrotik();
                $retornoPPPOE = $mk->pesquisarPPPOE($dadosCliente->usuario);
                $mk->removerPPPOE($retornoPPPOE[0][".id"]);
                parent::alerta("success", "CLIENTE DESBLOQUEADO COM SUCESSO", $dadosCliente->nomeCompleto, "/clientes/lista/ativados");
            }else{
                parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $alteracao->fail()->getMessage(), "/clientes/lista/ativados");
            }
        }else{
            parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $alteracao->fail()->getMessage(), "/clientes/lista/ativados");
        }
    }

    public function dados($data){
        $idCliente = $data["id"];
        $clientes = new ClientesModel();
        $dadosCliente = $clientes->find("id=:id", "id=$idCliente")->fetch();
        parent::render("clientes", "dados", [
            "nome" => $dadosCliente->nomeCompleto,
            "endereco" => $dadosCliente->endereco.", ".$dadosCliente->numero,
            "bairro" => $dadosCliente->bairro,
            "cidade" => $dadosCliente->cidade,
            "vencimento" => $dadosCliente->dataVencimento,
            "dataCadastro" => date("d/m/Y", strtotime($dadosCliente->dataCadastro)),
            "email" => $dadosCliente->email,
            "cpf" => $dadosCliente->cpf,
            "usuario" => $dadosCliente->usuario,
            "senha" => $dadosCliente->senha,
            "plano" => $dadosCliente->plano
        ]);
    }

    public function ativados(){
        $clientes = new ClientesModel();
        $dados = $clientes->ativados();
        $tabela = null;
        foreach($dados as $d){
            $data = date("d/m/Y", strtotime($d->dataCadastro));
            if($d->bloqueado == false){
                $bd = "<a data-role='hint' data-hint-text='Bloquear' href='/clientes/bloquear/$d->id'><img class='img-tabela' src='/src/img/desativar.png'></a>";
            }else if($d->bloqueado == true){
                $bd = "<a data-role='hint' data-hint-text='Desbloquear' href='/clientes/desbloquear/$d->id'><img class='img-tabela' src='/src/img/desbloquear.png'></a>";
            }
            $tabela .= "
            <tr>
                <td>$d->nomeCompleto</td>
                <td>$d->plano</td>
                <td>$data</td>
                <td>
                    <a data-role='hint' data-hint-text='Editar' href='/clientes/editar/$d->id'><img class='img-tabela' src='/src/img/editar.png'></a>
                    <a data-role='hint' data-hint-text='Dados' href='#' onClick='dados($d->id)'><img class='img-tabela' src='/src/img/detalhes.png'></a>
                    $bd
                </td>
            </tr>
            ";
        }
        parent::render("clientes", "ativados", [
            "tabela" => $tabela
        ]);
    }

    public static function planosSelect(){
        $planos = new PlanosModel();
        $dados = $planos->find()->fetch(true);
        $lista = null;
        foreach($dados as $d){
            $lista .= "<option>$d->descricao</option>";
        }
        return $lista;
    }
}