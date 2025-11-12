<?php
    require_once '../modelos/marca.modelo.php';

    class AjaxTipo
    {
        public $IdMarca;
        public function TraerTipo()
        {
            $IdMarca = $this->IdMarca;
            $response = MarcaModelos::BuscarTipoMarca($IdMarca);
            echo json_encode($response);
        }
    }

    if(isset($_POST["IdMarca"])){
        $TraerTipo = new AjaxTipo();
        $TraerTipo -> IdMarca = $_POST["IdMarca"];
        $TraerTipo -> TraerTipo();
    }
