/**
 * Sistema de Matriculación/Inscripción
 * Validación del formulario de registro
 */

$(document).ready(function() {
    console.log('Script de inscripción cargado correctamente');

    // ========================================
    // VALIDACIÓN DEL FORMULARIO
    // ========================================

    $('#formMatriculacion').on('submit', function(e) {
        console.log('Formulario enviado - iniciando validación');

        // Validar que se haya seleccionado estudiante
        const estudianteID = $('select[name="idcliente"]').val();
        console.log('Estudiante ID:', estudianteID);

        if (!estudianteID || estudianteID === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un estudiante',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar que se haya seleccionado grado académico
        const gradoAcademico = $('#gradoAcademico').val();
        if (!gradoAcademico || gradoAcademico === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un grado académico',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar que se haya seleccionado programa
        const programaID = $('#programa').val();
        if (!programaID || programaID === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un programa',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar monto de matrícula
        const montoMatricula = $('input[name="montoMatricula"]').val();
        if (!montoMatricula || parseFloat(montoMatricula) <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor ingrese un monto de matrícula válido',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar número de vaucher
        const numeroVaucher = $('input[name="numeroVaucher"]').val();
        if (!numeroVaucher || numeroVaucher.trim() === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor ingrese el número de vaucher',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar fecha de inscripción
        const fechaInscripcion = $('input[name="fechaInscripcion"]').val();
        if (!fechaInscripcion || fechaInscripcion === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione la fecha de inscripción',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar imagen SI se subió una (opcional)
        const comprobanteImagen = $('input[name="comprobanteImagen"]')[0].files[0];
        if (comprobanteImagen) {
            // Validar tipo de archivo
            const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
            if (!tiposPermitidos.includes(comprobanteImagen.type)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato no permitido',
                    text: 'Solo se permiten imágenes JPG, PNG, GIF o archivos PDF',
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }

            // Validar tamaño del archivo (máximo 5MB)
            const tamañoMaximo = 5 * 1024 * 1024; // 5MB
            if (comprobanteImagen.size > tamañoMaximo) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo muy grande',
                    text: 'El archivo no debe superar los 5MB',
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }
        }

        // Validar formulario HTML5
        if (!this.checkValidity()) {
            e.preventDefault();
            this.classList.add('was-validated');
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos requeridos correctamente',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Si todas las validaciones pasan, mostrar datos y procesar
        console.log('Todas las validaciones pasaron. Datos del formulario:');
        console.log('- Estudiante ID:', estudianteID);
        console.log('- Grado Académico:', gradoAcademico);
        console.log('- Programa ID:', programaID);
        console.log('- Monto Matrícula:', montoMatricula);
        console.log('- Número Vaucher:', numeroVaucher);
        console.log('- Fecha Inscripción:', fechaInscripcion);
        console.log('- Imagen:', comprobanteImagen ? comprobanteImagen.name : 'Sin imagen');

        Swal.fire({
            title: 'Procesando...',
            text: 'Registrando la matrícula, por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // El formulario se enviará normalmente
        console.log('Enviando formulario...');
        return true;
    });

    // ========================================
    // VISTA PREVIA DE LA IMAGEN
    // ========================================

    $('input[name="comprobanteImagen"]').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validar que sea una imagen
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Crear preview si no existe
                    let preview = $('#preview-comprobante');
                    if (preview.length === 0) {
                        $('input[name="comprobanteImagen"]').after(
                            '<div id="preview-comprobante" class="mt-2"></div>'
                        );
                        preview = $('#preview-comprobante');
                    }
                    preview.html(`
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">
                        <p class="text-muted small mt-1">
                            <i class="fa fa-file"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                        </p>
                    `);
                };
                reader.readAsDataURL(file);
            } else {
                // Si es PDF u otro tipo
                let preview = $('#preview-comprobante');
                if (preview.length === 0) {
                    $('input[name="comprobanteImagen"]').after(
                        '<div id="preview-comprobante" class="mt-2"></div>'
                    );
                    preview = $('#preview-comprobante');
                }
                preview.html(`
                    <p class="text-info">
                        <i class="fa fa-file-pdf"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                    </p>
                `);
            }
        }
    });

    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    // Establecer fecha actual por defecto
    const hoy = new Date().toISOString().split('T')[0];
    $('input[name="fechaInscripcion"]').val(hoy);

    console.log('Sistema de validación configurado correctamente');
});
