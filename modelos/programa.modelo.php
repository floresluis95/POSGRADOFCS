<?php
  require_once 'conexion.modelo.php';
  //PROGRAMAS
class ProgramasModelos
{
    public static function ListaProgramaModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa ORDER BY FechaInicio DESC");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt =null;
    }

    // Buscar programas con filtros
    public static function BuscarProgramasConFiltros($grado = null, $fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT * FROM programa WHERE 1=1";
        $params = array();

        // Filtro por grado académico
        if ($grado !== null && $grado !== '') {
            $sql .= " AND GradoAcademico = :grado";
            $params[':grado'] = $grado;
        }

        // Filtro por fecha inicio (desde)
        if ($fechaInicio !== null && $fechaInicio !== '') {
            $sql .= " AND FechaInicio >= :fechaInicio";
            $params[':fechaInicio'] = $fechaInicio;
        }

        // Filtro por fecha fin (hasta)
        if ($fechaFin !== null && $fechaFin !== '') {
            $sql .= " AND FechaInicio <= :fechaFin";
            $params[':fechaFin'] = $fechaFin;
        }

        $sql .= " ORDER BY FechaInicio DESC";

        $stmt = Conexion::Conectar()->prepare($sql);

        // Vincular parámetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;

        return $resultado;
    }

   public static function RegistrarProgramaModelo($datos)
{
    // Generar código automáticamente
    $codigo = self::GenerarCodigo($datos["Sede"], $datos["GradoAcademico"], $datos["FechaInicio"]);

    $stmt = Conexion::conectar()->prepare("
        INSERT INTO programa (NombrePrograma, GradoAcademico, Codigo, DuracionMeses, Modulos, FechaInicio, Sede, Costo, CostoMatricula, Detalle, Estado, Version, NumeroTramite)
        VALUES (:nombre, :grado, :codigo, :duracionmeses, :modulos, :fecha, :sede, :costo, :costomatricula, :detalle, 'ACTIVO', :version, :numerotramite)
    ");

    $stmt->bindParam(":nombre", $datos["NombrePrograma"], PDO::PARAM_STR);
    $stmt->bindParam(":grado", $datos["GradoAcademico"], PDO::PARAM_STR);
    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
    $stmt->bindParam(":duracionmeses", $datos["DuracionMeses"], PDO::PARAM_INT);
    $stmt->bindParam(":modulos", $datos["Modulos"], PDO::PARAM_INT);
    $stmt->bindParam(":fecha", $datos["FechaInicio"], PDO::PARAM_STR);
    $stmt->bindParam(":sede", $datos["Sede"], PDO::PARAM_STR);
    $stmt->bindParam(":costo", $datos["Costo"], PDO::PARAM_INT);
    $stmt->bindParam(":costomatricula", $datos["CostoMatricula"], PDO::PARAM_STR);
    $stmt->bindParam(":detalle", $datos["Detalle"], PDO::PARAM_STR);
    $stmt->bindParam(":version", $datos["Version"], PDO::PARAM_STR);
    $stmt->bindParam(":numerotramite", $datos["NumeroTramite"], PDO::PARAM_STR);
    if ($stmt->execute()) {
        return "exitoso";
    } else {
        print_r($stmt->errorInfo()); // para depuración
        return "error";
    }

    $stmt = null;
} 
 
private static function GenerarCodigo($sede, $grado, $fechaInicio)
{
    // Convertir a mayúsculas por consistencia
    $sede = strtoupper(trim($sede));
    $grado = strtoupper(trim($grado));

    // --- Prefijo por sede ---
    switch ($sede) {
        case 'ORURO':
            $prefijoSede = 'ORU';
            break;
        case 'LA PAZ':
            $prefijoSede = 'LPZ';
            break;
        case 'COCHABAMBA':
            $prefijoSede = 'CBB';
            break;
        case 'SANTA CRUZ':
            $prefijoSede = 'SCZ';
            break;
        default:
            $prefijoSede = 'OTR'; // Otro
            break;
    }

    // --- Prefijo por grado ---
    switch ($grado) {
        case 'DIPLOMADO':
            $prefijoGrado = 'DIP';
            break;
        case 'MAESTRÍA':
        case 'MAESTRIA':
            $prefijoGrado = 'MAE';
            break;
        case 'ESPECIALIDAD':
            $prefijoGrado = 'ESP';
            break;
        default:
            $prefijoGrado = 'OTR';
            break;
    }

    // --- Obtener año ---
    $anio = date('Y', strtotime($fechaInicio));

    // --- Contar programas existentes ---
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM programa");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)["total"] + 1;

    // --- Armar el código final ---
    $codigo = "{$prefijoSede}-{$prefijoGrado}-{$total}-{$anio}";
    return $codigo;
}
    public static function Buscarprogramamodelo($nombreprograma)
    {
        $stmt =conexion::Conectar()->prepare("SELECT * FROM `programa`where NombrePrograma= :nombreprograma");
        $stmt->bindParam(":nombreprograma", $nombreprograma, PDO::PARAM_STR);
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt =null;
    }

    // Actualizar programa existente
    public static function ActualizarProgramaModelo($datos)
    {
        $stmt = Conexion::conectar()->prepare("
            UPDATE programa
            SET NombrePrograma = :nombre,
                GradoAcademico = :grado,
                DuracionMeses = :duracionmeses,
                Modulos = :modulos,
                FechaInicio = :fecha,
                Sede = :sede,
                Costo = :costo,
                CostoMatricula = :costomatricula,
                Detalle = :detalle,
                Version = :version,
                NumeroTramite = :numerotramite
            WHERE ProgramaID = :id
        ");

        $stmt->bindParam(":id", $datos["ProgramaID"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["NombrePrograma"], PDO::PARAM_STR);
        $stmt->bindParam(":grado", $datos["GradoAcademico"], PDO::PARAM_STR);
        $stmt->bindParam(":duracionmeses", $datos["DuracionMeses"], PDO::PARAM_INT);
        $stmt->bindParam(":modulos", $datos["Modulos"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["FechaInicio"], PDO::PARAM_STR);
        $stmt->bindParam(":sede", $datos["Sede"], PDO::PARAM_STR);
        $stmt->bindParam(":costo", $datos["Costo"], PDO::PARAM_STR);
        $stmt->bindParam(":costomatricula", $datos["CostoMatricula"], PDO::PARAM_STR);
        $stmt->bindParam(":detalle", $datos["Detalle"], PDO::PARAM_STR);
        $stmt->bindParam(":version", $datos["Version"], PDO::PARAM_STR);
        $stmt->bindParam(":numerotramite", $datos["NumeroTramite"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "exitoso";
        } else {
            print_r($stmt->errorInfo());
            return "error";
        }

        $stmt = null;
    }  

    //detalle programa
    public static function MostrarDetallePrograma($id) {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM programa WHERE ProgramaID = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}

// Estados del programa
class ProgramaEstadoModelo
{
  public static function SubirProgramaModelo($id)
{
    $stmt = Conexion::conectar()->prepare("UPDATE programa SET Estado = 'ACTIVO' WHERE ProgramaID = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return "exitoso";
    } else {
        // 👇 para ver el error exacto de SQL
        print_r($stmt->errorInfo());
        return "error";
    }

    $stmt = null;
}

    public static function BajarProgramaModelo($id) {
        $stmt = Conexion::conectar()->prepare("UPDATE programa SET Estado = 'INACTIVO' WHERE ProgramaID = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "exitoso";
        } else {
            return "error";
        }

        $stmt = null;
    }
   
}
class ProgramaModelo {

  public static function MostrarPorGrado($grado) {
   
    $stmt = Conexion::conectar()->prepare("SELECT * FROM programa WHERE GradoAcademico = :grado AND Estado = 1");
    $stmt->bindParam(":grado", $grado, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}