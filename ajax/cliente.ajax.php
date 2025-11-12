<?php
    require_once '../controladores/cliente.controlador.php';
    require_once '../modelos/cliente.modelo.php';

    class AjaxCliente
    {

        public $ClienteCI;
        public function FiltrarCliente()
        {
            $IdPropietario = $this -> IdPropietario;
            $response = ClienteControladores::ListaClienteWhereControlador($IdPropietario);
            echo json_encode($response);
        }
    }

    if(isset($_POST["IdPropietario"])){
        $FiltrarCliente = new AjaxCliente();
        $FiltrarCliente -> IdPropietario = $_POST["IdPropietario"];
        $FiltrarCliente -> FiltrarCliente();
    }


