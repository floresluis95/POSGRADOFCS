<?php
/**
 * Generar Orden de Pago para Imprimir
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    die('Acceso denegado');
}

// Verificar que se recibieron los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido');
}

// Recibir datos del POST
$apaterno = $_POST['apaterno'] ?? '';
$amaterno = $_POST['amaterno'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$correo = $_POST['correo'] ?? '';
$ci = $_POST['ci'] ?? '';
$celular = $_POST['celular'] ?? '';
$programa = $_POST['programa'] ?? '';
$modulo = $_POST['modulo'] ?? '';
$montoNumeral = $_POST['montoNumeral'] ?? '';
$montoLiteral = $_POST['montoLiteral'] ?? '';
$version = $_POST['version'] ?? '';
$cuentaAuxiliar = $_POST['cuentaAuxiliar'] ?? '';
$nombreFactura = $_POST['nombreFactura'] ?? '';
$nitCiFactura = $_POST['nitCiFactura'] ?? '';
$responsable = $_POST['responsable'] ?? '';
$firma = $_POST['firma'] ?? '';

// Nombre completo del estudiante
$nombreCompleto = trim("$apaterno $amaterno $nombres");

// Fecha actual
$fechaActual = date('d/m/Y');
$horaActual = date('H:i:s');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Orden de Pago - <?php echo htmlspecialchars($nombreCompleto); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 15px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            color: #667eea;
            font-size: 22px;
            font-weight: bold;
        }

        .header h2 {
            margin: 3px 0 0 0;
            color: #464E5F;
            font-size: 14px;
        }

        .section {
            margin-bottom: 10px;
            border: 1px solid #e2e5ec;
            border-radius: 5px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: #667eea;
            color: white;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 11px;
        }

        .section-header-green {
            background: #11998e;
        }

        .section-header-red {
            background: #fd397a;
        }

        .section-content {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .label {
            color: #999;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .value {
            color: #333;
            font-weight: bold;
            font-size: 10px;
            display: block;
        }

        .monto-numeral {
            color: #11998e;
            font-weight: bold;
            font-size: 16px;
        }

        .monto-literal {
            color: #464E5F;
            font-style: italic;
            font-size: 9px;
        }

        .cuenta-box {
            background: #f8f9fa;
            border: 1px solid #667eea;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }

        .cuenta-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
        }

        .cuenta-box p {
            margin: 4px 0;
            font-size: 10px;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e2e5ec;
        }

        .firma-box {
            text-align: center;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .firma-linea {
            border-top: 1px solid #333;
            width: 250px;
            margin: 0 auto 6px auto;
        }

        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        .btn-imprimir {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
        }

        .btn-imprimir:hover {
            background: #5568d3;
        }

        .btn-cerrar {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
        }

        .btn-cerrar:hover {
            background: #5a6268;
        }

        /* Estilos para impresión */
        @media print {
            body {
                padding: 10px;
            }

            .no-print {
                display: none !important;
            }

            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Botones de acción (solo en pantalla) -->
    <div class="no-print">
        <button class="btn-imprimir" onclick="window.print()">
            Imprimir / Guardar como PDF
        </button>
        <button class="btn-cerrar" onclick="window.close()">
            Cerrar
        </button>
    </div>

    <!-- Contenido del documento -->
    <div class="header">
        <h1>ORDEN DE PAGO</h1>
        <h2>Universidad Técnica de Oruro</h2>
        <p style="margin: 10px 0; font-size: 13px;">
            <strong>Fecha:</strong> <?php echo $fechaActual; ?> -
            <strong>Hora:</strong> <?php echo $horaActual; ?>
        </p>
    </div>

    <!-- Datos del Estudiante -->
    <div class="section">
        <div class="section-header section-header-green">
            DATOS DEL ESTUDIANTE
        </div>
        <div class="section-content">
            <table>
                <tr>
                    <td style="width: 33%;">
                        <span class="label">Apellido Paterno</span>
                        <span class="value"><?php echo htmlspecialchars($apaterno); ?></span>
                    </td>
                    <td style="width: 33%;">
                        <span class="label">Apellido Materno</span>
                        <span class="value"><?php echo htmlspecialchars($amaterno); ?></span>
                    </td>
                    <td style="width: 33%;">
                        <span class="label">Nombres</span>
                        <span class="value"><?php echo htmlspecialchars($nombres); ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="label">Correo Electrónico</span>
                        <span class="value"><?php echo htmlspecialchars($correo); ?></span>
                    </td>
                    <td>
                        <span class="label">C.I.</span>
                        <span class="value"><?php echo htmlspecialchars($ci); ?></span>
                    </td>
                    <td>
                        <span class="label">N° Celular</span>
                        <span class="value"><?php echo htmlspecialchars($celular); ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Datos para la Emisión de Comprobante de Pago -->
    <div class="section">
        <div class="section-header section-header-red">
            DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO
        </div>
        <div class="section-content">
            <table>
                <tr>
                    <td colspan="2">
                        <span class="label">Programa</span>
                        <span class="value"><?php echo htmlspecialchars($programa); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="label">Módulo</span>
                        <span class="value"><?php echo htmlspecialchars($modulo); ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">
                        <span class="label">Monto (Numeral)</span>
                        <span class="monto-numeral"><?php echo htmlspecialchars($montoNumeral); ?></span>
                    </td>
                    <td style="width: 50%;">
                        <span class="label">Monto (Literal)</span>
                        <span class="monto-literal"><?php echo htmlspecialchars($montoLiteral); ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Datos del Formulario -->
    <div class="section">
        <div class="section-header">
            FORMULARIO DE REGISTRO
        </div>
        <div class="section-content">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <span class="label">Versión</span>
                        <span class="value"><?php echo htmlspecialchars($version); ?></span>
                    </td>
                    <td style="width: 50%;">
                        <span class="label">Cuenta Auxiliar</span>
                        <span class="value"><?php echo htmlspecialchars($cuentaAuxiliar); ?></span>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px;">
                <strong style="color: #667eea; font-size: 13px; display: block; margin-bottom: 10px;">
                    DATOS PARA LA EMISIÓN DE FACTURA
                </strong>
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <span class="label">Nombre de la Factura</span>
                            <span class="value"><?php echo htmlspecialchars($nombreFactura); ?></span>
                        </td>
                        <td style="width: 50%;">
                            <span class="label">NIT o CI</span>
                            <span class="value"><?php echo htmlspecialchars($nitCiFactura); ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Denominación de la Cuenta -->
    <div class="cuenta-box">
        <strong>DENOMINACIÓN DE LA CUENTA</strong>
        <p style="font-weight: bold; color: #464E5F;">
            UTO - APORTES EXTRAORDINARIOS - NÚMERO DE CUENTA 10000006050938
        </p>
        <p style="font-weight: bold; color: #11998e;">
            NIT: 120129022
        </p>
    </div>

    <!-- Responsable -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 50%;">
                    <span class="label">Responsable</span>
                    <span class="value"><?php echo htmlspecialchars($responsable); ?></span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Firma</span>
                    <span class="value"><?php echo htmlspecialchars($firma ?: '____________________'); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="firma-box">
        <div class="firma-linea"></div>
        <strong style="font-size: 14px;">Firma del Responsable</strong>
    </div>

    <p style="text-align: center; color: #999; font-size: 10px; margin-top: 30px;">
        Este documento fue generado electrónicamente el <?php echo $fechaActual; ?> a las <?php echo $horaActual; ?>
    </p>

    <div class="no-print" style="margin-top: 30px;">
        <p style="text-align: center; color: #666; font-size: 12px;">
            <strong>Instrucciones:</strong> Presione el botón "Imprimir" y seleccione "Guardar como PDF" como destino para generar el archivo PDF.
        </p>
    </div>
</body>
</html>
