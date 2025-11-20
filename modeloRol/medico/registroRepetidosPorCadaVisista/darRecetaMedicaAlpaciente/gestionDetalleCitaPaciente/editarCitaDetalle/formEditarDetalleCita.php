<?php
session_start();
include_once('../../../../../../shared/pantalla.php');
include_once('../../../../../../modelo/RecetaDetalleDAO.php');

class formEditarDetalleCita extends pantalla
{
    public function formEditarDetalleCitaShow()
    {
        $this->cabeceraShow('Editar Detalle de Receta Médica');

        // Verificar que el usuario sea médico
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Acceso denegado. Solo el personal médico puede editar detalles de recetas.', 
                '../../../../index.php', 
                'error'
            );
            exit();
        }

        // Obtener ID del detalle a editar
        $idDetalle = $_GET['id'] ?? null;
        
        if (!$idDetalle) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ ID de detalle no proporcionado.', 
                '../indexDetalleCita.php', 
                'error'
            );
            exit();
        }

        $objDetalle = new RecetaDetalleDAO();
        $detalle = $objDetalle->obtenerDetallePorId($idDetalle);

        if (!$detalle) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Detalle de receta no encontrado.', 
                '../indexDetalleCita.php', 
                'error'
            );
            exit();
        }

        // Verificar que el médico logueado es el dueño del detalle
        $idUsuarioMedico = $_SESSION['id_usuario'] ?? null;
        
        if (!$objDetalle->validarPropiedadDetalle($idDetalle, $idUsuarioMedico)) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ No tiene permisos para editar este detalle. Solo el médico que creó la receta puede modificarla.', 
                '../indexDetalleCita.php', 
                'error'
            );
            exit();
        }

        $nombreMedico = $_SESSION['login'] ?? 'Usuario no identificado';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white">
            <h4 class="mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                Editar Detalle de Receta Médica
            </h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-person-badge me-2"></i>
                <strong>Médico:</strong> <?php echo htmlspecialchars($nombreMedico); ?>
                <span class="badge bg-primary ms-2">Médico</span>
                <span class="badge bg-success ms-2">Creador de la receta</span>
            </div>

            <!-- Información de la Receta -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-file-medical me-2"></i>
                    Información de la Receta
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Receta #:</strong> <?php echo htmlspecialchars($detalle['id_receta']); ?><br>
                            <strong>Paciente:</strong> <?php echo htmlspecialchars($detalle['nombre_paciente']); ?><br>
                            <strong>DNI:</strong> <?php echo htmlspecialchars($detalle['dni']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Médico:</strong> <?php echo htmlspecialchars($detalle['nombre_medico']); ?><br>
                            <strong>Detalle ID:</strong> <?php echo htmlspecialchars($detalle['id_detalle']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <form action="./getEditarDetalleCita.php" method="POST" id="formEditar">
                <input type="hidden" name="idDetalle" value="<?php echo htmlspecialchars($detalle['id_detalle']); ?>">
                
                <!-- Información del Medicamento -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="medicamento" class="form-label">Medicamento *</label>
                        <input type="text" class="form-control" id="medicamento" name="medicamento" 
                               value="<?php echo htmlspecialchars($detalle['medicamento']); ?>"
                               required placeholder="Ej: Paracetamol, Amoxicilina, etc.">
                        <div class="form-text">Nombre comercial o genérico del medicamento</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="dosis" class="form-label">Dosis *</label>
                        <input type="text" class="form-control" id="dosis" name="dosis" 
                               value="<?php echo htmlspecialchars($detalle['dosis']); ?>"
                               required placeholder="Ej: 500mg, 1 tableta, 5ml">
                        <div class="form-text">Cantidad y unidad de medida por toma</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frecuencia" class="form-label">Frecuencia *</label>
                        <select class="form-select" id="frecuencia" name="frecuencia" required>
                            <option value="">-- Seleccione frecuencia --</option>
                            <option value="Cada 6 horas" <?php echo $detalle['frecuencia'] == 'Cada 6 horas' ? 'selected' : ''; ?>>Cada 6 horas</option>
                            <option value="Cada 8 horas" <?php echo $detalle['frecuencia'] == 'Cada 8 horas' ? 'selected' : ''; ?>>Cada 8 horas</option>
                            <option value="Cada 12 horas" <?php echo $detalle['frecuencia'] == 'Cada 12 horas' ? 'selected' : ''; ?>>Cada 12 horas</option>
                            <option value="Cada 24 horas" <?php echo $detalle['frecuencia'] == 'Cada 24 horas' ? 'selected' : ''; ?>>Cada 24 horas</option>
                            <option value="Una vez al día" <?php echo $detalle['frecuencia'] == 'Una vez al día' ? 'selected' : ''; ?>>Una vez al día</option>
                            <option value="Dos veces al día" <?php echo $detalle['frecuencia'] == 'Dos veces al día' ? 'selected' : ''; ?>>Dos veces al día</option>
                            <option value="Tres veces al día" <?php echo $detalle['frecuencia'] == 'Tres veces al día' ? 'selected' : ''; ?>>Tres veces al día</option>
                            <option value="Antes de las comidas" <?php echo $detalle['frecuencia'] == 'Antes de las comidas' ? 'selected' : ''; ?>>Antes de las comidas</option>
                            <option value="Después de las comidas" <?php echo $detalle['frecuencia'] == 'Después de las comidas' ? 'selected' : ''; ?>>Después de las comidas</option>
                            <option value="Al acostarse" <?php echo $detalle['frecuencia'] == 'Al acostarse' ? 'selected' : ''; ?>>Al acostarse</option>
                            <option value="Según necesidad" <?php echo $detalle['frecuencia'] == 'Según necesidad' ? 'selected' : ''; ?>>Según necesidad</option>
                        </select>
                        <div class="form-text">Con qué frecuencia se debe tomar el medicamento</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="duracion" class="form-label">Duración del Tratamiento</label>
                        <input type="text" class="form-control" id="duracion" name="duracion" 
                               value="<?php echo htmlspecialchars($detalle['duracion'] ?? ''); ?>"
                               placeholder="Ej: 7 días, 10 días, Hasta finalizar, etc.">
                        <div class="form-text">Tiempo que debe durar el tratamiento (opcional)</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas Adicionales</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3" 
                              placeholder="Instrucciones especiales, precauciones, efectos secundarios a observar, etc."><?php echo htmlspecialchars($detalle['notas'] ?? ''); ?></textarea>
                    <div class="form-text">Información adicional importante sobre el medicamento</div>
                </div>

                <!-- Resumen de Cambios -->
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirmar Cambios
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Revise cuidadosamente los cambios antes de guardar. Esta acción actualizará permanentemente el detalle de la receta médica.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning btn-lg">
                        <i class="bi bi-check-circle-fill me-2"></i>Actualizar Detalle
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
    const form = document.getElementById('formEditar');
    const medicamentoOriginal = '<?php echo htmlspecialchars($detalle['medicamento']); ?>';
    const dosisOriginal = '<?php echo htmlspecialchars($detalle['dosis']); ?>';
    const frecuenciaOriginal = '<?php echo htmlspecialchars($detalle['frecuencia']); ?>';
    const duracionOriginal = '<?php echo htmlspecialchars($detalle['duracion'] ?? ''); ?>';
    const notasOriginal = '<?php echo htmlspecialchars($detalle['notas'] ?? ''); ?>';

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const medicamento = document.getElementById('medicamento').value.trim();
        const dosis = document.getElementById('dosis').value.trim();
        const frecuencia = document.getElementById('frecuencia').value;

        // Validaciones básicas
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

        // Verificar si hay cambios reales
        const hayCambios = medicamento !== medicamentoOriginal ||
                          dosis !== dosisOriginal ||
                          frecuencia !== frecuenciaOriginal ||
                          document.getElementById('duracion').value.trim() !== duracionOriginal ||
                          document.getElementById('notas').value.trim() !== notasOriginal;

        if (!hayCambios) {
            e.preventDefault();
            if (confirm('No se detectaron cambios en el formulario. ¿Desea volver sin guardar?')) {
                window.location.href = '../indexDetalleCita.php';
            }
            return false;
        }

        return true;
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

    // Mostrar cambios en tiempo real
    function mostrarCambios() {
        const cambios = [];
        
        if (medicamentoInput.value !== medicamentoOriginal) {
            cambios.push('Medicamento');
        }
        if (document.getElementById('dosis').value !== dosisOriginal) {
            cambios.push('Dosis');
        }
        if (document.getElementById('frecuencia').value !== frecuenciaOriginal) {
            cambios.push('Frecuencia');
        }
        if (document.getElementById('duracion').value !== duracionOriginal) {
            cambios.push('Duración');
        }
        if (document.getElementById('notas').value !== notasOriginal) {
            cambios.push('Notas');
        }

        return cambios;
    }

    // Actualizar indicador de cambios
    const inputs = document.querySelectorAll('#formEditar input, #formEditar select, #formEditar textarea');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const cambios = mostrarCambios();
            console.log('Cambios detectados:', cambios);
        });
    });
});
</script>

<style>
.form-control:focus, .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
}
.card-header {
    font-weight: 600;
}
</style>

<?php
        $this->pieShow();
    }
}
?>