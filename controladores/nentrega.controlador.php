<?php
class NotaEntrgaControladores
{ 
    public function RegistrarNotaEntregaControlador()
    {
        if (isset($_POST["NotaEntregakit"]))
        {
            session_start();
            $IdPersonal = $_SESSION["IdPersonal"];
            $NotaEntrega = $_POST["NotaEntregakit"];
            $existe=NotaExistenteModelos::NotaKitExistenteModelo($NotaEntrega);
            if($existe==true)
            {
                echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("La nota de entrega ya existe", "", "error")
                        .then(function () {
                            location.href="nentrega";
                          })
                          ;
                         </script>';
            }
            else {
                $nmkit = MarcaKitModelos::RegistrarNotaEntregaModelo($NotaEntrega, $IdPersonal);

                if ($nmkit == 'exitoso') {
                    $tabla = 'recepcionkitkit';
                    $item = 'codrecepcion';
                    $IdRecepcion = HeredadoModelos::UltimoIdModelo($item, $tabla);
                    header('Location: index.php?action=notadetalle&CodRecepcion='.$IdRecepcion.'&&nota='.$NotaEntrega);
            }
       
            }
        
        }
    }
    public function ListaNotaentregakitControlador()
    {
        $ListaNotakit = NotaEntregaModelos::ListaNotaKitModelo();
            // PREFORMATEAR CODIGO - VARDUMP
            /*echo '<pre>';
                var_dump($ListaUsuarios);
            echo '</pre>';*/
        foreach ($ListaNotakit as $key => $Usuario) 
        {
            $i++;
    
            echo '<tr>
                <td WIDTH="50"HEIGHT="50">'.$Usuario['codrecepcion'].'</td>
                <td>'.$Usuario["fecharecepcion"].'</td>
                <td>'.$Usuario["notadeentrega"].'</td>
                <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                <td>'.$Usuario['tipo'].'</td>
                <td>                  
                <button type="button"  id="btndetallekit" idcod="'.$Usuario['codrecepcion'].'" class="btn btn-bold btn-label-brand btn-sm " data-toggle="modal" data-target="#listadenotaskit"><i class="kt-menu__link-icon flaticon-squares-4"></i></button>               
                </td>
                <td>
                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledenotakpasado.php?codigo='.$Usuario['codrecepcion'].'&fecha='.$Usuario["fecharecepcion"].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                </td>
                
            </tr>';
        }
    }
    public function ListadetalleKitControlador()
    {
        $Listadetallekit = NotaEntregaModelos::ListadetalleKitModelo($idnotadetalle);
        /*echo '<pre>';
            var_dump($ListaUsuarios);
        echo '</pre>';*/
    foreach ($Listadetallekit as $key => $Lista) 
    {
        $i++;

        echo '<tr>
            <td WIDTH="50" 
            HEIGHT="50">'.$i.'</td>
            <td>'.$Lista["seriekit"].'</td>
            <td>'.$Lista["descripcion"].'</td>
            <td>'.$Lista['tipo'].'</td>
            <td>'.$Lista['notadeentrega'].'</td> 
            </tr>';
    }
    }


   // Nota de ecilindros
   public function RegistrarNotaEntregacControlador()
    {
        if (isset($_POST["NotaEntregacil"]))
        {
            session_start();
            $IdPersonal = $_SESSION["IdPersonal"];
            $NotaEntregacil = $_POST["NotaEntregacil"];
            $existe=NotaExistenteModelos::NotaCitExistenteModelo($NotaEntregacil);
            if($existe==true)
            {
                echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("La nota de entrega ya existe", "", "error")
                        .then(function () {
                            location.href="nentregac";
                          })
                          ;
                         </script>';
            }
            else {
                $nmkit = MarcaKitModelos::RegistrarNotaEntregacilModelo($NotaEntregacil, $IdPersonal);

            if ($nmkit == 'exitoso') {
                $tabla = 'recepcioncilindro';
                $item = 'codrecepcioncil';
                $IdRecepcion = HeredadoModelos::UltimoIdModelo($item, $tabla);
                header('Location: index.php?action=notadetallec&CodRecepcioncil='.$IdRecepcion.'&&notac='.$NotaEntregacil);
            }
            }
            
        
        }
    }


    public function ListaNotaentregaCilControlador()
    {
        $ListaNotacil = NotaEntregaModelos::ListaNotacilModelo();
            // PREFORMATEAR CODIGO - VARDUMP
            /*echo '<pre>';
                var_dump($ListaUsuarios);
            echo '</pre>';*/
        foreach ($ListaNotacil as $key => $Usuario) 
        {
            $i++;
    
            echo '<tr>
                <td WIDTH="50"HEIGHT="50">'.$Usuario['codrecepcioncil'].'</td>
                <td>'.$Usuario["fecharecepcioncil"].'</td>
                <td>'.$Usuario["notadeentrega"].'</td>
                <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                <td>'.$Usuario['Tipo'].'</td>
                <td>
                <button type="button"  id="btndetallecil" idcodc="'.$Usuario['codrecepcioncil'].'" class="btn btn-bold btn-label-brand btn-sm " data-toggle="modal" data-target="#listadenotascil"><i class="kt-menu__link-icon flaticon-squares-4"></i></button> 
                </td>
                <td>
                <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledenotac.php?codigo='.$Usuario['codrecepcioncil'].'&fecha='.$Usuario["fecharecepcioncil"].'" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>
                </td>
                
            </tr>';
        }
    }
    public function ListadetallecilControlador()
    {
        $Listadetallecil = NotaEntregaModelos::ListadetalleCilModelo($idnotadetallecil);
        /*echo '<pre>';
            var_dump($ListaUsuarios);
        echo '</pre>';*/
        foreach ($Listadetallecil as $key => $Lista) 
        {
        $i++;

        echo '<tr>
            <td WIDTH="50" 
            HEIGHT="50">'.$i.'</td>
            <td>'.$Lista["seriecilindro"].'</td>
            <td>'.$Lista["descripcioncil"].'</td>
            <td>'.$Lista['capacidad'].'</td>
            <td>'.$Lista['añofab'].'</td> 
            <td>'.$Lista['notadeentrega'].'</td> 
            </tr>';
        }
    }
}