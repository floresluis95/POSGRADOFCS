<?php
require_once __DIR__ . '/conexion.modelo.php';

if (!function_exists('SesionPersonalVigente')) {
    /**
     * El rol del personal (ADM, SEC, AUX) se guarda en la sesión al ingresar. Si después el
     * Administrador le cambia el rol o da de baja la cuenta, la sesión abierta conservaría
     * los permisos anteriores: aquí se compara con la base y, si cambió, se cierra la sesión
     * (al volver a ingresar entra con el rol nuevo). Se consulta una vez por petición.
     * @return bool false si la sesión se cerró
     */
    function SesionPersonalVigente()
    {
        static $vigente = null;
        if ($vigente !== null) {
            return $vigente;
        }
        $vigente = true;
        if (empty($_SESSION['Validar']) || empty($_SESSION['IdUsuario']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
            return $vigente;
        }
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT Tipo, Estado FROM usuario WHERE ID = :id");
            $stmt->execute([':id' => (int)$_SESSION['IdUsuario']]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('SesionPersonalVigente: ' . $e->getMessage());
            return $vigente; // si la base falla no se expulsa a nadie
        }
        if (!$cuenta || (string)$cuenta['Estado'] !== '1' || $cuenta['Tipo'] !== $_SESSION['Tipo']) {
            $_SESSION = [];
            session_destroy();
            $vigente = false;
        }
        return $vigente;
    }
}
