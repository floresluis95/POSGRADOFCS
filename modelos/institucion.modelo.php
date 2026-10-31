<?php
require_once __DIR__ . '/configuracion.modelo.php';
require_once __DIR__ . '/imagenfirma.modelo.php';

/**
 * Datos de la institución (área de posgrado) que salen en los encabezados de los
 * reportes PDF, en las firmas y en las pantallas: universidad, facultad, área,
 * dirección, teléfonos y logos. Cada área (Odontología, Medicina, ...) tiene su
 * propia instalación del sistema y el ADM los cambia desde Configuración.
 *
 * Textos: tabla `configuracion` (claves INST_*). Logos: tabla `imagenfirma`
 * (claves LOGO_*). Mientras no se cambien, valen los de Posgrado Odontología.
 */
class InstitucionModelo
{
    /** Textos: clave => [etiqueta en Configuración, valor por defecto, ayuda] */
    const TEXTOS = [
        'INST_UNIVERSIDAD' => ['Universidad', 'UNIVERSIDAD TÉCNICA DE ORURO', 'Primera línea del encabezado'],
        'INST_FACULTAD' => ['Facultad', 'FACULTAD DE CIENCIAS DE LA SALUD', 'Segunda línea del encabezado'],
        'INST_AREA' => ['Área de posgrado', 'Odontología', 'Nombre corto del área: "Posgrado Odontología" en las pantallas y el correo, "Área de Odontología" en el compromiso'],
        'INST_UNIDAD' => ['Coordinación (encabezado)', 'COORDINACIÓN DE POSGRADO - ODONTOLOGÍA', 'Tercera línea del encabezado de los reportes'],
        'INST_CARGO_FIRMA' => ['Cargo bajo la firma del coordinador', 'COORDINADOR DE POSGRADO ODONTOLOGÍA', 'Se imprime bajo la línea de firma de las planillas y la situación académica'],
        'INST_DIRECCION' => ['Dirección', 'Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso', ''],
        'INST_TELEFONOS' => ['Teléfonos', 'Telf: 5237317 - Fax: 5247110', ''],
        'INST_CIUDAD' => ['Ciudad y país', 'Oruro - Bolivia', ''],
    ];

    /** Logos: clave => [nombre, archivo por defecto (desde la raíz del proyecto)] */
    const LOGOS = [
        'LOGO_IZQUIERDO' => ['Logo izquierdo de los reportes', 'extensiones/imagenespdf/logouto.png'],
        'LOGO_DERECHO' => ['Logo derecho de los reportes', 'extensiones/imagenespdf/logofcs.png'],
        'LOGO_SISTEMA' => ['Logo del sistema (pantallas)', 'vistas/recursos/assets/media/logos/logo.png'],
    ];

    private static $logos = [];
    private static $estadoLogos = null;

    public static function Texto($clave)
    {
        if (!isset(self::TEXTOS[$clave])) return '';
        $valor = trim((string)ConfiguracionModelo::Obtener($clave, ''));
        return $valor !== '' ? $valor : self::TEXTOS[$clave][1];
    }

    public static function Universidad() { return self::Texto('INST_UNIVERSIDAD'); }
    public static function Facultad() { return self::Texto('INST_FACULTAD'); }
    public static function Area() { return self::Texto('INST_AREA'); }
    public static function Unidad() { return self::Texto('INST_UNIDAD'); }
    public static function CargoFirma() { return self::Texto('INST_CARGO_FIRMA'); }
    public static function Direccion() { return self::Texto('INST_DIRECCION'); }
    public static function Telefonos() { return self::Texto('INST_TELEFONOS'); }
    public static function Ciudad() { return self::Texto('INST_CIUDAD'); }

    /** "Posgrado Odontología": nombre del sistema en pantallas, títulos y correo */
    public static function NombreSistema()
    {
        return 'Posgrado ' . self::Area();
    }

    /**
     * "UNIVERSIDAD TÉCNICA DE ORURO" -> "Universidad Técnica de Oruro", para las
     * pantallas (los encabezados de los reportes van en MAYÚSCULAS).
     */
    public static function Formal($texto)
    {
        $palabras = explode(' ', mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8'));
        foreach ($palabras as $i => $palabra) {
            if ($i > 0 && in_array(mb_strtolower($palabra, 'UTF-8'), ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'e', 'en'], true)) {
                $palabras[$i] = mb_strtolower($palabra, 'UTF-8');
            }
        }
        return implode(' ', $palabras);
    }

