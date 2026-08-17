<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir controlador
require_once 'controladores/ordenpago.controlador.php';
require_once 'modelos/conexion.modelo.php';

// Procesar formulario (si viene un POST de "Generar Orden de Pago")
$ordenPago = new OrdenPagoControladores();
$ordenPago->RegistrarOrdenPagoControlador();

// Esta pantalla ya NO busca/selecciona estudiante ni programa: se llega aquí
// desde "Validar Voucher" (preregistro), que ya inscribió formalmente al
// estudiante y conoce exactamente para quién y qué programa se genera esta
// orden de pago (el resto del programa, matrícula ya cancelada).
$estudianteID = isset($_GET['estudianteID']) ? (int)$_GET['estudianteID'] : 0;
$programaID = isset($_GET['programaID']) ? (int)$_GET['programaID'] : 0;

$estudiante = null;
$programa = null;

if ($estudianteID > 0 && $programaID > 0) {
    $pdo = Conexion::Conectar();

    $stmtEst = $pdo->prepare("SELECT * FROM estudiante WHERE EstudianteID = :id");
    $stmtEst->bindParam(':id', $estudianteID, PDO::PARAM_INT);
    $stmtEst->execute();
    $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

    $programa = ProgramasModelos::MostrarDetallePrograma($programaID);
}

$datosCompletos = $estudiante && $programa;

