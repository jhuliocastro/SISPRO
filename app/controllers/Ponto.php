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

    public function configuracoes(){
        $funcionarios = new FuncionariosModel();
        $dadosFuncionarios = $funcionarios->lista();
        $horarios = null;
        foreach($dadosFuncionarios as $d){
            $horarios .= "
                <div class='form-group'>
                    <fieldset class=\"fild\">
                        $d->nome
                    </fieldset>
                    <br/>
                    <input name='txt_$d->nome' data-role=\"timepicker\" data-seconds=\"false\">
                </div>
            ";
        }
        parent::render("ponto", "horarios", [
            "horarios" => $horarios
        ]);
    }
}