    /**
     * Dirección, teléfonos y ciudad en una sola línea (las partes vacías se omiten).
     * Sin $conCiudad, para los encabezados que ponen la ciudad en otra línea.
     */
    public static function DireccionCompleta($separador = ' - ', $conCiudad = true)
    {
        $partes = [self::Direccion(), self::Telefonos()];
        if ($conCiudad) $partes[] = self::Ciudad();
        return implode($separador, array_filter($partes, 'strlen'));
    }

    /** Todos los textos (para Configuración y para window.INSTITUCION) */
    public static function Textos()
    {
        $textos = [];
        foreach (array_keys(self::TEXTOS) as $clave) {
            $textos[$clave] = self::Texto($clave);
        }
        return $textos;
    }

    /**
     * Guarda los textos. Vacío = vuelve al valor por defecto.
     * @return bool
     */
    public static function GuardarTextos(array $valores, $responsable)
    {
        $ok = true;
        foreach (array_keys(self::TEXTOS) as $clave) {
            if (!array_key_exists($clave, $valores)) continue;
            $valor = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)$valores[$clave])), 0, 255, 'UTF-8');
            $ok = ConfiguracionModelo::Guardar($clave, $valor, $responsable) && $ok;
        }
        return $ok;
    }

    /**
     * Logo subido por el ADM (PNG) o null si se usa el de por defecto.
     */
    public static function LogoSubido($clave)
    {
        if (!isset(self::LOGOS[$clave])) return null;
        if (!array_key_exists($clave, self::$logos)) {
            self::$logos[$clave] = self::EstadoLogos()[$clave] ? ImagenFirmaModelo::Obtener($clave) : null;
        }
        return self::$logos[$clave];
    }

    /** Quién y cuándo subió cada logo (null = sin subir: se usa el de por defecto) */
    public static function EstadoLogos()
    {
        if (self::$estadoLogos === null) {
            $estado = ImagenFirmaModelo::Estado();
            self::$estadoLogos = [];
            foreach (array_keys(self::LOGOS) as $clave) {
                self::$estadoLogos[$clave] = $estado[$clave] ?? null;
            }
        }
        return self::$estadoLogos;
    }

    /** Ruta absoluta del logo por defecto */
    public static function LogoPorDefecto($clave)
    {
        return __DIR__ . '/../' . self::LOGOS[$clave][1];
    }

    /**
     * Logo para $pdf->Image() de TCPDF: '@' + PNG subido, o la ruta del archivo por
     * defecto. null si no hay ninguno.
     */
    public static function LogoPdf($clave)
    {
        $png = self::LogoSubido($clave);
        if ($png !== null) return '@' . $png;
        $ruta = self::LogoPorDefecto($clave);
        return file_exists($ruta) ? $ruta : null;
    }

    /**
     * Logo para <img src> en los reportes HTML que se imprimen desde vistas/componentes
     * (data URI si se subió; si no, la ruta relativa del archivo por defecto).
     * @param string $prefijo ruta desde la página hasta la raíz del proyecto (ej. '../../')
     */
    public static function LogoSrc($clave, $prefijo = '')
    {
        $png = self::LogoSubido($clave);
        if ($png !== null) return 'data:image/png;base64,' . base64_encode($png);
        return $prefijo . self::LOGOS[$clave][1];
    }

    /**
     * URL del logo para las pantallas (relativa a la raíz del sitio). El logo subido
     * se sirve desde ajax/imagen-institucion.php; la versión evita que el navegador
     * siga mostrando el anterior.
     */
    public static function LogoUrl($clave)
    {
        $estado = self::EstadoLogos()[$clave] ?? null;
        if ($estado) {
            return 'ajax/imagen-institucion.php?clave=' . $clave . '&v=' . strtotime($estado['FechaActualizacion']);
        }
        return self::LOGOS[$clave][1];
    }

    /** Datos que usa el JavaScript (window.INSTITUCION): impresión de tablas y exportar a PDF */
    public static function DatosJs()
    {
        return [
            'universidad' => self::Universidad(),
            'facultad' => self::Facultad(),
            'area' => self::Area(),
            'unidad' => self::Unidad(),
            'nombreSistema' => self::NombreSistema(),
            'direccionCompleta' => self::DireccionCompleta(),
            'logoIzquierdo' => self::LogoUrl('LOGO_IZQUIERDO'),
            'logoDerecho' => self::LogoUrl('LOGO_DERECHO'),
        ];
    }
}
