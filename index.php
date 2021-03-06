<?php
require __DIR__."/vendor/autoload.php";

use CoffeeCode\Router\Router;

$router = new Router(URL_BASE);

/*
 * RAIZ
 */
$router->namespace("Controllers");
$router->get("/", "Login:home");
$router->post("/login", "Login:login");
$router->get("/painel", "Painel:home");

/*
 * CHAT
 */
$router->group("chat");
$router->get("/", "Chat:chat");
$router->post("/exibir/chat", "Chat:exibirChat");

/*
 * SAIR
 */
$router->group("sair");
$router->get("/", "Sair:sair");

/*
 * PONTO ELETRÔNICO
 */
$router->group("ponto");
$router->get("/horarios", "Ponto:horarios");
$router->post("/alterar", "Ponto:alterar");
$router->get("/bater", "Ponto:bater");
$router->post("/bater", "Ponto:baterSender");

/*
 * CLIENTES
 */
$router->group("clientes");
$router->get("/cadastrar", "Clientes:cadastrar");
$router->post("/cadastrar", "Clientes:cadastrarSender");
$router->get("/editar/{cliente}", "Clientes:editar");
$router->get("/lista/ativados", "Clientes:ativados");
$router->get("/lista/bloqueados", "Clientes:bloqueados");
$router->get("/lista/inativos", "Clientes:inativos");
$router->get("/bloquear/{id}", "Clientes:bloquear");
$router->get("/bloquear/sender/{id}", "Clientes:bloquearSender");
$router->post("/verifica/cpf","Clientes:verificaCPF");
$router->get("/desbloquear/{id}", "Clientes:desbloquear");
$router->get("/desbloquear/sender/{id}", "Clientes:desbloquearSender");
$router->get("/dados/{id}", "Clientes:dados");
$router->get("/consultar", "Clientes:consultar");

/*
 * FICHA FINANCEIRA
 */
$router->group("ficha");
$router->get("/selecionar", "FichaFinanceira:selecionar");
$router->get("/cliente/{cliente}", "FichaFinanceira:ficha");
$router->get("/enviar/remessa/{cliente}/{id}", "FichaFinanceira:enviarRemessa");
$router->get("/cadastrar/titulos", "FichaFinanceira:cadastrarTitulos");
$router->post("/cadastrar/titulos", "FichaFinanceira:cadastrarTitulosSender");
$router->get("/recibo/{id}", "FichaFinanceira:recibo");
$router->get("/receber/{id}", "FichaFinanceira:receber");
$router->post("/receber", "FichaFinanceira:receberSender");
$router->get("/excluir/{id}/{cliente}", "FichaFinanceira:excluir");
$router->get("/excluir/sender/{id}/{cliente}", "FichaFinanceira:excluirSender");
$router->get("/estorno/{id}/{cliente}", "FichaFinanceira:estorno");
$router->get("/estorno/sender/{id}/{cliente}", "FichaFinanceira:estornoSender");
$router->get("/boleto/{id}", "FichaFinanceira:boleto");

/*
 * CARNÊS
 */
$router->group("/carne");
$router->get("/", "Carne:selecionar");
$router->get("/cliente/{cliente}", "Carne:gerar");
$router->post("/imprimir", "Carne:imprimir");
$router->post("/capa", "Carne:capa");

/*
 * TELEFONES
 */
$router->group("telefones");
$router->get("/", "Telefones:home");
$router->get("/add", "Telefones:add");
$router->post("/cadastrar", "Telefones:cadastrar");
$router->get("/excluir/{id}", "Telefones:excluir");
$router->get("/excluir/sender/{id}", "Telefones:excluirSender");

/*
 * CONTRATOS
 */
$router->group("contratos");
$router->get("/", "Contratos:home");
$router->get("/cadastrar", "Contratos:cadastrar");
$router->post("/cadastrar", "Contratos:cadastrarSender");
$router->get("/cadastrar/sender/{cliente}/{dataInicio}/{dataFinal}", "Contratos:cadastrarSender2");
$router->post("/imprimir", "Contratos:imprimir");

/*
 * PLANOS
 */
$router->group("planos");
$router->get("/lista", "Planos:home");
$router->get("/excluir/{id}", "Planos:excluir");
$router->get("/excluir/sender/{id}", "Planos:excluirSender");
$router->get("/cadastrar", "Planos:cadastrar");
$router->post("/cadastrar", "Planos:cadastrarSender");
$router->get("/editar/{id}", "Planos:editar");
$router->post("/editar", "Planos:editarSender");

/*
 * DOSSIE ELETRONICO
 */
$router->group("dossie");
$router->get("/arquivos", "Dossie:arquivos");
$router->get("/arquivos/{cliente}", "Dossie:cliente");
$router->get("/upload", "Dossie:upload");
$router->post("/upload", "Dossie:uploadSender");
$router->get("/excluir/{arquivo}", "Dossie:excluir");

/*
 * RELATORIOS
 */
