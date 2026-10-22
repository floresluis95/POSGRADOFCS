-- Usuarios de prueba para Playwright (uno por rol). SOLO para la copia "proyecto_pruebas":
-- lo aplica tests-e2e/proceso/preparar-bd.setup.js después de copiar la base. NO ejecutarlo en
-- "proyecto" ni en el servidor (la base real ya no tiene cuentas de prueba).
-- Se puede ejecutar las veces que haga falta: primero borra los que existan.
--
-- Contraseña de todos: Prueba123 (hash bcrypt, igual que los usuarios reales).
-- No crea personas: cada usuario se une al primer registro existente de
-- personal / docente / estudiante, para que sus páginas tengan datos que mostrar.

DELETE FROM usuario WHERE Usuario LIKE 'prueba\_%';

SET @hash = '$2y$10$8Q6CsenPf5piYDbV4.rDnuvLlv7Kg/Ef.4O92KsXhVjPahK5Vvffi';
SET @personal = (SELECT MIN(IdPersonal) FROM personal);
SET @docente = (SELECT MIN(DocenteID) FROM docente);
SET @estudiante = (SELECT MIN(EstudianteID) FROM estudiante);

INSERT INTO usuario (Usuario, Password, Estado, Tipo, IdPersonal, DocenteID, EstudianteID) VALUES
    ('prueba_adm', @hash, '1', 'ADM', @personal, NULL, NULL),
    ('prueba_sec', @hash, '1', 'SEC', @personal, NULL, NULL),
    ('prueba_aux', @hash, '1', 'AUX', @personal, NULL, NULL),
    ('prueba_doc', @hash, '1', 'DOC', NULL, @docente, NULL),
    ('prueba_est', @hash, '1', 'EST', NULL, NULL, @estudiante);
