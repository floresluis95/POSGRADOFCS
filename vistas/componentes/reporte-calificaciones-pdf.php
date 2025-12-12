<?php
/**
 * REPORTE DE CALIFICACIONES EN PDF
 * Este archivo genera un reporte imprimible de las calificaciones de un módulo
 * Puede imprimirse directamente desde el navegador (Ctrl+P)
 */

// Validar sesión

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso denegado');
}

// Obtener parámetros
$moduloID = isset($_GET['moduloID']) ? intval($_GET['moduloID']) : 0;
$programaID = isset($_GET['programaID']) ? intval($_GET['programaID']) : 0;
$moduloNombre = isset($_GET['moduloNombre']) ? $_GET['moduloNombre'] : '';
$moduloCodigo = isset($_GET['moduloCodigo']) ? $_GET['moduloCodigo'] : '';
$programaNombre = isset($_GET['programaNombre']) ? $_GET['programaNombre'] : '';
$gradoAcademico = isset($_GET['gradoAcademico']) ? $_GET['gradoAcademico'] : '';
$docenteNombre = isset($_GET['docenteNombre']) ? $_GET['docenteNombre'] : '';

// Incluir modelo
require_once '../../modelos/conexion.modelo.php';
require_once '../../modelos/calificacion.modelo.php';

// Obtener estudiantes y calificaciones
$estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

// Fecha actual
date_default_timezone_set("America/La_Paz");
$fechaImpresion = date('d/m/Y H:i:s');

/**
 * Convierte un número a su representación literal en español
 * @param float $numero - Número a convertir (puede tener decimales)
 * @return string - Representación en letras
 */
function numeroALetras($numero) {
    $entero = intval($numero); // Convertir a entero (sin decimales)

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    if ($entero == 0) {
        $literal = 'CERO';
    } elseif ($entero == 100) {
        $literal = 'CIEN';
    } elseif ($entero > 100) {
        $literal = 'CIENTO ';
        $resto = $entero - 100;

        if ($resto >= 10 && $resto < 20) {
            $literal .= $especiales[$resto - 10];
        } elseif ($resto >= 20 && $resto < 30) {
            $literal .= $veintenas[$resto - 20];
        } elseif ($resto >= 30) {
            $dec = floor($resto / 10);
            $uni = $resto % 10;
            $literal .= $decenas[$dec];
            if ($uni > 0) {
                $literal .= ' Y ' . $unidades[$uni];
            }
        } else {
            $literal .= $unidades[$resto];
        }
    } elseif ($entero >= 10 && $entero < 20) {
        $literal = $especiales[$entero - 10];
    } elseif ($entero >= 20 && $entero < 30) {
        $literal = $veintenas[$entero - 20];
    } else {
        $dec = floor($entero / 10);
        $uni = $entero % 10;
        $literal = $decenas[$dec];
        if ($uni > 0) {
            if ($dec > 0) {
                $literal .= ' Y ';
            }
            $literal .= $unidades[$uni];
        }
    }

    return trim($literal);
}
?>
<!DOCTYPE html>
<html lang="es">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Calificaciones - <?php echo htmlspecialchars($moduloCodigo); ?></title>
    <style>
        /* Contenedor principal */
.header {
    text-align: center; /* Centra el texto del encabezado */
    overflow: hidden; /* Importante para contener los elementos flotantes */
    padding: 10px 0; /* Agrega un poco de espacio arriba y abajo */
}

/* Estilo para las imágenes de logo */
.header img {
    width: 80px; /* Ajusta este valor al tamaño deseado de tu logo */
    height: auto;
    margin-top: -10px; /* Ajusta la posición vertical si es necesario */
}

/* Posiciona la imagen izquierda */
.logo-izquierda {
    float: left;
    margin-left: 20px; /* Espacio desde el borde izquierdo */
}

/* Posiciona la imagen derecha */
.logo-derecha {
    float: right;
    margin-right: 20px; /* Espacio desde el borde derecho */
}

