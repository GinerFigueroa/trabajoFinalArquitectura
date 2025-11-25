<?php

session_start();
include_once('../../../../../../shared/pantalla.php');
include_once('../../../../../../modelo/RecetaDetalleDAO.php');

class formAgregarDetalleCita extends pantalla
{
    public function formAgregarDetalleCitaShow()
    {
        $this->cabeceraShow('Agregar Detalle a Receta Médica');

        // Verificar que el usuario sea médico
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Acceso denegado. Solo el personal médico puede agregar detalles de recetas.', 
                '../../../../index.php', 
                'error'
            );
            exit();
        }

        // Obtención de recetas (PENDIENTE DE FILTRADO SEGURO POR EL MEDIATOR/CONTROLADOR)
        $objDetalle = new RecetaDetalleDAO();
        $recetas = $objDetalle->obtenerRecetasMedicas(); // Esta llamada debería ser al Mediator para filtrar
        $nombreMedico = $_SESSION['login'] ?? 'Usuario no identificado';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">
                <i class="bi bi-plus-circle-fill me-2"></i>
                Agregar Detalle a Receta Médica
            </h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-person-badge me-2"></i>
                <strong>Médico:</strong> <?php echo htmlspecialchars($nombreMedico); ?>
                <span class="badge bg-primary ms-2">Médico</span>
            </div>

            <form action="./getAgregarDetalleCita.php" method="POST" id="formDetalle">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="idReceta" class="form-label">Seleccionar Receta Médica *</label>
                        <select class="form-select" id="idReceta" name="idReceta" required>
                            <option value="">-- Seleccione una receta --</option>
                            <?php foreach ($recetas as $receta) { ?>
                                <option value="<?php echo htmlspecialchars($receta['id_receta']); ?>">
                                    Receta #<?php echo htmlspecialchars($receta['id_receta']); ?> - 
                                    <?php echo htmlspecialchars($receta['nombre_paciente']); ?> - 
                                    <?php echo date('d/m/Y', strtotime($receta['fecha'])); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="form-text">
                            Seleccione la receta médica a la que desea agregar este detalle
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="medicamento" class="form-label">Medicamento *</label>
                        <input type="text" class="form-control" id="medicamento" name="medicamento" 
                               required placeholder="Ej: Paracetamol, Amoxicilina, etc.">
                        <div class="form-text">Nombre comercial o genérico del medicamento</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="dosis" class="form-label">Dosis *</label>
                        <input type="text" class="form-control" id="dosis" name="dosis" 
                               required placeholder="Ej: 500mg, 1 tableta, 5ml">
                        <div class="form-text">Cantidad y unidad de medida por toma</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frecuencia" class="form-label">Frecuencia *</label>
                        <select class="form-select" id="frecuencia" name="frecuencia" required>
                            <option value="">-- Seleccione frecuencia --</option>
                            <option value="Cada 6 horas">Cada 6 horas</option>
                            <option value="Cada 8 horas">Cada 8 horas</option>
                            <option value="Cada 12 horas">Cada 12 horas</option>
                            <option value="Cada 24 horas">Cada 24 horas</option>
                            <option value="Una vez al día">Una vez al día</option>
                            <option value="Dos veces al día">Dos veces al día</option>
                            <option value="Tres veces al día">Tres veces al día</option>
                            <option value="Antes de las comidas">Antes de las comidas</option>
                            <option value="Después de las comidas">Después de las comidas</option>
                            <option value="Al acostarse">Al acostarse</option>
                            <option value="Según necesidad">Según necesidad</option>
                        </select>
                        <div class="form-text">Con qué frecuencia se debe tomar el medicamento</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="duracion" class="form-label">Duración del Tratamiento</label>
                        <input type="text" class="form-control" id="duracion" name="duracion" 
                               placeholder="Ej: 7 días, 10 días, Hasta finalizar, etc.">
                        <div class="form-text">Tiempo que debe durar el tratamiento (opcional)</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas Adicionales</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3" 
                              placeholder="Instrucciones especiales, precauciones, efectos secundarios a observar, etc."></textarea>
                    <div class="form-text">Información adicional importante sobre el medicamento</div>
                </div>

                <div class="card border-info mb-4" id="recetaInfo" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle me-2"></i>
                        Información de la Receta Seleccionada
                    </div>
                    <div class="card-body" id="recetaInfoContent">
                        </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnAgregar" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle-fill me-2"></i>Agregar Detalle a la Receta
                    </button>
                    <a href="../indexDetalleCita.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-2"></i>Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDetalle');
    const recetaSelect = document.getElementById('idReceta');
    const recetaInfo = document.getElementById('recetaInfo');
    const recetaInfoContent = document.getElementById('recetaInfoContent');

    // Mostrar información de la receta seleccionada
    recetaSelect.addEventListener('change', function() {
        const recetaId = this.value;
        
        if (recetaId) {
            // Aquí podrías hacer una llamada AJAX para obtener más detalles de la receta
            recetaInfo.style.display = 'block';
            recetaInfoContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Receta #${recetaId}</strong><br>
                        <small class="text-muted">Seleccionada para agregar detalle</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-success">Activa</span>
                    </div>
                </div>
            `;
        } else {
            recetaInfo.style.display = 'none';
        }
    });

    // Sugerencias de medicamentos comunes
    const medicamentoInput = document.getElementById('medicamento');
    const medicamentosComunes = [
        'Paracetamol', 'Ibuprofeno', 'Amoxicilina', 'Omeprazol', 'Loratadina',
        'Metformina', 'Atorvastatina', 'Losartán', 'Amlodipino', 'Salbutamol',
        'Prednisona', 'Diclofenaco', 'Cetirizina', 'Metronidazol', 'Ciprofloxacino'
    ];

    medicamentoInput.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        if (value.length > 2) {
            // Podrías implementar un datalist aquí para sugerencias
        }
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const medicamento = document.getElementById('medicamento').value.trim();
        const dosis = document.getElementById('dosis').value.trim();
        const frecuencia = document.getElementById('frecuencia').value;

        if (medicamento.length < 2) {
            e.preventDefault();
            alert('❌ El nombre del medicamento debe tener al menos 2 caracteres.');
            document.getElementById('medicamento').focus();
            return false;
        }

        if (dosis.length < 1) {
            e.preventDefault();
            alert('❌ La dosis es obligatoria.');
            document.getElementById('dosis').focus();
            return false;
        }

        if (!frecuencia) {
            e.preventDefault();
            alert('❌ Debe seleccionar una frecuencia.');
            document.getElementById('frecuencia').focus();
            return false;
        }

        return true;
    });
});

// Función para agregar otro detalle rápidamente
function limpiarFormulario() {
    document.getElementById('medicamento').value = '';
    document.getElementById('dosis').value = '';
    document.getElementById('frecuencia').value = '';
    document.getElementById('duracion').value = '';
    document.getElementById('notas').value = '';
    document.getElementById('medicamento').focus();
}
</script>

<style>
.form-control:focus, .form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}
.card-header {
    font-weight: 600;
}
</style>

<?php
        $this->pieShow();
    }
}
// Aquí termina formAgregarDetalleCita.php
?>