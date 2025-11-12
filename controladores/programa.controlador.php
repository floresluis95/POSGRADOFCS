<?php

class ProgramasControladores
{ 
    
    public function ListaProgramaControlador()
    {
        $listaprogramas = ProgramasModelos::ListaProgramaModelo();
            // PREFORMATEAR CODIGO - VARDUMP
          /*  echo '<pre>';
                var_dump($listaprogramas);
            echo '</pre>';*/
      foreach ($listaprogramas as $key => $programa) 
{
 
    echo '<tr>
        <td width="50" height="50">'.$programa['ProgramaID'].'</td>
        <td width="50" height="50">'.$programa["NombrePrograma"].'</td>
        <td>'.$programa["GradoAcademico"].'</td>
        <td>'.$programa["Codigo"].'</td>
        <td>'.date("d/m/Y", strtotime($programa["FechaInicio"])).'</td>
        <td>'.$programa["Sede"].'</td>';

    // ✅ Campo ESTADO con mensaje y color
    if ($programa["Estado"] == 1) {
        echo '<td style="color: green; font-weight: bold;">ACTIVO</td>';
    } else {
        echo '<td style="color: red; font-weight: bold;">INACTIVO</td>';
    }

    // Botón DETALLE
    echo '<td>
        <button type="button" id="btnDetallePrograma" idcod="'.$programa['ProgramaID'].'" 
            class="btn btn-bold btn-label-brand btn-sm" 
            data-toggle="modal" data-target="#DetallePrograma">
            <i class="kt-menu__link-icon flaticon-squares-4"></i>
        </button>
    </td>';

    // Botón SUBIR (activar programa)
    if ($programa["Estado"] == 1) {
        echo '<td>
                <button type="button" class="btn btn-success btn-sm round mr-1 mb-1" disabled>
                    <i class="fa fa-arrow-up"></i>
                </button>
              </td>';
    } else {
        echo '<td>
                <button data-toggle="modal" data-target="#modalsubir" type="button" 
                    data-idprograma="'.$programa['ProgramaID'].'" 
                    class="btn btn-success btn-sm round mr-1 mb-1 btnSubir">
                    <i class="fa fa-arrow-up"></i>
                </button>
              </td>';
    }

    // Botón DAR DE BAJA (desactivar programa)
    if ($programa["Estado"] == 0) {
        echo '<td>
                <button type="button" class="btn btn-danger btn-sm round mr-1 mb-1" disabled>
                    <i class="fa fa-arrow-down"></i>
                </button>
              </td>';
    } else {
        echo '<td>
                <button data-toggle="modal" data-target="#modalBorrar" type="button" 
                    data-idprograma="'.$programa['ProgramaID'].'" 
                    class="btn btn-danger btn-sm round mr-1 mb-1 btnDarBaja">
                    <i class="fa fa-arrow-down"></i>
                </button>
              </td>';
    }
     ;
     

        echo '</tr>';
   
        
     }
        
          
        }
        

    public function RegistrarProgramaControlador()
    {
        if (isset($_POST["NombrePrograma"]))
        {

                $datosPrograma =array(
                "NombrePrograma" => htmlspecialchars(trim($_POST['NombrePrograma'])), // Sanitizar texto
                "GradoAcademico" => htmlspecialchars(trim($_POST['GradoAcademico'])),
                "DuracionMeses" => (int)$_POST['DuracionMeses'],
                "Modulos" => (int)$_POST['Modulos'], // Forzar tipo entero
                "FechaInicio" => htmlspecialchars(trim($_POST['FechaInicio'])),
                "Sede" => htmlspecialchars(trim($_POST['Sede'])),
                "Costo" => (float)$_POST['Costo'], // Forzar tipo flotante/decimal
                "Detalle" => htmlspecialchars(trim($_POST['Detalle']))
                );
                $nombreprograma = $_POST['Nombreprograma'];
                $existe = ProgramasModelos::Buscarprogramamodelo($nombreprograma);
                if ($existe==true)
                    {
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "El programa ya se creo", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
                    }
                else
                    {   
                        $resultado = ProgramasModelos::RegistrarProgramaModelo($datosPrograma);
                        if ($resultado == 'exitoso')
                            {
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Se registro el programa", "success")
                                .then(function () {
                                    location.href="programas";
                                })
                                ;
                                </script>';  
                            }
                        else
                            {
                               echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se registro el programa", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
                            }
                    }
        }
    }
    public function ListaProgramaActivoControlador()
    {
        $TraerListaProgramaActivo = ProgramasModelos::ListaProgramaActivoModelo();
        foreach ($TraerListaProgramaActivo as $key =>$value)
        {
            $i++;
            echo '<option value="'.$value["ProgramaID"].'">'.$value["NombrePrograma"]. '</option>';
        
        }

    }

    // Mostrar detalle en panel
    public static function MostrarDetalleProgramaControlador($id) {
    $detalle = ProgramasModelos::MostrarDetallePrograma($id);
    return $detalle;
    }
}

class ProgramaEstadoControlador
{
   public function SubirProgramaControlador()
{
    if (isset($_POST["idProgramaSubir"])) {
        $id = $_POST["idProgramaSubir"];

        $respuesta = ProgramaEstadoModelo::SubirProgramaModelo($id);

        if ($respuesta == "exitoso") {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("EXITOSO!", "El programa fue activado correctamente", "success")
            .then(function () {
                location.href="programas";
            });
            </script>';
        } else {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "No se pudo activar el programa", "error")
            .then(function () {
                location.href="programas";
            });
            </script>';
        }
    }
}
    public function BajarProgramaControlador() {
        if (isset($_POST["idProgramaBaja"])) {
            
            $id = $_POST["idProgramaBaja"];
            
            $respuesta = ProgramaEstadoModelo::BajarProgramaModelo($id);

            if ($respuesta == "exitoso") {
                echo '
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "El programa ya no esta disponible", "success")
                                .then(function () {
                                    location.href="programas";
                                })
                                ;
                                </script>';
            } else {
                 echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se modifico el programa", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
            }
        }
    }
}


