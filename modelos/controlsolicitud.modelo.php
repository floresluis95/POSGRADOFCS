<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Control de envíos repetidos (tabla `solicitudprocesada`).
 *
 * Problema: el usuario guarda algo, el servidor lo registra, pero se corta internet
 * antes de que llegue la respuesta. El usuario cree que falló, vuelve a presionar el
 * botón y el registro (orden, pago, etc.) queda duplicado.
 *
 * Solución: el navegador (control-envio.js) manda en cada POST a ajax/ la cabecera
 * X-Solicitud-Id, y repite el MISMO identificador si reenvía los mismos datos tras
 * un corte. Aquí:
 *  - identificador nuevo: se ejecuta normal y se guarda la respuesta;
 *  - identificador ya terminado: se devuelve la respuesta guardada sin volver a ejecutar;
 *  - identificador todavía en proceso (doble clic, reintento rápido): se espera a que
 *    termine el primero y se devuelve su respuesta.
 *
 * Los datos incompletos no dependen de esto: las operaciones de varios pasos usan
 * transacción (todo o nada). Si la tabla no existe todavía, el control no hace nada
 * y el sistema funciona como antes.
 */
class ControlSolicitudModelo
{
    const ESPERA_MAXIMA = 25;          // segundos que se espera a un envío igual en proceso
    const PROCESO_ABANDONADO = 300;    // segundos tras los que un PROCESANDO se da por muerto
    const RESPUESTA_MAXIMA = 4194304;  // bytes: respuestas más grandes no se guardan (listados)

    private static $id = null;
    private static $respuesta = '';

    public static function Iniciar()
    {
        $id = $_SERVER['HTTP_X_SOLICITUD_ID'] ?? '';
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id)) {
            return;
        }
        // Si el usuario pierde la conexión a mitad, PHP termina igual lo que empezó
        // (y guarda la respuesta para cuando reintente).
        ignore_user_abort(true);

        try {
            $pdo = Conexion::Conectar();
            if (mt_rand(1, 50) === 1) {
                $pdo->exec("DELETE FROM solicitudprocesada WHERE Fecha < NOW() - INTERVAL 2 DAY");
            }
            $url = substr(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', -255);
            $limite = time() + self::ESPERA_MAXIMA;

            while (true) {
                if (self::Registrar($pdo, $id, $url)) {
                    self::$id = $id;
                    ob_start([self::class, 'Capturar']);
                    register_shutdown_function([self::class, 'Terminar']);
                    return;
                }

                $stmt = $pdo->prepare("SELECT Estado, CodigoHttp, TipoContenido, Respuesta,
                                              TIMESTAMPDIFF(SECOND, Fecha, NOW()) AS Segundos
                                       FROM solicitudprocesada WHERE IdSolicitud = :id");
                $stmt->execute([':id' => $id]);
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($fila && $fila['Estado'] === 'TERMINADA') {
                    http_response_code((int)$fila['CodigoHttp'] ?: 200);
                    if ($fila['TipoContenido']) {
                        header('Content-Type: ' . $fila['TipoContenido']);
                    }
                    header('X-Solicitud-Repetida: 1');
                    echo $fila['Respuesta'];
                    exit;
                }
                if ($fila && (int)$fila['Segundos'] > self::PROCESO_ABANDONADO) {
                    // El primer intento murió sin terminar (se reinició el servidor): se borra y se ejecuta
                    $pdo->prepare("DELETE FROM solicitudprocesada WHERE IdSolicitud = :id AND Estado = 'PROCESANDO'")
                        ->execute([':id' => $id]);
                    continue;
                }
                if ($fila && time() >= $limite) {
                    http_response_code(409);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'status' => 'error',
                        'mensaje' => 'Su envío anterior todavía se está procesando. Espere unos segundos y revise si se guardó antes de volver a intentar.',
                    ]);
                    exit;
                }
                // En proceso: se espera. Si la fila desapareció (el primero falló), se vuelve a intentar.
                usleep(500000);
            }
        } catch (Throwable $e) {
            // Sin la tabla (falta la migración) o sin base: se sigue sin control
            error_log("ControlSolicitudModelo::Iniciar: " . $e->getMessage());
            self::$id = null;
        }
    }

    /* true si este envío es el primero con ese identificador */
    private static function Registrar(PDO $pdo, $id, $url)
    {
        try {
            $pdo->prepare("INSERT INTO solicitudprocesada (IdSolicitud, Url) VALUES (:id, :url)")
                ->execute([':id' => $id, ':url' => $url]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false; // ya existe
            }
            throw $e;
        }
    }

    /* Copia de lo que se envía al navegador (no cambia la salida) */
    public static function Capturar($buffer, $fase)
    {
        if (!($fase & PHP_OUTPUT_HANDLER_CLEAN)) {
            self::$respuesta .= $buffer;
        }
        return $buffer;
    }

    /* Al terminar el script: guarda la respuesta, o borra el registro si falló para permitir el reintento */
    public static function Terminar()
    {
        if (self::$id === null) {
            return;
        }
        $nombre = self::class . '::Capturar';
        $capturada = in_array($nombre, ob_list_handlers(), true);
        if ($capturada) {
            while (ob_get_level() > 0) {
                $handlers = ob_list_handlers();
                $ultimo = end($handlers);
                ob_end_flush();
                if ($ultimo === $nombre) {
                    break;
                }
            }
        }

        $error = error_get_last();
        $fatal = $error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
        $codigo = http_response_code() ?: 200;
        $tipo = null;
        foreach (headers_list() as $cabecera) {
            if (stripos($cabecera, 'Content-Type:') === 0) {
                $tipo = trim(substr($cabecera, 13));
            }
        }

        try {
            $pdo = Conexion::Conectar();
            if (!$capturada || $fatal || $codigo >= 500 || strlen(self::$respuesta) > self::RESPUESTA_MAXIMA) {
                $pdo->prepare("DELETE FROM solicitudprocesada WHERE IdSolicitud = :id")->execute([':id' => self::$id]);
                return;
            }
            $pdo->prepare("UPDATE solicitudprocesada
                           SET Estado = 'TERMINADA', CodigoHttp = :codigo, TipoContenido = :tipo, Respuesta = :respuesta
                           WHERE IdSolicitud = :id")
                ->execute([':codigo' => $codigo, ':tipo' => $tipo, ':respuesta' => self::$respuesta, ':id' => self::$id]);
        } catch (Throwable $e) {
            error_log("ControlSolicitudModelo::Terminar: " . $e->getMessage());
        }
    }
}
