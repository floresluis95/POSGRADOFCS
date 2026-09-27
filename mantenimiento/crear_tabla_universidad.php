<?php
/**
 * Script para crear la tabla del catálogo de universidades (de dónde egresó
 * el estudiante antes de inscribirse al posgrado) y agregar a "estudiante"
 * las columnas IdUniversidad y FechaEgreso.
 */

require_once file_exists(__DIR__ . '/modelos/conexion.modelo.php') ? __DIR__ . '/modelos/conexion.modelo.php' : __DIR__ . '/core/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    $sql = "CREATE TABLE IF NOT EXISTS universidad (
        IdUniversidad INT AUTO_INCREMENT PRIMARY KEY,
        NombreUniversidad VARCHAR(150) NOT NULL,
        TipoUniversidad ENUM('PUBLICA','PRIVADA','EXTRANJERA','CONVENIO') NOT NULL,
        Estado TINYINT(1) NOT NULL DEFAULT 1,
        FechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_universidad_nombre (NombreUniversidad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    COMMENT='Catálogo de universidades de egreso de los estudiantes (pública/privada/extranjera/convenio)'";

    $pdo->exec($sql);
    echo "✓ Tabla 'universidad' creada exitosamente\n";

    // Columnas nuevas en estudiante: nullable a propósito (no todos los
    // estudiantes ya registrados tienen este dato, y otros flujos de alta
    // rápida -como el registro desde Pre-Registro- no lo envían).
    $columnas = $pdo->query("SHOW COLUMNS FROM estudiante LIKE 'IdUniversidad'")->fetchAll();
    if (count($columnas) === 0) {
        $pdo->exec("ALTER TABLE estudiante
            ADD COLUMN IdUniversidad INT NULL AFTER IdProfesion,
            ADD COLUMN FechaEgreso DATE NULL AFTER IdUniversidad,
            ADD KEY idx_estudiante_universidad (IdUniversidad),
            ADD CONSTRAINT fk_estudiante_universidad FOREIGN KEY (IdUniversidad)
                REFERENCES universidad (IdUniversidad) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "✓ Columnas 'IdUniversidad' y 'FechaEgreso' agregadas a 'estudiante'\n";
    } else {
        echo "→ La columna 'IdUniversidad' ya existía en 'estudiante', no se modificó\n";
    }

    echo "\n=== ESTRUCTURA DE LA TABLA universidad ===\n";
    foreach ($pdo->query("DESCRIBE universidad") as $campo) {
        echo "- {$campo['Field']}: {$campo['Type']}\n";
    }

    echo "\n✓ Script completado exitosamente\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
