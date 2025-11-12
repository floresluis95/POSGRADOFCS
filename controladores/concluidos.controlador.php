<?php
class ListaDeTrabajosControladores
{ 
    public function ListaDeTrabajosConcluidosControlador()
    {
        $ListaConcluidos = ConcluidosModelos::ListaTrabajosConcluidos();
            // PREFORMATEAR CODIGO - VARDUMP
            /*echo '<pre>';
                var_dump($Listaconcluidos);
            echo '</pre>';*/
            
        foreach ($ListaConcluidos as $key => $concluido) 
        {
            $i++;
    
            echo '<tr>
                <td>'.$concluido['codsolicitud'].'</td>
                <td>'.$concluido['fechasolicitud'].'</td>
                <td>'.$concluido['nroplaca'].'</td>
                <td>'.$concluido["fechatrabajo"].'</td>
                <td>'.$concluido["fechaconcluido"].'</td>
                <td>'.$concluido['Nombres'].' '.$concluido["ApellidoPaterno"].'</td>  
                <td>'.$concluido['estado'].'</td>  
                <td>
                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledesolicitud.php?codigo='.$concluido['codsolicitud'].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                </td> 
               
                
            </tr>';
        }
    }
    


}