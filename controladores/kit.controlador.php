<?php
    class MarcaKitControladores
    {
        public function ListaMarcaKitControlador()
        {
                $TraerListaMarcaKit = MarcaKitModelos:: ListaMarcaKitModelo();
                foreach ($TraerListaMarcaKit as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["codmarca"].'">'.$value["descripcion"].'</option>';
                }      
        }
    
        public function RegistrarMarcaKitControlador()
        {
            if (isset($_POST["nmarcakit"]))
                {
                $nmkit = array(
                    "descripcion" => strtoupper($_POST['nmarcakit']),
                    "estado" => 1              
                );
                $nmkit = MarcaKitModelos::InsertarMarcaKitModelo($nmkit);
                $NotaEntrega=$_GET['nota'];
                $IdRecepcion= $_GET['CodRecepcion'];
                if ($nmkit=="exitoso")
                {   
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("", "Registro correcto", "succes")
                     </script>';
                     header('Location: index.php?action=notadetalle&CodRecepcion='.$IdRecepcion.'&&nota='.$NotaEntrega);
                }
                else
                {
                    echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "Registro incorrecto", "error")
                        
                         </script>';
                }
            }
        }
        public function RegistrarKitControlador()
        {
            if (isset($_POST["seriekit"]))
                {
                $nmkitdata = array(
                    "seriekit" =>strtoupper($_POST['seriekit']),
                    "tipo" => $_POST['idtipokit'],
                    "estado" => 1,
                    "codmarca" => $_POST['idmarca'],
                    "codrecpecion" => $_POST['idnota']            
                );
                $seriekit = $_POST["seriekit"];
                $existe=KitexistenteModelos::KitExistenteModelo($seriekit);
                $NotaEntrega=$_GET['nota'];
                if($existe==true)
                {
                    echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("El kit ya existe", "", "error")
                            
                             </script>';
                }
                else
                {
                    $nmkit = MarcaKitModelos::InsertaKitModelo($nmkitdata);
                    if ($nmkit=="exitoso")
                    {   
                           header('Location: index.php?action=notadetalle&CodRecepcion='.$_POST['idnota'].'&&nota='.$NotaEntrega);
                    }
                }

               
                $Id = $_POST["seriekit"];
                // header("Location: extensiones/tcpdf/pdf/pdf.php?codigo=".$Id);
            }
            
        }
        
        public function RegistrarNotaKitControlador()
        {
            if (isset($_POST["seriekit"]))
                {
                $nmkit = array(
                    "seriekit" => $_POST['seriekit'],
                    "tipo" => $_POST['idtipokit'],
                    "estado" => 1 ,
                    "codmarca" => $_POST['idmarca'],
                    "codrecpecion" => $_POST['idnota']            
                );
                $nmkit = MarcaKitModelos::InsertaKitModelo($nmkit);
                if ($nmkit=="exitoso")
                {   
                       header( "Location:kit" ); 
                       
                      
                }
                else
                {
                    echo ("error de registro ");
                }
                $Id = $_POST["seriekit"];
                //header("Location: extensiones/tcpdf/pdf/pdf.php?codigo=".$Id);
                
            }
        }

        public function RegistrarNotaEntregaControlador()
        {
            if (isset($_POST["NotaEntrega"]))
            {
                session_start();
                $IdPersonal = $_SESSION["IdPersonal"];
                $NotaEntrega = $_POST["NotaEntrega"];
                $nmkit = MarcaKitModelos::RegistrarNotaEntregaModelo($NotaEntrega, $IdPersonal);

                if ($nmkit == 'exitoso') {
                    $tabla = 'recepcionkitkit';
                    $item = 'codrecepcion';
                    $IdRecepcion = HeredadoModelos::UltimoIdModelo($item, $tabla);
                    header('Location: index.php?action=notadetalle&CodRecepcion='.$IdRecepcion);
                }
            
            }
        }
       
    
    }
    class ListaKitAsignarControladores
    {
        public function ListaKitControlador()
        {
                $TraerListaKit = ListakitModelos:: ListakitAsignarModelo();
                foreach ($TraerListaKit as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["seriekit"].'">'.$value["seriekit"].'--'.$value["tipo"].'</option>';
                }      
        }
    }
