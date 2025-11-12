<?php
    class MarcaControladores
    {
        public function ListaMarcaControlador()
        {
                $TraerListaMarca = MarcaModelos:: ListaMarcaModelo();
                foreach ($TraerListaMarca as $key => $value) 
                {
                    $i++;
            
                    echo '
                    <option value="'.$value["idmarca"].'">'.$value["descmarca"].'</option>';
                   
                } 
        }
        public function BuscarTipoMarca($idmarca)
        {
                    $TraerListaMarca = MarcaModelos:: BuscarTipoMarca($idmarca);
                    foreach ($TraerListaTipo as $key => $value) 
                    {
                        $i++;
                        echo '
                        <option value="'.$value["idtipo"].'">'.$value["desctipo"].'</option>';
                       
                    } 
            

        }
        public function RegistrarTipoControlador()
        {
        
            if (isset($_POST["desctipo"]))
                {
                $tipo = array(
                    "desctipo" => $_POST['desctipo'],
                    "idmarca" => $_POST['idmarca']
                             
                );
                $nmkit = MarcaModelos::GuartadarTipoMarca($tipo);
                if ($nmkit=="exitoso")
                {   
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", "Guardado con exito", "success")
                    .then(function () {
                        location.href="solicitud";
                      })
                      ;
                     </script>';  
                }
                else
                {
                    echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "El tipo ya existo ya existe", "error")
                        .then(function () {
                            location.href="solicitud;
                          })
                          ;
                         </script>';
                }
                $Id = $_POST["seriekit"];
                // header("Location: extensiones/tcpdf/pdf/pdf.php?codigo=".$Id);
            }
        }
       
    }