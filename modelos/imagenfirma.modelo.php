<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

/**
 * Firma del coordinador y sello de Posgrado, en imagen (tabla `imagenfirma`, clave
 * FIRMA o SELLO). Se imprimen solo en las órdenes de pago de matrícula y cuota que
 * genera el propio estudiante desde el sistema; las del personal se firman a mano.
 * Se cambian desde Configuración cuando cambia el coordinador.
 * En la misma tabla van los logos de la institución (LOGO_*, ver InstitucionModelo).
 */
class ImagenFirmaModelo
{
    const CLAVES = [
        'FIRMA' => 'Firma del coordinador',
        'SELLO' => 'Sello de Posgrado',
        'LOGO_IZQUIERDO' => 'Logo izquierdo de los reportes',
        'LOGO_DERECHO' => 'Logo derecho de los reportes',
        'LOGO_SISTEMA' => 'Logo del sistema (pantallas)',
    ];

    /** Color con que se imprime la firma (azul de bolígrafo) */
    const AZUL_TINTA = [24, 46, 140];

    /** Lado mayor con que se guarda la imagen (de sobra para imprimirla a ~4 cm) */
    const LADO_MAXIMO = 900;

    /**
     * Imagen guardada (PNG) o null si no hay.
     * @return string|null
     */
    public static function Obtener($clave)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT Imagen FROM imagenfirma WHERE Clave = :clave");
            $stmt->execute([':clave' => $clave]);
            $imagen = $stmt->fetchColumn();
            return $imagen !== false ? $imagen : null;
        } catch (PDOException $e) {
            error_log("Error en ImagenFirmaModelo::Obtener: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Qué imágenes hay cargadas, con quién y cuándo las cambió.
     * @return array [CLAVE => ['FechaActualizacion', 'ResponsableActualizacion'] | null]
     */
    public static function Estado()
    {
        $estado = array_fill_keys(array_keys(self::CLAVES), null);
        try {
            $stmt = Conexion::Conectar()->query("SELECT Clave, FechaActualizacion, ResponsableActualizacion FROM imagenfirma");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $estado[$fila['Clave']] = $fila;
            }
        } catch (PDOException $e) {
            error_log("Error en ImagenFirmaModelo::Estado: " . $e->getMessage());
        }
        return $estado;
    }

    /**
     * Guarda la imagen subida como PNG. A la firma se le vuelve transparente el fondo
     * blanco del escaneo (así no tapa las líneas de la orden al imprimirla encima).
     * @param string $clave FIRMA, SELLO o LOGO_*
     * @param string $contenido archivo subido (PNG o JPG)
     * @return string|true mensaje de error o true
     */
    public static function Guardar($clave, $contenido, $responsable)
    {
        if (!isset(self::CLAVES[$clave])) return 'Imagen no válida';
        // Los logos no se recortan: el recorte sigue el trazo oscuro y cortaría sus partes claras
        $png = self::PrepararPng($contenido, $clave === 'FIRMA', in_array($clave, ['FIRMA', 'SELLO'], true));
        if ($png === null) return 'El archivo no es una imagen PNG o JPG válida';

        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO imagenfirma (Clave, Imagen, FechaActualizacion, ResponsableActualizacion)
                 VALUES (:clave, :imagen, NOW(), :responsable)
                 ON DUPLICATE KEY UPDATE Imagen = VALUES(Imagen), FechaActualizacion = NOW(),
                                         ResponsableActualizacion = VALUES(ResponsableActualizacion)"
            );
            $stmt->bindValue(':clave', $clave);
            $stmt->bindValue(':imagen', $png, PDO::PARAM_LOB);
            $stmt->bindValue(':responsable', $responsable);
            return $stmt->execute() ? true : 'No se pudo guardar la imagen';
        } catch (PDOException $e) {
            error_log("Error en ImagenFirmaModelo::Guardar: " . $e->getMessage());
            return 'No se pudo guardar la imagen';
        }
    }

    /** Quita la imagen: las órdenes del estudiante salen sin ella */
    public static function Quitar($clave)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("DELETE FROM imagenfirma WHERE Clave = :clave");
            return $stmt->execute([':clave' => $clave]);
        } catch (PDOException $e) {
            error_log("Error en ImagenFirmaModelo::Quitar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * PNG con transparencia, recortado al lado máximo; con $fondoTransparente los
     * píxeles casi blancos pasan a transparentes y con $recortar se quitan los bordes vacíos.
     * @return string|null
     */
    private static function PrepararPng($contenido, $fondoTransparente, $recortar = true)
    {
        $info = @getimagesizefromstring($contenido);
        if (!$info || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) return null;
        $origen = @imagecreatefromstring($contenido);
        if (!$origen) return null;

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $escala = min(1, self::LADO_MAXIMO / max($ancho, $alto));
        $nuevoAncho = max(1, (int)round($ancho * $escala));
        $nuevoAlto = max(1, (int)round($alto * $escala));

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagefill($destino, 0, 0, imagecolorallocatealpha($destino, 255, 255, 255, 127));
        imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        // Firma: fondo transparente y trazo en azul de bolígrafo. Cada punto queda azul y
        // tan opaco como oscuro era (así se conservan los bordes suaves del trazo).
        if ($fondoTransparente) {
            for ($x = 0; $x < $nuevoAncho; $x++) {
                for ($y = 0; $y < $nuevoAlto; $y++) {
                    $c = imagecolorsforindex($destino, imagecolorat($destino, $x, $y));
                    $luminancia = ($c['red'] * 299 + $c['green'] * 587 + $c['blue'] * 114) / 1000;
                    $opacidad = (1 - $c['alpha'] / 127) * max(0, min(1, (225 - $luminancia) / 150));
                    imagesetpixel($destino, $x, $y, imagecolorallocatealpha(
                        $destino, self::AZUL_TINTA[0], self::AZUL_TINTA[1], self::AZUL_TINTA[2], (int)round(127 * (1 - $opacidad))
                    ));
                }
            }
        }

        // Se recortan los bordes vacíos: así el tamaño impreso es el de la firma o el sello
        if ($recortar) {
            $destino = self::RecortarBordes($destino);
        }

        ob_start();
        imagepng($destino);
        $png = ob_get_clean();
        imagedestroy($origen);
        imagedestroy($destino);
        return $png;
    }

    /**
     * Quita los márgenes alrededor del trazo. Cuenta como trazo lo visible (alpha 0 =
     * opaco, 127 = transparente) y oscuro, y solo las columnas y filas con al menos 3
     * de esos puntos: las motas sueltas del escaneo no agrandan el recorte.
     */
    private static function RecortarBordes($imagen)
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $columnas = array_fill(0, $ancho, 0);
        $filas = array_fill(0, $alto, 0);
        for ($y = 0; $y < $alto; $y++) {
            for ($x = 0; $x < $ancho; $x++) {
                $c = imagecolorat($imagen, $x, $y);
                $luminancia = (($c >> 16 & 0xFF) * 299 + ($c >> 8 & 0xFF) * 587 + ($c & 0xFF) * 114) / 1000;
                if (($c >> 24 & 0x7F) < 110 && $luminancia < 170) {
                    $columnas[$x]++;
                    $filas[$y]++;
                }
            }
        }
        $conTrazo = function ($conteos) {
            return array_keys(array_filter($conteos, function ($n) { return $n >= 3; }));
        };
        $xs = $conTrazo($columnas);
        $ys = $conTrazo($filas);
        if (!$xs || !$ys) return $imagen; // sin trazo: se deja igual
        $minX = min($xs); $maxX = max($xs); $minY = min($ys); $maxY = max($ys);
        $recorte = imagecrop($imagen, ['x' => $minX, 'y' => $minY, 'width' => $maxX - $minX + 1, 'height' => $maxY - $minY + 1]);
        if (!$recorte) return $imagen;
        imagealphablending($recorte, false);
        imagesavealpha($recorte, true);
        imagedestroy($imagen);
        return $recorte;
    }
}
