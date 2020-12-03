<?php
namespace Controllers;

use Models\PontoEletronicoHorarios;
use Models\FuncionariosModel;

class Ponto extends Controller{
    public function __construct($router)
    {
        $this->router = $router;
        parent::__construct();
    }

    public function bater(){
        $funcionarios = new FuncionariosModel();
        $func = $funcionarios->lista();
        $lista = null;
        foreach ($func as $d){
            $lista .= "<option>$d->nome</option>";
        }
        $data = date('Y-m-d');
        $hora = date('H:m:s');
        parent::render("ponto", "baterPonto", [
            "data" => $data,
            "hora" => $hora,
            "funcionarios" => $lista
        ]);
    }

    public function baterSender(){
        var_dump($_POST);
    }

    public function alterar(){
        $model = new PontoEletronicoHorarios();
        $retorno = $model->alterar($_POST);
        if($retorno != "ok"){
            parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $retorno, "/ponto/horarios");
        }else{
            parent::alerta("success", "HORÁRIO ALTERADO COM SUCESSO", $_POST["funcionario"], "/ponto/horarios");
        }
    }

    public function horarios(){
        $funcionarios = new FuncionariosModel();
        $dadosFuncionarios = $funcionarios->lista();
        $horarios = null;
        $i = 0;
        foreach($dadosFuncionarios as $d){
            $model = new PontoEletronicoHorarios();
            $retornoExiste = $model->existe($d->nome);
            if($retornoExiste == 0){
                $cadastrar = $model->cadastrar($d->nome);
                if($cadastrar != true){
                    parent::alerta("error", "ERRO AO PROCESSAR REQUISIÇÃO", $cadastrar, "/painel");
                    die();
                }
            }else{
                $horarioFunc = $model->horarios($d->nome);
            }
            if($i == 0){
                $horarios .= "<div class='row'>";
            }
            $horarios .= "
                <div class='col-md-6'>
                    <div class='card' style='padding: 5px;'>
                        <div class='card-header'>
                            <span style='font-weight: bold; font-size: 18px;'>$d->nome</span>
                        </div>
                        <div class='card-body'>
                            <form method='post' action='/ponto/alterar'>
                                <fieldset class=\"fild\">
                                    Horário Chegada
                                </fieldset>
                                <input data-value='$horarioFunc->horaChegada' name='horaChegada' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Saída Almoço
                                </fieldset>
                                <input data-value='$horarioFunc->horaAlmocoEntrada' name='horaSaidaAlmoco' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Chegada Almoço
                                </fieldset>
                                <input data-value='$horarioFunc->horaAlmocoSaida' name='horaChegadaAlmoco' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Saída
                                </fieldset>
                                <input data-value='$horarioFunc->horaSaida' name='horaSaida' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <button class='button' type='submit'>Alterar</button>
                                <input type='hidden' name='funcionario' value='$d->nome'>
                            </form>
                        </div>
                    </div>
                </div>
            ";
            $i++;
            if($i == 2){
                $horarios .= "</div>";
            }
        }
        parent::render("ponto", "horarios", [
            "horarios" => $horarios
        ]);
    }
}