if ($datosCompletos) {
    $ciCompleto = $estudiante['Ci'] . (!empty($estudiante['Complemento']) ? '-' . $estudiante['Complemento'] : '') . ' ' . $estudiante['Exp'];
    $nombreCompleto = trim($estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'] . ' ' . $estudiante['Nombre']);
    $costoPrograma = (float)$programa['Costo'];
}
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left
kt-aside--fixed kt-page--loading">

  <!-- Header Mobile -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
        <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40">
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler">
        <i class="flaticon-more-1"></i>
      </button>
    </div>
  </div>
  <!-- End Header Mobile -->

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background-color: #EAEAEA; font-size:12pt;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

        <?php
          $NavBar = new FuncionesControladores();
          $NavBar->NavBarControlador();
        ?>

        <button class="kt-aside-close" id="kt_aside_close_btn">
          <i class="la la-close"></i>
        </button>

        <?php
          $Sidebar = new FuncionesControladores();
          $Sidebar->SidebarControlador();
        ?>

        <!-- Cuerpo principal -->
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>ORDEN DE PAGO</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>PAGO DEL PROGRAMA (MATRÍCULA YA VALIDADA)</h3>
                    </a>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contenido principal -->
            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="row">
                <div class="col-lg-12">

<?php if (!$datosCompletos): ?>

                  <div class="kt-portlet">
                    <div class="kt-portlet__body">
                      <div class="alert alert-warning mb-0">
                        <h5><i class="flaticon2-information"></i> Falta el estudiante y/o el programa</h5>
                        <p>
                            Esta pantalla genera la orden de pago del <strong>resto del programa</strong> para un
                            estudiante cuya matrícula ya fue validada. Se llega aquí automáticamente desde
                            <strong>Pre-Registro → Validar Voucher</strong>.
                        </p>
                        <a href="preregistro" class="btn btn-primary">
                            <i class="fa fa-arrow-left"></i> Ir a Pre-Registro
                        </a>
                      </div>
                    </div>
                  </div>

<?php else: ?>

                  <div class="kt-portlet mb-3">
                    <div class="kt-portlet__body py-3">

                      <!-- Datos del estudiante y programa (solo lectura, ya definidos) -->
                      <div class="chip-summary mb-3">
                        <div class="chip-summary-row">
                          <span><i class="flaticon2-user-outline-symbol"></i> <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
                          <span>CI: <?php echo htmlspecialchars($ciCompleto); ?></span>
                          <span><?php echo htmlspecialchars($estudiante['Correo'] ?? '-'); ?></span>
                          <span><?php echo htmlspecialchars($estudiante['Celular'] ?? '-'); ?></span>
                        </div>
                      </div>

                      <div class="chip-summary mb-3">
                        <div class="chip-summary-row flex-wrap">
                          <span><strong><?php echo htmlspecialchars($programa['NombrePrograma']); ?></strong> (<?php echo htmlspecialchars($programa['Codigo']); ?>)</span>
                          <span>Grado: <?php echo htmlspecialchars($programa['GradoAcademico']); ?></span>
                          <span>Duración: <?php echo (int)$programa['DuracionMeses']; ?> meses</span>
                          <span>Costo Programa: <strong class="text-primary">Bs. <?php echo number_format($costoPrograma, 2); ?></strong></span>
                          <span>Módulos: <?php echo (int)$programa['Modulos']; ?></span>
                          <span>Sede: <?php echo htmlspecialchars($programa['Sede']); ?></span>
                        </div>
                      </div>

                      <form method="POST" id="formOrdenPago">
                        <input type="hidden" name="idcliente" value="<?php echo (int)$estudianteID; ?>">
                        <input type="hidden" name="programa" value="<?php echo (int)$programaID; ?>">
                        <input type="hidden" name="pagoCompleto" value="1">
                        <input type="hidden" name="paginaRedirect" value="ordenpago">

                        <hr class="step-divider">

                        <!-- Monto a pagar del programa -->
                        <div class="step-title"><span class="step-num">1</span> Monto a Pagar del Programa</div>
                        <div class="row">
                          <div class="col-lg-3 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Tipo de Pago</label>
                            <select class="form-control form-control-sm" id="tipoPago">
                              <option value="REGULAR">Plan Regular (sin descuento)</option>
                              <option value="CONTADO">Plan al Contado (con descuento)</option>
                              <option value="GRUPAL">Plan Grupal (2+ inscritos)</option>
                            </select>
                          </div>
                          <div class="col-lg-2 form-group mb-2" id="grupoDescuentoManual" style="display: none;">
                            <label class="small font-weight-bold mb-1">% Descuento</label>
                            <input type="number" class="form-control form-control-sm" id="porcentajeDescuentoInput" min="0" max="100" step="0.01" value="0">
                          </div>
                          <div class="col-lg-3 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Monto a Pagar</label>
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                              <input type="text" class="form-control font-weight-bold" id="montoAPagarDisplay" readonly>
                            </div>
                          </div>
                          <div class="col-lg-4 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Fecha de Orden</label>
                            <input type="date" class="form-control form-control-sm" name="fechaInscripcion" value="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                        </div>
                        <small class="text-success font-weight-bold" id="chipDescuentoInfo" style="display: none;"></small>

                        <input type="hidden" name="montoAPagar" id="montoAPagar" value="<?php echo $costoPrograma; ?>">
                        <input type="hidden" name="porcentajeDescuento" id="porcentajeDescuento" value="0">
                        <input type="hidden" name="montoDescuento" id="montoDescuento" value="0">
                        <input type="hidden" name="tipoPago" id="tipoPagoHidden" value="PLAN REGULAR">

                        <hr class="step-divider">

                        <!-- Datos para la Factura -->
                        <div class="step-title"><span class="step-num">2</span> Datos para la Factura</div>
                        <div class="row">
                          <div class="col-lg-6 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Nombre para Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombreFactura"
                                   value="<?php echo htmlspecialchars($nombreCompleto); ?>" required>
                          </div>
                          <div class="col-lg-6 form-group mb-2">
                            <label class="small font-weight-bold mb-1">NIT o CI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nitCiFactura"
                                   value="<?php echo htmlspecialchars($estudiante['Ci'] . (!empty($estudiante['Complemento']) ? '-' . $estudiante['Complemento'] : '')); ?>" required>
                          </div>
                          <div class="col-lg-6 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Responsable</label>
                            <input type="text" class="form-control form-control-sm" name="responsable"
                                   placeholder="Nombre de quien genera la orden">
                          </div>
                          <div class="col-lg-6 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Firma</label>
                            <input type="text" class="form-control form-control-sm" name="firma" placeholder="(opcional)">
                          </div>
                        </div>

                        <div class="text-right mt-3">
                          <a href="preregistro" class="btn btn-sm btn-secondary">
                            <i class="flaticon2-back"></i> Volver
                          </a>
                          <button type="submit" name="registrarOrdenPago" class="btn btn-sm btn-success">
                            <i class="flaticon2-files-and-folders"></i> Generar Orden de Pago
                          </button>
                        </div>
                      </form>

                    </div>
                  </div>

<?php endif; ?>

                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer->FooterControlador();
        ?>
      </div>
    </div>
  </div>

<!-- Scripts necesarios -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js" type="text/javascript"></script>

<!-- SweetAlert v1 (necesario aquí porque se ejecuta antes que los scripts de plantilla.php) -->
<script src="vistas/recursos/sweetalert.min.js" type="text/javascript"></script>

<?php if ($datosCompletos): ?>
<script>
const costoPrograma = <?php echo (float)$costoPrograma; ?>;

function recalcularMontoPrograma() {
    const tipoPago = document.getElementById('tipoPago').value;
    let porcentaje = 0;

    if (tipoPago === 'CONTADO' || tipoPago === 'GRUPAL') {
        porcentaje = parseFloat(document.getElementById('porcentajeDescuentoInput').value) || 0;
        if (porcentaje < 0) porcentaje = 0;
        if (porcentaje > 100) porcentaje = 100;
    }

    const montoDescuento = Math.round((costoPrograma * porcentaje / 100) * 100) / 100;
    const montoFinal = Math.round((costoPrograma - montoDescuento) * 100) / 100;

    document.getElementById('montoAPagarDisplay').value = montoFinal.toFixed(2);
    document.getElementById('montoAPagar').value = montoFinal.toFixed(2);
    document.getElementById('porcentajeDescuento').value = porcentaje.toFixed(2);
    document.getElementById('montoDescuento').value = montoDescuento.toFixed(2);
    document.getElementById('tipoPagoHidden').value = document.getElementById('tipoPago').selectedOptions[0].text;

    const chip = document.getElementById('chipDescuentoInfo');
    if (montoDescuento > 0) {
        chip.textContent = 'Descuento aplicado: Bs. ' + montoDescuento.toFixed(2) + ' (' + porcentaje.toFixed(2) + '%)';
        chip.style.display = 'block';
    } else {
        chip.style.display = 'none';
    }
}

document.getElementById('tipoPago').addEventListener('change', function() {
    const tipoPago = this.value;
    const grupo = document.getElementById('grupoDescuentoManual');
    if (tipoPago === 'CONTADO' || tipoPago === 'GRUPAL') {
        grupo.style.display = '';
    } else {
        grupo.style.display = 'none';
        document.getElementById('porcentajeDescuentoInput').value = 0;
    }
    recalcularMontoPrograma();
});

document.getElementById('porcentajeDescuentoInput').addEventListener('input', recalcularMontoPrograma);

document.getElementById('formOrdenPago').addEventListener('submit', function(e) {
    const monto = parseFloat(document.getElementById('montoAPagar').value) || 0;
    if (monto <= 0) {
        e.preventDefault();
        swal('Error', 'El monto a pagar debe ser mayor a 0', 'error');
        return false;
    }
});

// Calcular el monto inicial (Plan Regular, sin descuento)
recalcularMontoPrograma();
</script>
<?php endif; ?>

<style>
.kt-portlet__head {
    border-radius: 4px 4px 0 0;
}

.step-title {
    font-size: 14px;
    font-weight: 700;
    color: #3b3f5c;
    margin-bottom: 10px;
}

.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 12px;
    margin-right: 6px;
}

.step-divider {
    margin: 12px 0;
    border-top: 1px dashed #dfe1ea;
}

.chip-summary {
    padding: 8px 12px;
    background: #f4f6fb;
    border-left: 3px solid #667eea;
    border-radius: 6px;
}

.chip-summary-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #444;
}
</style>

</body>
</html>
