<?php
    class TecnicoControladores
    {

        public function ListaTecnicoControlador()
        {
                $TraerListaTecnico = TecnicoModelos:: ListaTecnicoModelo();
                foreach ($TraerListaTecnico as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["IdPersonal"].'">'.$value["Nombres"].' '.$value["ApellidoPaterno"].' '.$value["ApellidoMaterno"].'</option>';
                }      
        }
        
    }