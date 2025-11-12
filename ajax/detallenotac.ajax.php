<?php
    require_once '../modelos/nentrega.modelo.php';

    class Ajax
    {
        public $id;
        public function TraerDetalle()
        {
            $id = $this -> id;
            $response = NotaEntregaModelos::ListadetalleCilModelo($id); 
            echo json_encode($response);
        }
    }

    if(isset($_POST["id"])){
        $TraerDetalle = new Ajax();
        $TraerDetalle -> id =  $_POST["id"];
        $TraerDetalle -> TraerDetalle();
    }  