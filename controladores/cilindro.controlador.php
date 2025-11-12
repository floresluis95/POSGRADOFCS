<?php
    class MarcaCilindroControladores
    {
        public function ListaMarcaCilindroControlador()
        {
                $TraerListaMarcaCilindro =MarcaCilindroModelos:: ListaMarcaCilindroModelo();
                foreach ($TraerListaMarcaCilindro as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["codmarcacil"].'">'.$value["descripcioncil"].'</option>';
                   
                }      
        }
        public function RegistrarMarcaCilindroControlador()
        {
            if (isset($_POST["nmarcacil"]))
                {
                $nmcil = array(
                    "descripcioncil" => strtoupper($_POST['nmarcacil']),
                    "estado" => 1              
                );
                $nmcil =MarcaCilindroModelos::InsertarMarcaCilindroModelo($nmcil);
                $IdRecepcion= $_GET['CodRecepcioncil'];
                $NotaEntrega=$_GET['notac'];
                if ($nmcil=="exitoso")
                {      
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("", "Registro correcto", "succes")
                    </script>';
                     header('Location: index.php?action=notadetallec&CodRecepcion='.$IdRecepcion.'&&notac='.$NotaEntrega);
                }
                else
                {
                    echo ("error de registro");
                }
            }
        }
    }
    class RegistrarNotaEntregacControlador
    {
        public function RegistrarNotaEntregaControlador()
        {
            if (isset($_POST["NotaEntregac"]))
            {
                session_start();
                $IdPersonal = $_SESSION["IdPersonal"];
                $NotaEntregac = strtoupper($_POST["NotaEntregac"]);
                $nmkit = MarcaCilindroModelos::RegistrarNotaEntregaModelo($NotaEntregac, $IdPersonal);

                if ($nmkit == 'exitoso') {
                    $tabla = 'recepcioncilindro';
                    $item = 'codrecepcion';
                    $IdRecepcion = HeredadoModelos::UltimoIdModelo($item, $tabla);
                    header('Location: index.php?action=notadetalle&CodRecepcion='.$IdRecepcion);
                }
            
            }
        }
    }
    class ListaNotacControladores
    {
        public function ListaNotacControladores()
        {
            $ListaNotas = ListaNotacModelos::ListaNotaModelo();
                // PREFORMATEAR CODIGO - VARDUMP
               /* echo '<pre>';
                    var_dump($ListaNotas);
                echo '</pre>';*/
            foreach ($ListaNotas as $key => $Usuario) 
            {  
                echo '<tr>
                  
                    <td>'.$Usuario["codrecepcioncil"].'</td>
                    <td>'.$Usuario["fecharecepcioncil"].'</td>
                    <td>'.$Usuario["notadeentrega"].'</td>
                    <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                    
                  
                    </tr>';
            }
        }
    }
    date_default_timezone_set("America/La_Paz");
    class RegistrarCilindroControlador
    {

        public function RegistrarCilControlador()
        {
            if (isset($_POST["seriecil"]))
                {

                $nmcildata = array(
                    "seriecilindro" => strtoupper($_POST["seriecil"]),
                    "codmarcacil" => $_POST["idmarca"],
                    "capacidad" => $_POST["capacidad"],
                    "aofab" => $_POST["aofab"], 
                    "codrecpecioncil" => $_GET["CodRecepcioncil"]      
                );
                $seriec= $_POST["seriecil"];
                $existe=buscarcilindroModelos::buscarcilindromodelo($seriec);
                if($existe==false)
                {
                    $nmkit = RegistrarCilModelos::RegistrarCilModelo($nmcildata);
                
                    if ($nmkit=="exitoso")
                    {    $NotaEntrega=$_GET['notac'];
                           header('Location: index.php?action=notadetallec&CodRecepcioncil='.$_GET["CodRecepcioncil"].'&&notac='.$NotaEntrega);
                    }
                   
                }
                else
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("El cilindro ya existe", "", "error")
                    
                     </script>';
                   
                }

               
                $Id = $_POST["seriecil"];
                // header("Location: extensiones/tcpdf/pdf/pdf.php?codigo=".$Id);
            }       
            
        }

    }
    class ListacilAsignarControladores
    {
        public function ListacilControlador()
        {
                $TraerListacil = ListacilModelos:: ListacilAsignarModelo();
                foreach ($TraerListacil as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["seriecilindro"].'">'.$value["seriecilindro"].'</option>';
                }      
        }
    }

   

       
    