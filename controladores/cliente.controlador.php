<?php
    class ClienteControladores
    {
        public function ListaClienteWhereControlador($IdPropietario)
        {
            return ClienteModelos::ListaClienteWhereModelo($IdPropietario);
        }
        public function RegistrarClienteoControlador()
        {
            if (isset($_POST["nci"]))
            {
                $DatosModelo = array(
                    "ci" => $_POST['nci'],
                    "nombre" => $_POST['nnombres'],
                    "paterno" => $_POST['npaterno'],
                    "materno" => $_POST['nmaterno'],
                    "materno" => $_POST['nmaterno'],
                    "telefono" => $_POST['ntelefono'],
                    "estado" => 1
                    
                );
               

                $TraerUsuario =  ClienteModelos::InsertarClienteModelo($DatosModelo);
                if ($TraerUsuario=="exitoso"){
                        echo ("Registro de cliente correcto");
                        
                }
                else
                {
                    echo ("Registro fallido");
            }
                
            }
        }
        public function BuscarClienteControlador($busquedaci)
        {
            
            $busquedaci=$_POST['busquedaci'];
            if ($busquedaci=="exitoso")
        {
                return ClienteModelos::BuscarClienteModelo($busquedaci);
            
        }
        else
        {
            echo ("No se encontro registros");
    }
            
        }
    }