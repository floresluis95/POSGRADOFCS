<?php
    require_once '../modelos/solicitud.modelo.php';

    class AjaxTipo
    {
        public $Idsol;
        public function TraerTipo()
        {
            $Idsol = $this->Idsol;
            $response = BuscarSolicitudwhereModelos::buscarSolicitudModelo($Idsol);
            echo json_encode($response);
        }
    }

    if(isset($_POST["Idsol"])){
        $TraerTipo = new AjaxTipo();
        $TraerTipo -> Idsol = $_POST["Idsol"];
        $TraerTipo -> TraerTipo();
    }
