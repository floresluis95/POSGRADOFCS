<?php
    require_once 'conexion.modelo.php';
    class IngresoModelos
    {
        public static function ValidarIngresoModelo($usuario)
        {
            // Consulta mejorada que soporta 3 tipos de usuarios: Personal, Estudiantes y Docentes
            $stmt = Conexion::Conectar()->prepare("
                SELECT
                    u.*,
                    -- Datos de Personal (si aplica)
                    p.IdPersonal,
                    p.CedulaIdentidad,
                    p.ApellidoPaterno,
                    p.ApellidoMaterno,
                    p.Nombres,
                    p.Direccion,
                    p.Celular,
                    p.Telefono,
                    -- Datos de Estudiante (si aplica)
                    e.EstudianteID,
                    e.Ci as EstudianteCi,
                    e.Complemento as EstudianteComplemento,
                    e.Exp as EstudianteExp,
                    e.Nombre as EstudianteNombre,
                    e.Apaterno as EstudianteApaterno,
                    e.Amaterno as EstudianteAmaterno,
                    e.Correo as EstudianteCorreo,
                    e.Celular as EstudianteCelular,
                    e.Direccion as EstudianteDireccion,
                    -- Datos de Docente (si aplica)
                    d.DocenteID,
                    d.Ci as DocenteCi,
                    d.Complemento as DocenteComplemento,
                    d.Exp as DocenteExp,
                    d.Nombre as DocenteNombre,
                    d.Apaterno as DocenteApaterno,
                    d.Amaterno as DocenteAmaterno,
                    d.Correo as DocenteCorreo,
                    d.Cel as DocenteCel,
                    d.Direccion as DocenteDireccion,
                    d.Especialidad as DocenteEspecialidad
                FROM usuario u
                LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal
                LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                LEFT JOIN docente d ON u.DocenteID = d.DocenteID
                WHERE u.Usuario = :usuario
            ");
            $stmt -> bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }
    }