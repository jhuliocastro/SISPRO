<?php
namespace Controllers;

class GalaxPay{
    private $galaxID = "9594";
    private $galaxHash = "4s5nI2GiOd29Y0T3Vu2uDjEp08VcVs0iYf86Ty9j";
    private $webService = "https://app.galaxpay.com.br/webservice/";
    public $multa = "2";
    private $juros = "1";

    private function enviar($dados,$metodo){
        $dados = json_encode($dados);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webService . $metodo);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        $resposta = trim(curl_exec($ch));
        curl_close($ch);
        $resposta = json_decode($resposta, true);
        return $resposta;
    }

    public function cancelar($id){
        $dados = [
            "Auth" => [
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ],
            "Request" => [
                "paymentBillIntegrationId" => $id
            ]
        ];
        return self::enviar($dados, "cancelPaymentBill");
    }

    public function pesquisarCliente($cpf){
        $array = [
            "Auth" => [
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ],
            "Request" => [
                "customerDocument" => $cpf
            ]
        ];

        return self::enviar($array, "getCustomerInfo");
    }

    public function dadosCobranca($id){
        $dados = [
            "Auth" => [
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ],
            "Request" => [
                "integrationId" => $id
            ]
        ];
        return self::enviar($dados, "getPaymentBillInfo");
    }

    public function receber($id){
        $dados = [
            "Auth" => [
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ],
            "Request" => [
                "paymentBillIntegrationId" => $id
            ]
        ];
        return self::enviar($dados, "cancelPaymentBill");
    }

    public function cadastrarCliente($dados){
        $array = array(
            "Auth" => array(
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ),
            "Request" => array(
                "integrationId" => $dados->id,
                "document" => $dados->cpf,
                "name" => $dados->nomeCompleto,
                "email" => $dados->email,
                "phone" => "",
                "Address" => [
                    "zipCode" => $dados->cep,
                    "street" => $dados->endereco,
                    "number" => $dados->numero,
                    "neighborhood" => $dados->bairro,
                    "city" => $dados->cidade,
                    "state" => $dados->estado
                ]
            )
        );
        return self::enviar($array, "createCustomer");
    }

    public function cadastraCobranca($md5, $idCliente, $valor, $vencimento){
        $dados = [
            "Auth" => [
                "galaxId" => $this->galaxID,
                "galaxHash" => $this->galaxHash
            ],
            "Request" => [
                "integrationId" => $md5,
                "typeBill" => "contract",
                "periodicity" => "monthly",
                "quantity" => "1",
                "value" => $valor,
                "customerIntegrationId" => $idCliente,
                "fineBoleto" => $this->multa,
                "interestBoleto" => $this->juros,
                "payday" => $vencimento
            ]
        ];
        return self::enviar($dados, "createPaymentBillBoleto");
    }
}