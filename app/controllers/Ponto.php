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
                            <form method='post' action=''>
                                <fieldset class=\"fild\">
                                    Horário Chegada
                                </fieldset>
                                <input data-value='{{hora_chegada_$d->nome}}' name='txt_hora_chegada_$d->nome' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Saída Almoço
                                </fieldset>
                                <input data-value='{{hora_saida_almoco_$d->nome}}' name='txt_hora_saida_almoco_$d->nome' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Chegada Almoço
                                </fieldset>
                                <input data-value='{{hora_chegada_almoco_$d->nome}}' name='txt_hora_chegada_almoco_$d->nome' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
                                <fieldset class=\"fild\">
                                    Horário Saída
                                </fieldset>
                                <input data-value='{{hora_saida_$d->nome}}' name='txt_hora_saida_$d->nome' data-role=\"timepicker\" data-seconds=\"false\">
                                <br/>
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