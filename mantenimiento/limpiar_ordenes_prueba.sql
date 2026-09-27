-- Script para limpiar órdenes de pago de prueba
-- Ejecutar en phpMyAdmin antes de generar nuevas órdenes

-- Ver órdenes pendientes actuales
SELECT
    op.NumeroOrden,
    op.FechaGeneracion,
    op.Estado,
    e.Nombre, e.Apaterno,
    p.NombrePrograma,
    op.MontoFinal
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE op.Estado = 'PENDIENTE'
ORDER BY op.FechaGeneracion DESC;

-- Ver inscripciones pendientes
SELECT
    ep.idInscripcion,
    ep.FechaInscripcion,
    ep.Estado,
    e.Nombre, e.Apaterno,
    p.NombrePrograma,
    ep.montoPagado
FROM estudianteprograma ep
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
WHERE ep.Estado = 'PENDIENTE'
ORDER BY ep.FechaInscripcion DESC;

-- OPCIÓN 1: Eliminar TODAS las órdenes de pago PENDIENTES (de prueba)
-- CUIDADO: Esto eliminará todas las órdenes pendientes, incluyendo legítimas
DELETE FROM ordenpago WHERE Estado = 'PENDIENTE';

-- OPCIÓN 2: Eliminar solo inscripciones PENDIENTES (órdenes de pago sin pagar)
-- Primero eliminar de pagomodulo (si hay)
DELETE FROM pagomodulo
WHERE idinscripcion IN (
    SELECT idInscripcion
    FROM estudianteprograma
    WHERE Estado = 'PENDIENTE'
);

-- Luego eliminar de estudianteprograma
DELETE FROM estudianteprograma WHERE Estado = 'PENDIENTE';

-- OPCIÓN 3: Eliminar solo órdenes de HOY (más seguro)
DELETE FROM ordenpago
WHERE Estado = 'PENDIENTE'
  AND DATE(FechaGeneracion) = CURDATE();

DELETE FROM estudianteprograma
WHERE Estado = 'PENDIENTE'
  AND DATE(FechaInscripcion) = CURDATE();

-- OPCIÓN 4: Eliminar una orden específica por número
-- Reemplaza 'ORD-000002-20251219' con el número que causa el error
DELETE FROM ordenpago WHERE NumeroOrden = 'ORD-000002-20251219';

DELETE FROM estudianteprograma
WHERE idInscripcion = (
    SELECT idInscripcion
    FROM ordenpago
    WHERE NumeroOrden = 'ORD-000002-20251219'
);

-- VERIFICAR que se eliminaron correctamente
SELECT COUNT(*) as OrdenesRestantes FROM ordenpago WHERE Estado = 'PENDIENTE';
SELECT COUNT(*) as InscripcionesRestantes FROM estudianteprograma WHERE Estado = 'PENDIENTE';
