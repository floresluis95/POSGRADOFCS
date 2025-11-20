<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Inscripción
 * Gestiona inscripciones, planes de pago, cuotas y vouchers
 * Compatible con PHP 8 - PDO
 */
class InscripcionModelos
{
    /**
     * Obtener detalles de un programa por ID
     * @param int $programaID
     * @return array|false
     */
    public static function ObtenerDetalleProgramaModelo($programaID)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ProgramaID, NombrePrograma, GradoAcademico, Codigo, DuracionMeses,
                    Modulos, FechaInicio, Sede, Costo, CostoMatricula, Detalle, Estado
             FROM programa
             WHERE ProgramaID = :programaID AND Estado = 1"
        );
        $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar programas por grado académico
     * @param string $gradoAcademico
     * @return array
     */
    public static function ListarProgramasPorGradoModelo($gradoAcademico)
    {
        try {
            error_log("ListarProgramasPorGradoModelo - Buscando: " . $gradoAcademico);

            // Normalizar el grado académico (sin acentos)
            $gradoNormalizado = str_replace(['Í', 'í'], ['I', 'i'], $gradoAcademico);
            error_log("ListarProgramasPorGradoModelo - Grado normalizado: " . $gradoNormalizado);

            $pdo = Conexion::Conectar();

            // Buscar con ambas variantes (con y sin tilde)
            $stmt = $pdo->prepare(
                "SELECT ProgramaID, NombrePrograma, GradoAcademico, Codigo, Costo, CostoMatricula, Modulos, Sede
                 FROM programa
                 WHERE (GradoAcademico = :gradoAcademico
                    OR GradoAcademico = :gradoNormalizado
                    OR REPLACE(REPLACE(GradoAcademico, 'Í', 'I'), 'í', 'i') = :gradoNormalizado)
                    AND Estado = 1
                 ORDER BY NombrePrograma ASC"
            );
            $stmt->bindParam(":gradoAcademico", $gradoAcademico, PDO::PARAM_STR);
            $stmt->bindParam(":gradoNormalizado", $gradoNormalizado, PDO::PARAM_STR);
            $stmt->execute();

            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("ListarProgramasPorGradoModelo - Resultados encontrados: " . count($resultados));

            if (count($resultados) > 0) {
                error_log("ListarProgramasPorGradoModelo - Primer resultado: " . print_r($resultados[0], true));
            }

            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en ListarProgramasPorGradoModelo: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return [];
        }
    }
}