/* Estilos para el texto, si quieres asegurarte que no se superponga */
.header h1, .header h2, .header p {
    margin-left: 120px; /* Reserva espacio para el logo izquierdo */
    margin-right: 120px; /* Reserva espacio para el logo derecho */
}
        /* ====================================
           ESTILOS GENERALES
           ==================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        /* ====================================
           ENCABEZADO DEL REPORTE
           ==================================== */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #667eea;
            font-size: 20pt;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            color: #764ba2;
            font-size: 14pt;
            margin-bottom: 3px;
        }

        .header p {
            color: #666;
            font-size: 10pt;
            margin: 2px 0;
        }

        /* ====================================
           INFORMACIÓN DEL MÓDULO
           ==================================== */
        .info-modulo {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info-modulo table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-modulo td {
            padding: 5px 10px;
            font-size: 10pt;
        }

        .info-modulo td:first-child {
            font-weight: bold;
            width: 150px;
            color: #667eea;
        }

        /* ====================================
           TABLA DE CALIFICACIONES
           ==================================== */
        .tabla-calificaciones {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tabla-calificaciones thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .tabla-calificaciones thead th {
            padding: 12px 8px;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            border: 1px solid #5867dd;
        }

        .tabla-calificaciones tbody td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        .tabla-calificaciones tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .tabla-calificaciones tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Columnas específicas */
        .col-numero { width: 5%; text-align: center; }
        .col-ci { width: 12%; text-align: center; }
        .col-estudiante { width: 40%; }
        .col-nota { width: 12%; text-align: center; font-weight: bold; font-size: 11pt; }
        .col-estado { width: 15%; text-align: center; }
        .col-fecha { width: 16%; text-align: center; font-size: 9pt; }

        /* Estados de calificación */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-aprobado {
            background-color: #1dc9b7;
            color: white;
        }

        .badge-reprobado {
            background-color: #fd397a;
            color: white;
        }

        .badge-pendiente {
            background-color: #6c757d;
            color: white;
        }

        /* ====================================
           ESTADÍSTICAS
           ==================================== */
        .estadisticas {
            display: table;
            width: 100%;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .estadistica-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-left: 3px solid #667eea;
            margin: 0 5px;
        }

        .estadistica-item h3 {
            font-size: 24pt;
            color: #667eea;
            margin-bottom: 5px;
        }

        .estadistica-item p {
            font-size: 10pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ====================================
           PIE DE PÁGINA
           ==================================== */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            font-size: 9pt;
            color: #666;
        }

        .footer .firmas {
            margin-top: 60px;
            display: table;
            width: 100%;
        }

        .footer .firma {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .footer .firma .linea {
            border-top: 2px solid #333;
            width: 60%;
            margin: 0 auto 10px auto;
        }

        .footer .firma p {
            font-weight: bold;
            color: #333;
        }

        /* ====================================
           ESTILOS PARA IMPRESIÓN
           ==================================== */
        @media print {
            body {
                padding: 0;
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .tabla-calificaciones {
                box-shadow: none;
            }

            @page {
                size: letter;
                margin: 1.5cm;
            }

            /* Evitar saltos de página en filas */
            .tabla-calificaciones tbody tr {
                page-break-inside: avoid;
            }
        }

        /* ====================================
           BOTÓN DE IMPRESIÓN
           ==================================== */
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 12pt;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-imprimir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-imprimir:active {
            transform: translateY(0);
        }

.col-nombre {
    width: 20%; /* Para nombres más largos */
}
.col-sigla {
    width: 15%; /* Sigla es corto */
}
.col-modulo {
    width: 15%; /* Módulo es largo */
}
.col-num {
    width: 15%; /* Número */
}
.col-literal {
    width: 60%; /* Literal */
}

/* Opcional: Para asegurar que el ancho se aplica correctamente */
table {
    width: 100%;
    table-layout: fixed; /* Ayuda a que los anchos definidos se respeten */
}
th {
    /* Opcional: para que el texto largo no fuerce el ancho */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
    </style>

</head>
<body>

    <!-- BOTÓN DE IMPRESIÓN (no se imprime) -->
    <button onclick="window.print()" class="btn-imprimir no-print">
        🖨️ Imprimir / Guardar PDF
    </button>

   <div class="header">
    <img src="../../extensiones/imagenespdf/logouto.png"  class="logo-izquierda">
    
    <img src="../../extensiones/imagenespdf/logofcs.png"  class="logo-derecha">

    <h1>UNIVERSIDAD TECNICA DE ORURO</h1>
    <h2>FACULTAD DE CIENCIAS DE LA SALUD</h2>
    <h2>COORDINACION POSGRADO AREA - ODONTOLOGIA</h2>
    <p>AV. Del Moreno Edificio San Agustin II (Ex Almacenes COMIBOL) Telefonos: 52 - 37317 - Fax: 52 - 47110 </p>
    <p>Oruro - Bolivia</p>
    <p style="font-size: 9pt; margin-top: 5px;">Generado el: <?php echo $fechaImpresion; ?></p>
</div>
        
    <!-- INFORMACIÓN DEL MÓDULO -->
    <div class="info-modulo">
      <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
    <tr> 
        <td style="border: 1px solid black; padding: 8px;">PROGRAMA:</td>
        <td style="border: 1px solid black; padding: 8px;"><?php echo htmlspecialchars($programaNombre); ?></td> 
    </tr>
    <tr>
        <td style="border: 1px solid black; padding: 8px;">MODULO:</td>
        <td style="border: 1px solid black; padding: 8px;" colspan="2">
            <strong><?php echo htmlspecialchars($moduloCodigo); ?> - </strong>
            <strong><?php echo htmlspecialchars($moduloNombre); ?></strong>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; padding: 8px;">DOCENTE:</td>
        <td style="border: 1px solid black; padding: 8px;" colspan="2"><?php echo htmlspecialchars($docenteNombre); ?></td>
    </tr>
     <tr>
        <td style="border: 1px solid black; padding: 8px;">FECHA:</td>
        <td style="border: 1px solid black; padding: 8px;" colspan="2"> <input type="date">  <input type="date"> </td>
    </tr>
</table>
    </div>
    <center> <h2>PLANILLA DE CALIFICACIONES</h2></center>

    <!-- TABLA DE CALIFICACIONES -->
    <table class="tabla-calificaciones">
        <thead>
            <tr>
               <th class="col-numero">#</th>
                
               <th class="col-nombre">NOMBRE COMPLETO</th>
      <th class="col-sigla">SIGLA</th>
      <th class="col-modulo">MODULO</th>
      <th class="col-num">NUM.</th>
      <th class="col-literal">LITERAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($estudiantes) > 0):
                $aprobados = 0;
                $reprobados = 0;
                $pendientes = 0;
                $totalNotas = 0;
                $sumaNotas = 0;

                foreach ($estudiantes as $index => $est):
                    $nombreCompleto = htmlspecialchars($est['Nombre'] . ' ' . $est['Apaterno'] . ' ' . $est['Amaterno']);
                    $ci = htmlspecialchars($est['Ci']);
                    $nota = $est['Nota'];
                    $fechaRegistro = $est['FechaRegistro'] ? date('d/m/Y', strtotime($est['FechaRegistro'])) : '-';

                    // Determinar estado
                    if ($nota === null || $nota === '') {
                        $estadoClase = 'badge-pendiente';
                        $estadoTexto = 'Pendiente';
                        $notaMostrar = '-';
                        $notaLiteral = '-';
                        $pendientes++;
                    } else {
                        $notaInt = intval($nota);
                        $notaMostrar = $notaInt;
                        $notaLiteral = numeroALetras($notaInt);
                        $totalNotas++;
                        $sumaNotas += $notaInt;

                        if ($notaInt >= 76) {
                            $estadoClase = 'badge-aprobado';
                            $estadoTexto = 'Aprobado';
                            $aprobados++;
                        } else {
                            $estadoClase = 'badge-reprobado';
                            $estadoTexto = 'Reprobado';
                            $reprobados++;
                        }
                    }
            ?>
            <tr>
                <td class="col-numero"><?php echo $index + 1; ?></td>
                <td class="col-estudiante"><?php echo $nombreCompleto; ?></td>
                <td class="col-estudiante"></td>
                <td class="col-estudiante"><?php echo $moduloCodigo; ?></td>
                <td class="col-nota"><?php echo $notaMostrar; ?></td>
                <td class="col-literal"><?php echo $notaLiteral; ?></td>
            </tr>
            <?php
                endforeach;

                // Calcular promedio
                $promedio = $totalNotas > 0 ? $sumaNotas / $totalNotas : 0;
            ?>
        </tbody>
    </table>



    <?php else: ?>
        </tbody>
    </table>
    <p style="text-align: center; padding: 30px; color: #999; font-style: italic;">
        No hay estudiantes inscritos en este módulo
    </p>
    <?php endif; ?>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p style="text-align: center; margin-bottom: 10px;">
            Este documento certifica las calificaciones finales del módulo mencionado
        </p>

        <div class="firmas">
            <div class="firma">
                <div class="linea"></div>
                <p>Firma del Docente</p>
                <p style="font-weight: normal; font-size: 9pt;"><?php echo htmlspecialchars($docenteNombre); ?></p>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <p>Sello y Firma de Autorización</p>
                <p style="font-weight: normal; font-size: 9pt;">COORDINACION POSGRADO ODONTOLOGIA</p>
            </div>
        </div>

        <p style="text-align: center; margin-top: 30px; font-size: 8pt; color: #999;">
            Documento generado automáticamente por el Sistema de Gestión Académica
        </p>
    </div>

</body>

</html>
