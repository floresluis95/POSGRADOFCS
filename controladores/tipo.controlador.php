<?php 
class MarcaControlador
{
    public function ListaMarcaWhereControlador($IdMarca)
    {
        $ListaMarca = MarcaModelos::ListaMarcaWhereModelo($IdMarca);
        return $ListaMarca;
    }
}