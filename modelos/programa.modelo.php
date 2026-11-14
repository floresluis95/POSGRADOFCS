<?php
  require_once __DIR__ . '/../core/conexion.modelo.php';
  //PROGRAMAS
class ProgramasModelos
{
    public static function ListaProgramaModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 ORDER BY FechaInicio DESC");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt =null;
    }

    // Listar sedes distintas registradas en programas activos
    public static function ListarSedesModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT DISTINCT Sede FROM programa WHERE Estado = 1 AND Sede IS NOT NULL AND Sede <> '' ORDER BY Sede ASC");
        $stmt -> execute();
        return $stmt -> fetchAll(PDO::FETCH_COLUMN);
    }

    // Buscar programas con filtros
    /**
     * @param bool $ultimosPrimero true: el último programa registrado primero (Pre-Registro);
     *                             false: por fecha de inicio (lista de Programas)
     */
    public static function BuscarProgramasConFiltros($grado = null, $fechaInicio = null, $fechaFin = null, $sede = null, $ultimosPrimero = false)
    {
        $sql = "SELECT * FROM programa WHERE Estado = 1";
        $params = array();

        // Filtro por grado académico
        if ($grado !== null && $grado !== '') {
            $sql .= " AND GradoAcademico = :grado";
            $params[':grado'] = $grado;
        }

        // Filtro por sede
        if ($sede !== null && $sede !== '') {
            $sql .= " AND Sede = :sede";
            $params[':sede'] = $sede;
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

        $sql .= $ultimosPrimero ? " ORDER BY ProgramaID DESC" : " ORDER BY FechaInicio DESC";

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

    // Sigla: identificador corto y legible del programa (distinto del Codigo
    // institucional autogenerado), definido por quien registra el programa.
    $sigla = !empty(trim($datos["Sigla"] ?? '')) ? strtoupper(trim($datos["Sigla"])) : null;

    $stmt = Conexion::conectar()->prepare("
        INSERT INTO programa (NombrePrograma, GradoAcademico, Codigo, Sigla, DuracionMeses, Modulos, FechaInicio, Sede, Costo, CostoMatricula, Detalle, Estado, Version, NumeroTramite)
        VALUES (:nombre, :grado, :codigo, :sigla, :duracionmeses, :modulos, :fecha, :sede, :costo, :costomatricula, :detalle, '1', :version, :numerotramite)
    ");

    $stmt->bindParam(":nombre", $datos["NombrePrograma"], PDO::PARAM_STR);
    $stmt->bindParam(":grado", $datos["GradoAcademico"], PDO::PARAM_STR);
    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
    $stmt->bindParam(":sigla", $sigla, PDO::PARAM_STR);
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
        error_log("Error SQL: " . print_r($stmt->errorInfo(), true)); // para depuración
        return "error";
    }

    $stmt = null;
}

/**
 * Código institucional del programa: SEDE-GRADO-N-AÑO (ej. ORU-ESP-1-2024).
 *
 * N es el consecutivo PROPIO de cada sede y grado: la 1.ª especialidad de Oruro es
 * ORU-ESP-1, la 2.ª ORU-ESP-2…; las maestrías y diplomados de Oruro llevan su propia
 * cuenta (ORU-MAE-1, ORU-DIP-1) y lo mismo cada sede (LPZ-ESP-1). El consecutivo no
 * vuelve a 1 cada año: el año (de la fecha de inicio) solo acompaña al número.
 * Antes N era el total de programas del sistema, y especialidades, maestrías y
 * diplomados compartían la numeración (2026-11-14).
 *
 * Al editar ($programaID): si sigue en la misma sede y grado conserva su número y
 * solo se actualiza el año; si cambió de sede o de grado toma el siguiente número
 * de su nueva categoría.
 */
public static function GenerarCodigo($sede, $grado, $fechaInicio, $programaID = null)
{
    $prefijo = self::PrefijoSede($sede) . '-' . self::PrefijoGrado($grado) . '-';
    $anio = date('Y', strtotime($fechaInicio));
    $conexion = Conexion::conectar();

    if ($programaID) {
        $stmt = $conexion->prepare("SELECT Codigo FROM programa WHERE ProgramaID = :id");
        $stmt->execute([':id' => $programaID]);
        $actual = (string)$stmt->fetchColumn();
        if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)-\d{4}$/', $actual, $m)) {
            return $prefijo . $m[1] . '-' . $anio;
        }
    }

    // Siguiente número de la categoría (el mayor + 1: si se borró uno, no se repite)
    $stmt = $conexion->prepare(
        "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(Codigo, '-', 3), '-', -1) AS UNSIGNED))
         FROM programa WHERE Codigo LIKE :prefijo AND ProgramaID <> :id"
    );
    $stmt->execute([':prefijo' => $prefijo . '%', ':id' => (int)$programaID]);
    $numero = (int)$stmt->fetchColumn() + 1;

    return $prefijo . $numero . '-' . $anio;
}

