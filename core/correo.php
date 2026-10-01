<?php
/**
 * Envío de correos del sistema (por ahora, los códigos de recuperación de
 * contraseña) con PHPMailer por SMTP. Los datos de la cuenta están en
 * config/correo.php (ver config/correo.ejemplo.php).
 */

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Correo
{
    private static $config = null;

    /**
     * Configuración de config/correo.php; si el archivo no existe se trabaja en
     * modo prueba (no se envía nada).
     * @return array
     */
    public static function Config()
    {
        if (self::$config === null) {
            $archivo = __DIR__ . '/../config/correo.php';
            self::$config = file_exists($archivo) ? require $archivo : ['modo_prueba' => true];
        }
        return self::$config;
    }

    public static function ModoPrueba()
    {
        return !empty(self::Config()['modo_prueba']);
    }

    /**
     * @param string $para Correo del destinatario
     * @param string $nombre Nombre del destinatario
     * @param string $asunto
     * @param string $html Cuerpo en HTML
     * @param string $texto Cuerpo en texto plano (clientes sin HTML)
     * @return bool true si se envió (o si está en modo prueba)
     */
    public static function Enviar($para, $nombre, $asunto, $html, $texto)
    {
        if (self::ModoPrueba()) {
            return true;
        }

        $config = self::Config();
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = (int)$config['puerto'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['usuario'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = (int)$config['puerto'] === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Timeout = 15;

            $mail->setFrom($config['usuario'], $config['remitente_nombre'] ?? 'Posgrado');
            $mail->addAddress($para, $nombre);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->AltBody = $texto;

            return $mail->send();
        } catch (PHPMailerException $e) {
            error_log('Error al enviar correo a ' . $para . ': ' . $mail->ErrorInfo);
            return false;
        }
    }
}
