<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Obtener ID de la orden desde GET
$idOrdenPago = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idOrdenPago == 0) {
    echo '<script>window.location.href = "ordenpago";</script>';
    exit;
}

// Obtener datos de la orden
require_once 'modelos/conexion.modelo.php';

$pdo = Conexion::Conectar();
$stmt = $pdo->prepare("
    SELECT
        op.*,
        e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
        p.NombrePrograma, p.Codigo, p.Version, p.NumeroTramite, p.GradoAcademico
    FROM ordenpago op
    INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
    INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
    WHERE op.IdOrdenPago = :id
");
$stmt->execute([':id' => $idOrdenPago]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    echo '<script>window.location.href = "ordenpago";</script>';
    exit;
}

// Preparar CI completo
$ciCompleto = $orden['Ci'];
if (!empty($orden['Complemento'])) $ciCompleto .= '-' . $orden['Complemento'];
$ciCompleto .= ' ' . $orden['Exp'];

// Función para convertir número a letras
function numeroALetras($numero) {
    $entero = floor($numero);
    $decimales = round(($numero - $entero) * 100);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    function convertirEntero($num, $unidades, $decenas, $especiales, $centenas) {
        if ($num == 0) return 'CERO';
        if ($num == 100) return 'CIEN';

        $resultado = '';

        if ($num >= 1000000) {
            $millones = floor($num / 1000000);
            $resultado .= ($millones == 1) ? 'UN MILLON ' : convertirEntero($millones, $unidades, $decenas, $especiales, $centenas) . ' MILLONES ';
            $num %= 1000000;
        }

        if ($num >= 1000) {
            $miles = floor($num / 1000);
            $resultado .= ($miles == 1) ? 'MIL ' : convertirEntero($miles, $unidades, $decenas, $especiales, $centenas) . ' MIL ';
            $num %= 1000;
        }

        if ($num >= 100) {
            $resultado .= $centenas[floor($num / 100)] . ' ';
            $num %= 100;
        }

        if ($num >= 10 && $num < 20) {
            $resultado .= $especiales[$num - 10] . ' ';
        } elseif ($num >= 20) {
            $resultado .= $decenas[floor($num / 10)];
            if ($num % 10 > 0) $resultado .= ' Y ' . $unidades[$num % 10];
            $resultado .= ' ';
        } elseif ($num > 0) {
            $resultado .= $unidades[$num] . ' ';
        }

        return trim($resultado);
    }

    $letras = convertirEntero($entero, $unidades, $decenas, $especiales, $centenas);

    if ($decimales > 0) {
        return strtoupper($letras . ' CON ' . $decimales . '/100 BOLIVIANOS');
    } else {
        return strtoupper($letras . ' 00/100 BOLIVIANOS');
    }
}

$montoNumeral = number_format($orden['MontoFinal'], 2);
$montoLiteral = numeroALetras($orden['MontoFinal']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Pago Generada - <?php echo $orden['NumeroOrden']; ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
    <link href="vistas/recursos/assets/vendors/general/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" type="text/css" />
    <link href="vistas/recursos/assets/css/demo9/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="vistas/recursos/assets/vendors/custom/vendors/flaticon/flaticon.css" rel="stylesheet" type="text/css" />
    <link href="vistas/recursos/assets/vendors/custom/vendors/flaticon2/flaticon.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .success-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .success-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-title {
            font-size: 32px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }

        .success-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        .order-details {
            padding: 40px;
        }

        .order-number {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .order-number-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .order-number-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .amount-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }

        .amount-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .amount-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .amount-literal {
            font-size: 14px;
            opacity: 0.9;
            font-style: italic;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 30px;
        }

        .btn-download {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            box-shadow: 0 5px 20px rgba(108, 117, 125, 0.4);
        }

        @media (max-width: 768px) {
            .info-grid, .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">✓</div>
        <h1 class="success-title">¡Orden de Pago Generada Exitosamente!</h1>
        <p class="success-subtitle">Tu orden de pago ha sido registrada correctamente</p>
    </div>

    <div class="order-details">
        <!-- Número de Orden -->
        <div class="order-number">
            <div class="order-number-label">NÚMERO DE ORDEN</div>
            <div class="order-number-value"><?php echo $orden['NumeroOrden']; ?></div>
        </div>

        <!-- Información del Estudiante -->
        <h3 style="margin-bottom: 20px; color: #667eea;">
            <i class="flaticon2-user"></i> Información del Estudiante
        </h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre Completo</div>
                <div class="info-value"><?php echo $orden['Nombre'] . ' ' . $orden['Apaterno'] . ' ' . $orden['Amaterno']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Cédula de Identidad</div>
                <div class="info-value"><?php echo $ciCompleto; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Correo Electrónico</div>
                <div class="info-value"><?php echo $orden['Correo']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Celular</div>
                <div class="info-value"><?php echo $orden['Celular']; ?></div>
            </div>
        </div>

        <!-- Información del Programa -->
        <h3 style="margin-bottom: 20px; margin-top: 30px; color: #667eea;">
            <i class="flaticon2-layers-1"></i> Información del Programa
        </h3>
        <div class="info-grid">
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Programa</div>
                <div class="info-value"><?php echo $orden['GradoAcademico'] . ' EN ' . strtoupper($orden['NombrePrograma']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Código</div>
                <div class="info-value"><?php echo $orden['Codigo']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Versión</div>
                <div class="info-value"><?php echo $orden['Version']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Tipo de Pago</div>
                <div class="info-value"><?php echo $orden['PagoCompleto'] == 1 ? 'PAGO COMPLETO' : 'SOLO MATRÍCULA'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de Generación</div>
                <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($orden['FechaGeneracion'])); ?></div>
            </div>
        </div>

        <!-- Información de Facturación -->
        <h3 style="margin-bottom: 20px; margin-top: 30px; color: #667eea;">
            <i class="flaticon2-document"></i> Información de Facturación
        </h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre para Factura</div>
                <div class="info-value"><?php echo $orden['NombreFactura']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">NIT/CI</div>
                <div class="info-value"><?php echo $orden['NitCiFactura']; ?></div>
            </div>
        </div>

        <!-- Monto a Pagar -->
        <div class="amount-box">
            <div class="amount-label">MONTO A PAGAR</div>
            <div class="amount-value">Bs. <?php echo $montoNumeral; ?></div>
            <div class="amount-literal"><?php echo $montoLiteral; ?></div>

            <?php if ($orden['MontoDescuento'] > 0): ?>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3);">
                <small>
                    Descuento aplicado: <?php echo $orden['PorcentajeDescuento']; ?>%
                    (Bs. <?php echo number_format($orden['MontoDescuento'], 2); ?>)
                </small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Botones de Acción -->
        <div class="action-buttons">
            <form method="POST" action="vistas/componentes/generar-orden-pago-pdf.php" target="_blank" style="margin: 0;">
                <input type="hidden" name="apaterno" value="<?php echo htmlspecialchars($orden['Apaterno']); ?>">
                <input type="hidden" name="amaterno" value="<?php echo htmlspecialchars($orden['Amaterno']); ?>">
                <input type="hidden" name="nombres" value="<?php echo htmlspecialchars($orden['Nombre']); ?>">
                <input type="hidden" name="correo" value="<?php echo htmlspecialchars($orden['Correo']); ?>">
                <input type="hidden" name="ci" value="<?php echo htmlspecialchars($ciCompleto); ?>">
                <input type="hidden" name="celular" value="<?php echo htmlspecialchars($orden['Celular']); ?>">
                <input type="hidden" name="programa" value="<?php echo htmlspecialchars($orden['NombrePrograma']); ?>">
                <input type="hidden" name="modulo" value="">
                <input type="hidden" name="montoNumeral" value="Bs. <?php echo $montoNumeral; ?>">
                <input type="hidden" name="montoLiteral" value="<?php echo htmlspecialchars($montoLiteral); ?>">
                <input type="hidden" name="version" value="<?php echo htmlspecialchars($orden['Version']); ?>">
                <input type="hidden" name="numeroTramite" value="<?php echo htmlspecialchars($orden['NumeroTramite']); ?>">
                <input type="hidden" name="cuentaAuxiliar" value="">
                <input type="hidden" name="nombreFactura" value="<?php echo htmlspecialchars($orden['NombreFactura']); ?>">
                <input type="hidden" name="nitCiFactura" value="<?php echo htmlspecialchars($orden['NitCiFactura']); ?>">
                <input type="hidden" name="responsable" value="<?php echo htmlspecialchars($orden['ResponsableGeneracion']); ?>">
                <input type="hidden" name="firma" value="<?php echo htmlspecialchars($orden['Firma']); ?>">
                <input type="hidden" name="numeroOrden" value="<?php echo htmlspecialchars($orden['NumeroOrden']); ?>">

                <button type="submit" class="btn-download">
                    <i class="flaticon2-download"></i>
                    Descargar Orden de Pago
                </button>
            </form>

            <a href="ordenpago" class="btn-download btn-secondary">
                <i class="flaticon2-back"></i>
                Generar Nueva Orden
            </a>
        </div>

        <!-- Instrucciones -->
        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
            <h4 style="margin: 0 0 10px 0; color: #856404;">
                <i class="flaticon2-information"></i> Instrucciones de Pago
            </h4>
            <ol style="margin: 0; padding-left: 20px; color: #856404;">
                <li>Descarga tu orden de pago haciendo clic en el botón de arriba</li>
                <li>Realiza el pago en el Banco Unión a nombre de la UTO</li>
                <li>Presenta el voucher de pago en la oficina de caja</li>
                <li>Guarda este número de orden: <strong><?php echo $orden['NumeroOrden']; ?></strong></li>
            </ol>
        </div>
    </div>
</div>

</body>
</html>