/** ORURO → ORU, LA PAZ → LPZ…; otra sede: sus 3 primeras letras (POTOSÍ → POT). */
public static function PrefijoSede($sede)
{
    $sede = mb_strtoupper(trim((string)$sede), 'UTF-8');
    $conocidas = ['ORURO' => 'ORU', 'LA PAZ' => 'LPZ', 'COCHABAMBA' => 'CBB', 'SANTA CRUZ' => 'SCZ'];
    if (isset($conocidas[$sede])) {
        return $conocidas[$sede];
    }
    $letras = preg_replace('/[^A-Z]/', '', strtr($sede, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N', 'Ü' => 'U']));
    return $letras !== '' ? substr($letras, 0, 3) : 'OTR';
}

/** DIPLOMADO → DIP, ESPECIALIDAD → ESP, MAESTRÍA → MAE, DOCTORADO → DOC. */
public static function PrefijoGrado($grado)
{
    $grado = mb_strtoupper(trim((string)$grado), 'UTF-8');
    $grados = ['DIPLOMADO' => 'DIP', 'ESPECIALIDAD' => 'ESP', 'MAESTRIA' => 'MAE', 'MAESTRÍA' => 'MAE', 'DOCTORADO' => 'DOC'];
    return $grados[$grado] ?? 'OTR';
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
        $sigla = !empty(trim($datos["Sigla"] ?? '')) ? strtoupper(trim($datos["Sigla"])) : null;
        // El código sigue a la sede, el grado y el año de inicio (ver GenerarCodigo)
        $codigo = self::GenerarCodigo($datos["Sede"], $datos["GradoAcademico"], $datos["FechaInicio"], (int)$datos["ProgramaID"]);

        $stmt = Conexion::conectar()->prepare("
            UPDATE programa
            SET NombrePrograma = :nombre,
                GradoAcademico = :grado,
                Codigo = :codigo,
                Sigla = :sigla,
                DuracionMeses = :duracionmeses,
                Modulos = :modulos,
                FechaInicio = :fecha,
                Sede = :sede,
                Costo = :costo,
                CostoMatricula = :costomatricula,
                Detalle = :detalle,
                Version = :version
            WHERE ProgramaID = :id
        ");

        $stmt->bindParam(":id", $datos["ProgramaID"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["NombrePrograma"], PDO::PARAM_STR);
        $stmt->bindParam(":grado", $datos["GradoAcademico"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        $stmt->bindParam(":sigla", $sigla, PDO::PARAM_STR);
        $stmt->bindParam(":duracionmeses", $datos["DuracionMeses"], PDO::PARAM_INT);
        $stmt->bindParam(":modulos", $datos["Modulos"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["FechaInicio"], PDO::PARAM_STR);
        $stmt->bindParam(":sede", $datos["Sede"], PDO::PARAM_STR);
        $stmt->bindParam(":costo", $datos["Costo"], PDO::PARAM_STR);
        $stmt->bindParam(":costomatricula", $datos["CostoMatricula"], PDO::PARAM_STR);
        $stmt->bindParam(":detalle", $datos["Detalle"], PDO::PARAM_STR);
        $stmt->bindParam(":version", $datos["Version"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "exitoso";
        } else {
            error_log("Error SQL: " . print_r($stmt->errorInfo(), true));
            return "error";
        }

        $stmt = null;
    }  

    //detalle programa
    public static function MostrarDetallePrograma($id) {
    // El alias NumeroTramite se repite a propósito: PDO se queda con el
    // último valor de una columna duplicada, así que esto sobrescribe el
    // p.NumeroTramite crudo del SELECT p.* con la cuenta MATRICULA principal
    // (o el propio NumeroTramite si el programa aún no tiene cuentas).
    $stmt = Conexion::conectar()->prepare("
        SELECT p.*,
            COALESCE(
                (SELECT pc.NumeroCuenta FROM programacuenta pc
                 WHERE pc.ProgramaID = p.ProgramaID AND pc.TipoCuenta = 'MATRICULA' AND pc.Principal = 1 AND pc.Estado = 1
                 LIMIT 1),
                p.NumeroTramite
            ) AS NumeroTramite
        FROM programa p
        WHERE p.ProgramaID = :id
    ");
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
    $stmt = Conexion::conectar()->prepare("UPDATE programa SET Estado = '1' WHERE ProgramaID = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return "exitoso";
    } else {
        // 👇 para ver el error exacto de SQL
        error_log("Error SQL: " . print_r($stmt->errorInfo(), true));
        return "error";
    }

    $stmt = null;
}

    public static function BajarProgramaModelo($id) {
        $stmt = Conexion::conectar()->prepare("UPDATE programa SET Estado = '0' WHERE ProgramaID = :id");
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
