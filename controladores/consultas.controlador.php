<?php
///CILINDROS
class BuscarcilControlador
{
    public function BuscarcilControlador ()
    {
        if (isset($_POST["seriecilindro"]))
                {
              
                $seriecilindro = $_POST['seriecilindro'];
                
                $existe = ConsultacilModelos::concluidoModelo($seriecilindro);
                if($existe==true)
            {
            echo '<tr>
                <td>'.$existe['codsolicitud'].'</td>
                <td>'.$existe['fechasolicitud'].'</td>
                <td>'.$existe['nroplaca'].'</td>
                <td>'.$existe["fechatrabajo"].'</td>
                <td>'.$existe["fechaconcluido"].'</td>
                <td>'.$existe['Nombres'].' '.$existe["ApellidoPaterno"].'</td>  
                <td>
                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledesolicitud.php?codigo='.$existe['codsolicitud'].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                </td>  
                </tr>';
            }
            else {
                $existe = ConsultacilModelos::ListaCilAsignado($seriecilindro);
                if($existe==true)
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("CILINDRO ASIGNADO", "", "")
                    .then(function () {
                        location.href="ccilindro";
                      })
                      ;
                     </script>'; 
                }
                else
                {
                    $existe = ConsultacilModelos::Listacildisponibles($seriecilindro);
                    if($existe==true)
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("CILINDRO DISPONIBLE", "", "")
                        .then(function () {
                            location.href="ccilindro";
                          })
                          ;
                         </script>'; 
                    }
                    else {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("NO SE ENCONTRO EL CILINDRO", "", "")
                        .then(function () {
                            location.href="ccilindro";
                          })
                          ;
                         </script>';
                    }
                   

                }
        
    }
    
    }
    

    }

}
///KITS
class BuscarkitControladores
{ 
    public function BuscarkitControlador ()
    {
        if (isset($_POST["seriekit"]))
                {
              
                $seriekit = $_POST['seriekit'];
                
                $existe = ConsultakitModelos::ConsultakitModelo($seriekit);
                if($existe==true)
            {
            echo '<tr>
                <td>'.$existe['codsolicitud'].'</td>
                <td>'.$existe['fechasolicitud'].'</td>
                <td>'.$existe['nroplaca'].'</td>
                <td>'.$existe["fechatrabajo"].'</td>
                <td>'.$existe["fechaconcluido"].'</td>
                <td>'.$existe['Nombres'].' '.$existe["ApellidoPaterno"].'</td>  
                <td>
                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledesolicitud.php?codigo='.$existe['codsolicitud'].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                </td>  
                </tr>';
            }
            else {
                $existe = ConsultakitModelos::ListaKitAsignado($seriekit);
                if($existe==true)
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("KIT ASIGNADO", "", "")
                    .then(function () {
                        location.href="ckit";
                      })
                      ;
                     </script>'; 
                }
                else
                {
                    $existe = ConsultakitModelos::ListaKitdisponibles($seriekit);
                    if($existe==true)
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("KIT DISPONIBLE", "", "")
                        .then(function () {
                            location.href="ckit";
                          })
                          ;
                         </script>'; 
                    }
                    else {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("NO SE ENCONTRO EL KIT", "", "")
                        .then(function () {
                            location.href="ckit";
                          })
                          ;
                         </script>';
                    }
                   

                }
        
    }
    
    }
    

    }
}

class TrabajoscConcluidosControlador
{ 
    public function BuscartrabajoporfechaControlador ()
    {
        if (isset($_POST["estado"]))
        
        {   $estado = $_POST['estado'];
            $inicio = $_POST['inicio'];
            $final = $_POST['final'];
                  
        if($inicio<=$final)
        {
            if($estado=='TERMINADO')
            {
                $inicio = $_POST['inicio'];
                $final = $_POST['final'];
                $terminado = trabajosConcluidosModelos::TrabajosConcluidosModelo($inicio,$final);
                if($terminado==true)
                    {
                        foreach ($terminado as $key => $concluido) 
                        {
                            $i++;       
                            echo '<tr>
                                <td>'.$concluido['codsolicitud'].'</td>
                                <td>'.$concluido["fechasolicitud"].'</td>
                                <td>'.$concluido["nombre"].' '.$concluido["paterno"].'</td>
                                <td>'.$concluido["nroplaca"].'</td>
                                <td>'.$concluido["fechatrabajo"].'</td>
                                <td>'.$concluido["fechaconcluido"].'</td>
                                <td>
                                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledesolicitud.php?codigo='.$concluido['codsolicitud'].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                                </td> 
                            </tr>';                
                        }
                        $inicio = $_POST['inicio'];
                $finali = $_POST['final'];
                    echo'<tr><td><button type="submit" class="btn btn-primary" ><a href="extensiones/tcpdf/pdf/pdfconcluidos.php?inicio='.$inicio.'&finali='.$finali.'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button></td></tr>';           
                    } 
                else 
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("", "NO SE ENCONTRO TRABAJOS", "error")
                        .then(function () {
                            location.href="trabajos";
                          })
                          ;
                         </script>'; 
                    }           
            }
            if($estado=='SOLICITADO')
            {   $inicio = $_POST['inicio'];
                $final = $_POST['final'];
                $traerconsulta = trabajosConcluidosModelos::Trabajossolicitados($inicio,$final);
                if($traerconsulta==true)
                    {
                        foreach ($traerconsulta as $key => $concluido) 
                        {
                            $i++;       
                            echo '<tr>
                                <td>'.$concluido['codsolicitud'].'</td>
                                <td>'.$concluido["fechasolicitud"].'</td>
                                <td>'.$concluido["nombre"].' '.$concluido["paterno"].'</td>
                                <td>'.$concluido["nroplaca"].'</td>             
                            </tr>';                
                        }
                    } 
                    else
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("", "NO SE ENCONTRO SOLICITUDES", "error")
                        .then(function () {
                            location.href="trabajos";
                          })
                          ;
                         </script>'; 
                    }
                }
                if($estado=='PROGRAMADO')
                {   $inicio = $_POST['inicio'];
                    $final = $_POST['final'];
                    $traerconsulta = trabajosConcluidosModelos::trabajosprogramados($inicio,$final);
                    if($traerconsulta==true)
                        {
                            foreach ($traerconsulta as $key => $concluido) 
                            {
                                $i++;       
                                echo '<tr>
                                    <td>'.$concluido['codsolicitud'].'</td>
                                    <td>'.$concluido["fechasolicitud"].'</td>
                                    <td>'.$concluido["nombre"].' '.$concluido["paterno"].'</td>
                                    <td>'.$concluido["nroplaca"].'</td>     
                                    <td>'.$concluido["fechatrabajo"].'</td>          
                                </tr>';                
                            }
                        } 
                        else
                        {
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("", "NO HAY TRABAJOS PROGRAMADOS", "error")
                            .then(function () {
                                location.href="trabajos";
                              })
                              ;
                             </script>'; 
                        }
                    }
        }
        else
        {
            echo'
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "FECHA INCORRECTA", "error")
            .then(function () {
                location.href="trabajos";
              })
              ;
             </script>'; 
        }
                
        
    
    }

    }
    
}