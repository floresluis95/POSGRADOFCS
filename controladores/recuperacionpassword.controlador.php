<?php
/**
 * Controlador de Recuperación de Contraseña por correo (página de ingreso).
 * Paso 1: el usuario escribe su usuario o C.I. y se le envía un código de 6 dígitos.
 * Paso 2: escribe el código y su contraseña nueva.
 *
 * Las respuestas del paso 1 son siempre iguales exista o no el usuario, para que
 * nadie pueda averiguar qué usuarios o correos hay en el sistema.
 */

require_once __DIR__ . '/../modelos/recuperacionpassword.modelo.php';
require_once __DIR__ . '/usuario.controlador.php';
require_once __DIR__ . '/../core/correo.php';

class RecuperacionPasswordControlador
{
    const MAX_SOLICITUDES_USUARIO_HORA = 3;
    const MAX_SOLICITUDES_IP_HORA = 10;

    const MENSAJE_ENVIADO = 'Si los datos corresponden a un usuario con correo registrado, le enviamos un código de 6 dígitos. Revise también la carpeta de spam. Si no le llega, comuníquese con el administrador.';

    /**
     * Paso 1: enviar el código al correo registrado.
     * @return array
     */
    public static function SolicitarCodigoControlador($identificador, $ip)
    {
        $identificador = trim($identificador);
        if ($identificador === '') {
            return ['success' => false, 'mensaje' => 'Ingrese su usuario o C.I.'];
        }

        $respuesta = ['success' => true, 'mensaje' => self::MENSAJE_ENVIADO];

        $usuario = RecuperacionPasswordModelo::BuscarUsuarioModelo($identificador);
        if (!$usuario || !filter_var($usuario['Correo'], FILTER_VALIDATE_EMAIL)) {
            return $respuesta;
        }

        $recientes = RecuperacionPasswordModelo::ContarSolicitudesRecientesModelo($usuario['ID'], $ip);
        // Límite alcanzado: no se envía otro código, pero la respuesta es la misma
        // (un mensaje distinto revelaría que el usuario existe)
        if ($recientes['usuario'] >= self::MAX_SOLICITUDES_USUARIO_HORA || $recientes['ip'] >= self::MAX_SOLICITUDES_IP_HORA) {
            return $respuesta;
        }

        $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        if (!RecuperacionPasswordModelo::CrearCodigoModelo($usuario['ID'], password_hash($codigo, PASSWORD_DEFAULT), $ip)) {
            return ['success' => false, 'mensaje' => 'No se pudo generar el código. Intente más tarde.'];
        }

        $enviado = Correo::Enviar(
            $usuario['Correo'],
            $usuario['Nombre'],
            'Código para restablecer su contraseña',
            self::CuerpoHtml($usuario['Nombre'], $codigo),
            self::CuerpoTexto($usuario['Nombre'], $codigo)
        );
        if (!$enviado) {
            return ['success' => false, 'mensaje' => 'No se pudo enviar el correo. Intente más tarde o comuníquese con el administrador.'];
        }

        // Modo prueba: sin cuenta de correo configurada, el código se muestra en
        // pantalla, solo si el sistema se abre desde el mismo equipo
        if (Correo::ModoPrueba() && in_array($ip, ['127.0.0.1', '::1'], true)) {
            $respuesta['codigoPrueba'] = $codigo;
        }

        return $respuesta;
    }

    /**
     * Paso 2: verificar el código y guardar la contraseña nueva.
     * @return array
     */
    public static function CambiarPasswordControlador($identificador, $codigo, $password, $confirmacion)
    {
        $codigo = trim($codigo);
        if (!preg_match('/^\d{6}$/', $codigo)) {
            return ['success' => false, 'mensaje' => 'El código debe tener 6 dígitos'];
        }

        $errorPassword = UsuarioControladores::ValidarPasswordPersonal($password, $confirmacion);
        if ($errorPassword !== null) {
            return ['success' => false, 'mensaje' => $errorPassword];
        }

        $invalido = ['success' => false, 'mensaje' => 'El código no es válido o ya venció. Solicite uno nuevo.'];

        $usuario = RecuperacionPasswordModelo::BuscarUsuarioModelo(trim($identificador));
        if (!$usuario) {
            return $invalido;
        }

        $registro = RecuperacionPasswordModelo::ObtenerCodigoVigenteModelo($usuario['ID']);
        if (!$registro) {
            return $invalido;
        }

        if (!password_verify($codigo, $registro['CodigoHash'])) {
            RecuperacionPasswordModelo::SumarIntentoModelo($registro['IdRecuperacion']);
            $restantes = RecuperacionPasswordModelo::MAX_INTENTOS - ((int)$registro['Intentos'] + 1);
            return ['success' => false, 'mensaje' => $restantes > 0
                ? 'Código incorrecto. Le quedan ' . $restantes . ' intento(s).'
                : 'Código incorrecto. Se agotaron los intentos: solicite un código nuevo.'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        if (!RecuperacionPasswordModelo::CambiarPasswordModelo($registro['IdRecuperacion'], $usuario['ID'], $passwordHash)) {
            return $invalido;
        }

        return ['success' => true, 'mensaje' => 'Su contraseña fue cambiada. Ya puede ingresar con la nueva contraseña.'];
    }

    private static function CuerpoHtml($nombre, $codigo)
    {
        $nombre = htmlspecialchars($nombre);
        $minutos = RecuperacionPasswordModelo::MINUTOS_VIGENCIA;
        return '
        <div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;color:#333;">
            <div style="background:#1e3c72;color:#fff;padding:18px 24px;border-radius:10px 10px 0 0;">
                <strong style="font-size:16px;">Posgrado Odontología - UTO</strong>
            </div>
            <div style="border:1px solid #e3e6ef;border-top:0;padding:24px;border-radius:0 0 10px 10px;">
                <p>Hola ' . $nombre . ',</p>
                <p>Recibimos una solicitud para restablecer su contraseña. Su código es:</p>
                <p style="font-size:32px;font-weight:bold;letter-spacing:8px;text-align:center;color:#1e3c72;margin:24px 0;">' . $codigo . '</p>
                <p>El código vence en ' . $minutos . ' minutos y solo puede usarse una vez.</p>
                <p style="color:#888;font-size:13px;">Si usted no lo solicitó, ignore este correo: su contraseña no cambiará. No comparta este código con nadie.</p>
            </div>
        </div>';
    }

    private static function CuerpoTexto($nombre, $codigo)
    {
        return "Hola $nombre,\n\nSu código para restablecer la contraseña es: $codigo\n\n"
            . "Vence en " . RecuperacionPasswordModelo::MINUTOS_VIGENCIA . " minutos y solo puede usarse una vez.\n"
            . "Si usted no lo solicitó, ignore este correo.\n\nPosgrado Odontología - UTO";
    }
}
