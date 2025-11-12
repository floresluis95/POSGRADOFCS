-- ============================================
-- Script para Insertar Estudiantes de Prueba
-- Sistema de Posgrado FCS
-- ============================================

USE `proyecto`;

-- Insertar 10 estudiantes de prueba
INSERT INTO `estudiante` (`Ci`, `Complemento`, `Exp`, `Nombre`, `Apaterno`, `Amaterno`, `FechaNacimiento`, `Edad`, `Lugarn`, `Correo`, `Profesion`, `Trabajo`, `Direccion`, `Telefono`, `Celular`, `Estado`) VALUES
('12345678', '', 'LP', 'Juan Carlos', 'Pérez', 'Mamani', '1990-05-15', 34, 'La Paz', 'juan.perez@example.com', 'Ingeniero de Sistemas', 'Empresa Tech SA', 'Av. Arce #1234', '2234567', '71234567', 1),
('23456789', '1A', 'CB', 'María Elena', 'González', 'Quispe', '1988-08-22', 36, 'Cochabamba', 'maria.gonzalez@example.com', 'Contadora Pública', 'Auditores Asociados', 'Calle Junín #456', '4445678', '72345678', 1),
('34567890', '', 'SC', 'Roberto', 'Fernández', 'Choque', '1992-03-10', 32, 'Santa Cruz', 'roberto.fernandez@example.com', 'Administrador de Empresas', 'Corporación XYZ', 'Av. Cristobal de Mendoza #789', '3556789', '73456789', 1),
('45678901', '2B', 'LP', 'Ana Patricia', 'Flores', 'Condori', '1995-11-28', 29, 'El Alto', 'ana.flores@example.com', 'Licenciada en Economía', 'Ministerio de Economía', 'Calle 16 de Julio #321', '2667890', '74567890', 1),
('56789012', '', 'OR', 'Carlos Alberto', 'Vargas', 'Huanca', '1987-07-05', 37, 'Oruro', 'carlos.vargas@example.com', 'Ingeniero Industrial', 'Minera del Sur', 'Av. 6 de Agosto #654', '5278901', '75678901', 1),
('67890123', '3C', 'LP', 'Sandra Paola', 'Morales', 'Apaza', '1993-02-18', 31, 'La Paz', 'sandra.morales@example.com', 'Licenciada en Derecho', 'Bufete Jurídico Legal', 'Calle Comercio #987', '2389012', '76789012', 1),
('78901234', '', 'SC', 'Luis Fernando', 'Castro', 'Nina', '1991-09-30', 33, 'Santa Cruz', 'luis.castro@example.com', 'Arquitecto', 'Construcciones Modernas', 'Av. Banzer #147', '3490123', '77890123', 1),
('89012345', '4D', 'CB', 'Patricia Isabel', 'Gutiérrez', 'Mamani', '1989-12-12', 35, 'Cochabamba', 'patricia.gutierrez@example.com', 'Médica Cirujana', 'Hospital Central', 'Calle Ayacucho #258', '4501234', '78901234', 1),
('90123456', '', 'PT', 'Jorge Andrés', 'Mendoza', 'Ticona', '1994-06-25', 30, 'Potosí', 'jorge.mendoza@example.com', 'Ingeniero Civil', 'Obras Públicas', 'Av. Villazon #369', '6612345', '79012345', 1),
('01234567', '5E', 'LP', 'Verónica Cristina', 'Rojas', 'Callisaya', '1996-04-08', 28, 'La Paz', 'veronica.rojas@example.com', 'Licenciada en Marketing', 'Agencia Creativa Plus', 'Calle Potosí #741', '2723456', '70123456', 1),
('11111111', '', 'LP', 'Diego Alejandro', 'Sánchez', 'Condori', '1990-01-15', 34, 'La Paz', 'diego.sanchez@example.com', 'Licenciado en Educación', 'Colegio San Agustín', 'Av. 20 de Octubre #852', '2834567', '71111111', 1),
('22222222', '6F', 'CB', 'Carla Andrea', 'Torres', 'Flores', '1992-10-20', 32, 'Cochabamba', 'carla.torres@example.com', 'Psicóloga Clínica', 'Centro de Salud Mental', 'Calle España #963', '4945678', '72222222', 1),
('33333333', '', 'SC', 'Miguel Ángel', 'Cortez', 'Quispe', '1988-05-03', 36, 'Santa Cruz', 'miguel.cortez@example.com', 'Ingeniero Agrónomo', 'Agroindustrias del Oriente', 'Av. Roca y Coronado #159', '3056789', '73333333', 1),
('44444444', '7G', 'LP', 'Gabriela Susana', 'Ramírez', 'Mamani', '1991-08-17', 33, 'La Paz', 'gabriela.ramirez@example.com', 'Licenciada en Turismo', 'Agencia de Viajes Andes', 'Calle Sagarnaga #357', '2167890', '74444444', 1),
('55555555', '', 'OR', 'Fernando José', 'Paredes', 'Choque', '1993-03-29', 31, 'Oruro', 'fernando.paredes@example.com', 'Contador Auditor', 'Tributaria Nacional', 'Av. Cívica #468', '5578901', '75555555', 1);

-- Verificar la inserción
SELECT COUNT(*) AS 'Total Estudiantes Insertados' FROM `estudiante` WHERE `Estado` = 1;

-- Mostrar los estudiantes insertados
SELECT
    EstudianteID,
    CONCAT(Nombre, ' ', Apaterno, ' ', Amaterno) AS 'Nombre Completo',
    Ci,
    Correo,
    Profesion,
    Celular
FROM `estudiante`
WHERE `Estado` = 1
ORDER BY EstudianteID DESC
LIMIT 15;

-- ============================================
-- Instrucciones:
-- 1. Abrir phpMyAdmin
-- 2. Seleccionar la base de datos 'proyecto'
-- 3. Ir a la pestaña SQL
-- 4. Copiar y pegar este script
-- 5. Ejecutar
-- ============================================
