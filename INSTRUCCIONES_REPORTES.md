# Sistema de Reportes PDF - TCPDF

## Estado Actual
✅ TCPDF instalado en `vendor/tecnickcom/tcpdf/`
✅ Autoloader creado en `vendor/autoload.php`
✅ Modelo de reportes creado en `modelos/reportes.modelo.php`

## Reportes Implementados en el Modelo
1. **Lista Completa de Estudiantes** - `ListaEstudiantesCompleta()`
2. **Estudiantes por Programa** - `EstudiantesPorPrograma($programaID)`
3. **Módulos con Matriculados** - `ModulosConMatriculados()`
4. **Estudiantes en un Módulo** - `EstudiantesEnModulo($codigoModulo)`

## Siguientes Pasos para Completar

### 1. Crear el Controlador de Reportes
Crea el archivo `controladores/reportes.controlador.php` con la estructura base:

```php
<?php
require_once 'vendor/autoload.php';
require_once 'modelos/reportes.modelo.php';

class ReportesControladores extends TCPDF
{
    // Header del PDF
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'UNIVERSIDAD - POSGRADO FCS', 0, false, 'C');
        $this->Ln();
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'Reporte Generado: ' . date('d/m/Y H:i'), 0, false, 'R');
        $this->Ln(10);
    }

    // Footer del PDF
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}
```

### 2. Métodos para Generar Reportes

Agrega estos métodos al controlador:

#### Reporte de Lista de Estudiantes
```php
public static function GenerarReporteEstudiantes() {
    $pdf = new self();
    $pdf->SetCreator('Sistema Posgrado');
    $pdf->SetAuthor('Posgrado FCS');
    $pdf->SetTitle('Lista de Estudiantes');

    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'LISTA GENERAL DE ESTUDIANTES', 0, 1, 'C');
    $pdf->Ln(5);

    // Tabla de estudiantes
    $estudiantes = ReportesModelos::ListaEstudiantesCompleta();

    // Header de tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255);
    $pdf->Cell(10, 7, 'Nº', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'C.I.', 1, 0, 'C', true);
    $pdf->Cell(70, 7, 'Nombre Completo', 1, 0, 'C', true);
    $pdf->Cell(50, 7, 'Profesión', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Celular', 1, 1, 'C', true);

    // Datos
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0);
    $contador = 1;

    foreach ($estudiantes as $est) {
        $pdf->Cell(10, 6, $contador++, 1, 0, 'C');
        $pdf->Cell(25, 6, $est['Ci'] . ' ' . $est['Exp'], 1, 0, 'C');
        $pdf->Cell(70, 6, $est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'], 1, 0, 'L');
        $pdf->Cell(50, 6, $est['NombreProfesion'], 1, 0, 'L');
        $pdf->Cell(35, 6, $est['Celular'], 1, 1, 'C');
    }

    $pdf->Output('Lista_Estudiantes_' . date('Y-m-d') . '.pdf', 'D');
}
```

#### Reporte de Estudiantes por Programa
```php
public static function GenerarReportePorPrograma($programaID = null) {
    $pdf = new self();
    $pdf->SetCreator('Sistema Posgrado');
    $pdf->SetTitle('Estudiantes por Programa');

    $pdf->AddPage();

    $estudiantes = ReportesModelos::EstudiantesPorPrograma($programaID);
    $programaActual = '';
    $contador = 1;

    foreach ($estudiantes as $est) {
        if ($programaActual != $est['NombrePrograma']) {
            $programaActual = $est['NombrePrograma'];

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, 'PROGRAMA: ' . strtoupper($programaActual), 0, 1, 'L');
            $pdf->Ln(2);

            // Header
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(102, 126, 234);
            $pdf->SetTextColor(255);
            $pdf->Cell(10, 6, 'Nº', 1, 0, 'C', true);
            $pdf->Cell(60, 6, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'C.I.', 1, 0, 'C', true);
            $pdf->Cell(40, 6, 'Profesión', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'F. Inscripción', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'Celular', 1, 1, 'C', true);

            $pdf->SetTextColor(0);
            $contador = 1;
        }

        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(10, 5, $contador++, 1, 0, 'C');
        $pdf->Cell(60, 5, $est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'], 1, 0, 'L');
        $pdf->Cell(30, 5, $est['Ci'] . ' ' . $est['Exp'], 1, 0, 'C');
        $pdf->Cell(40, 5, substr($est['NombreProfesion'], 0, 25), 1, 0, 'L');
        $pdf->Cell(30, 5, date('d/m/Y', strtotime($est['FechaInscripcion'])), 1, 0, 'C');
        $pdf->Cell(20, 5, $est['Celular'], 1, 1, 'C');
    }

    $pdf->Output('Estudiantes_Por_Programa_' . date('Y-m-d') . '.pdf', 'D');
}
```

#### Reporte de Módulos y Matriculados
```php
public static function GenerarReporteModulos() {
    $pdf = new self();
    $pdf->SetTitle('Módulos y Matriculados');

    $pdf->AddPage('L'); // Landscape
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'REPORTE DE MÓDULOS Y MATRICULADOS', 0, 1, 'C');
    $pdf->Ln(5);

    $modulos = ReportesModelos::ModulosConMatriculados();

    // Header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255);
    $pdf->Cell(25, 7, 'Código', 1, 0, 'C', true);
    $pdf->Cell(100, 7, 'Nombre Módulo', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Horas', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Costo', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Matriculados', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Pagados', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Pendientes', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Recaudado', 1, 1, 'C', true);

    // Datos
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(0);

    foreach ($modulos as $mod) {
        $pdf->Cell(25, 6, $mod['CodigoModulo'], 1, 0, 'C');
        $pdf->Cell(100, 6, substr($mod['NombreModulo'], 0, 60), 1, 0, 'L');
        $pdf->Cell(25, 6, $mod['HorasAcademicas'], 1, 0, 'C');
        $pdf->Cell(30, 6, 'Bs. ' . number_format($mod['Costo'], 2), 1, 0, 'R');
        $pdf->Cell(30, 6, $mod['TotalMatriculados'], 1, 0, 'C');
        $pdf->Cell(25, 6, $mod['TotalPagados'], 1, 0, 'C');
        $pdf->Cell(30, 6, $mod['TotalPendientes'], 1, 0, 'C');
        $pdf->Cell(35, 6, 'Bs. ' . number_format($mod['TotalRecaudado'], 2), 1, 1, 'R');
    }

    $pdf->Output('Reporte_Modulos_' . date('Y-m-d') . '.pdf', 'D');
}
```

### 3. Crear la Vista de Reportes

Archivo: `vistas/componentes/reportes.php`

La vista debe incluir:
- Botones para generar cada tipo de reporte
- Formulario para seleccionar programa (en reporte por programa)
- Diseño similar a las demás vistas del sistema

### 4. Integrar en el Sistema

1. Agregar opción en el menú sidebar
2. Configurar la ruta en `index.php`

## Enlaces de Referencia
- [TCPDF Documentation](https://tcpdf.org/)
- [GitHub TCPDF](https://github.com/tecnickcom/TCPDF)

## Notas
- Los reportes se descargan automáticamente en formato PDF
- Se pueden personalizar colores, fuentes y formatos
- La orientación puede ser Portrait (vertical) o Landscape (horizontal)
