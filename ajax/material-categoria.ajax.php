<?php
    require_once '../controladores/tipo.controlador.php';
    require_once '../modelos/tipo.modelo.php';
    
    class AjaxMarcaCategoria
    {
        public $IdMarca;
        public function ajaxTraerMarca()
        {
            $IdMarca = $this -> IdMarca;
            $response = MarcaControlador::ListaMarcaControlador($IdMarca);
            echo json_encode($response);
        }
    }

    if(isset($_POST["IdMarca"]))
    {
        $TraerMarcaes = new AjaxMarcaCategoria();
        $TraerMarcaes -> IdMarca = $_POST["IdMarca"];
        $TraerMarcaes -> ajaxTraerMarca();
    }