$router->group("relatorios");
$router->get("/faturas/atrasadas", "FichaFinanceira:faturasAtrasadas");
$router->get("/conexoes", "ConexoesAcesso:conexoes");
$router->get("/clientes/financeiro/sem", "Clientes:semFinanceiro");
$router->get("/ponto", "Ponto:relatorio");
$router->get("/ponto/{funcionario}", "Ponto:relatorioSender");

/*
 * FORNECEDORES
 */
$router->group("fornecedores");
$router->get("/cadastrar", "Fornecedores:cadastrar");
$router->post("/cadastrar", "Fornecedores:cadastrarSender");
$router->get("/relacao", "Fornecedores:relacao");
$router->get("/dados/{id}", "Fornecedores:dados");
$router->get("/editar/{id}", "Fornecedores:editar");
$router->post("/editar", "Fornecedores:editarSender");
$router->get("/excluir/{id}", "Fornecedores:excluir");
$router->get("/excluir/sender/{id}", "Fornecedores:excluirSender");

/*
 * PRODUTOS
 */
$router->group("produtos");
$router->get("/cadastrar", "Produtos:cadastrar");
$router->post("/cadastrar", "Produtos:cadastrarSender");
$router->get("/relacao", "Produtos:relacao");
$router->get("/excluir/{id}/{nome}", "Produtos:excluir");
$router->get("/excluir/sender/{id}", "Produtos:excluirSender");
$router->get("/editar/{id}", "Produtos:editar");
$router->post("/editar", "Produtos:editarSender");
$router->get("/entrada", "Produtos:entrada");
$router->post("/entrada", "Produtos:entradaSender");
$router->get("/historico", "Produtos:historico");
$router->get("/saida", "Produtos:saida");
$router->post("/saida", "Produtos:saidaSender");

/*
 * FUNCIONARIOS
 */
$router->group("funcionarios");
$router->get("/relacao", "Funcionarios:relacao");
$router->get("/cadastrar", "Funcionarios:cadastrar");
$router->post("/cadastrar", "Funcionarios:cadastrarSender");

/*
 * FTTH
 */
$router->group("ftth");
$router->get("/postes", "Postes:home");
$router->get("/postes/cadastrar", "Postes:cadastrar");
$router->post("/postes/cadastrar", "Postes:cadastrarSender");
$router->get("/postes/editar/{id}", "Postes:editar");
$router->post("/postes/editar", "Postes:editarSender");
$router->get("/postes/excluir/{id}", "Postes:excluir");
$router->get("/postes/excluir/sender/{id}", "Postes:excluirSender");
$router->get("/caixa/emenda/cadastrar", "CaixaEmenda:cadastrar");
$router->post("/caixa/emenda/cadastrar", "CaixaEmenda:cadastrarSender");
$router->get("/caixa/emenda", "CaixaEmenda:home");
$router->get("/caixa/emenda/excluir/{id}", "CaixaEmenda:excluir");
$router->get("/caixa/emenda/excluir/sender/{id}", "CaixaEmenda:excluirSender");
$router->get("/caixa/emenda/diagrama", "CaixaEmenda:diagrama");
$router->get("/caixa/emenda/diagrama/{caixa}", "CaixaEmenda:diagramaSender");

/*
 * DIAGRAMAS
 */
$router->group("diagrama");
$router->post("/backbone/existe", "Diagramas:backboneExiste");
$router->post("/splitter/adicionar", "Diagramas:splitterCadastrar");

/*
 * CEPs
 */
$router->group("cep");
$router->post("/pesquisar", "CEP:pesquisar");
 

/*
 * CAIXA DIÁRIO
 */
$router->group("caixa");
$router->get("/", "Caixa:home");
$router->get("/abrir", "Caixa:abrir");
$router->post("/incluir", "Caixa:incluirSender");
$router->get("/excluir/{id}", "Caixa:excluir");
$router->get("/excluir/sender/{id}", "Caixa:excluirSender");
$router->get("/fechar", "Caixa:fechar");
$router->get("/fechar/sender", "Caixa:fecharSender");
$router->get("/incluir", "Caixa:incluir");
$router->get("/relatorio/selecionar", "Caixa:relatorioSelecionar");
$router->get("/relatorio/selecionar/{data}", "Caixa:relatorio");

/*
 * POOLS
 */
$router->group("pool");
$router->get("/cadastrar", "Pool:cadastrar");
$router->post("/cadastrar", "Pool:cadastrarSender");
$router->get("/lista", "Pool:lista");
$router->get("/excluir/{id}", "Pool:excluir");

/*
 * SERVIDOR
 */
$router->group("servidor");
$router->get("/dados", "Servidor:home");
$router->post("/alterar", "Servidor:alterar");

/*
 * ERROS
 */
$router->group("error");
$router->get("/{errcode}", "Erro:home");

$router->dispatch();

if($router->error()){
    //$router->redirect("/error/{$router->error()}");
}