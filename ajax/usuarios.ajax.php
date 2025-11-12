<?php
require_once '../controladores/usuario.controlador.php';
require_once '../modelos/usuario.modelo.php';
class AjaxUsuarios{
    public $idUsuario;
    public function ajaxEditarUsuario(){
        $item ="id";
        $valor= $this->idUsuario;
        $respuesta = UsuarioControladores::ListaUsuariosControlador();
        echo json_encode($respuesta);
    }
}

if(isset($_POST["idUsuario"])){
    $editar = new AjaxUsuarios();
    $editar -> CedulaIdentidad =$_POST["idUsuario"];
    $editar -> ajaxEditarUsuario();
}
