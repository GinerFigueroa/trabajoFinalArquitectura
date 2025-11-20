<?php
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/InternadoDAO.php');

class formEditarInternado extends pantalla
{
    public function formEditarInternadoShow()
    {
        $this->cabeceraShow("Editar Internado");

        // Obtener ID del internado desde GET
        $idInternado = $_GET['id'] ?? null;

        if (!$idInternado || !is_numeric($idInternado)) {
            echo '<div class="alert alert-danger">ID de internado no válido.</div>';
            $this->pieShow();
            return;
        }

        // Obtener datos del internado
        $objInternado = new InternadoDAO();
        $internado = $objInternado->obtenerInternadoPorId($idInternado);

        if (!$internado) {
            echo '<div class="alert alert-danger">Internado no encontrado.</div>';
            $this->pieShow();
            return;
        }

        // Obtener datos para los selects
        $objAuxiliar = new InternadoAuxiliarDAO();
        $medicos = $objAuxiliar->obtenerMedicos();
        
        // Obtener habitaciones disponibles + la habitación actual
        $habitaciones = $objAuxiliar->obtenerHabitacionesDisponiblesConActual($internado['id_habitacion']);

        // Determinar si es editable
        $esEditable = ($internado['estado'] == 'Activo');
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></h4>
        </div>
        <div class="card-body">
            
            <?php if (!$esEditable) { ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Este internado se encuentra en estado <strong><?php echo htmlspecialchars($internado['estado']); ?></strong> y tiene restricciones de edición.
                </div>
            <?php } ?>

            <form action="./getEditarInternado.php" method="POST" id="formEditarInternado">
                <input type="hidden" name="idInternado" value="<?php echo htmlspecialchars($internado['id_internado']); ?>">
                <input type="hidden" name="idHabitacionAnterior" value="<?php echo htmlspecialchars($internado['id_habitacion']); ?>">

                <!-- Información del Paciente (solo lectura) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">
                            <i class="bi bi-person-vcard me-2"></i>Datos del Paciente
                        </h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Paciente:</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?php echo htmlspecialchars($internado['nombre_paciente'] ?? 'N/A'); ?>" 
                               readonly>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado Actual:</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?php echo htmlspecialchars($internado['estado']); ?>" 
                               readonly>
                    </div>
                </div>

                <!-- Datos Editables del Internado -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">
                            <i class="bi bi-hospital me-2"></i>Datos del Internado
                        </h5>
                    </div>
                    
                    <!-- Habitación -->
                    <div class="col-md-6 mb-3">
                        <label for="idHabitacion" class="form-label">Habitación <?php echo $esEditable ? '(*)' : ''; ?>:</label>
                        <select class="form-select" id="idHabitacion" name="idHabitacion" <?php echo $esEditable ? 'required' : 'disabled'; ?>>
                            <option value="">Seleccione una habitación</option>
                            <?php 
                            if (count($habitaciones) > 0) {
                                foreach ($habitaciones as $habitacion) { 
                                    $selected = ($habitacion['id_habitacion'] == $internado['id_habitacion']) ? 'selected' : '';
                                    $estadoHabitacion = $habitacion['estado'] ?? 'Desconocido';
                                    ?>
                                    <option value="<?php echo htmlspecialchars($habitacion['id_habitacion']); ?>" 
                                            <?php echo $selected; ?>
                                            data-tipo="<?php echo htmlspecialchars($habitacion['tipo']); ?>"
                                            data-piso="<?php echo htmlspecialchars($habitacion['piso']); ?>"
                                            data-estado="<?php echo htmlspecialchars($estadoHabitacion); ?>">
                                        <?php echo htmlspecialchars(
                                            'Habitación ' . $habitacion['numero_puerta'] . 
                                            ' - Piso ' . $habitacion['piso'] . 
                                            ' (' . $habitacion['tipo'] . ') - ' . $estadoHabitacion
                                        ); ?>
                                    </option>
                                <?php }
                            } else { ?>
                                <option value="" disabled>No hay habitaciones disponibles</option>
                            <?php } ?>
                        </select>
                        <div class="form-text">
                            <?php if ($esEditable) { ?>
                                Solo se muestran habitaciones disponibles + la actual.
                            <?php } else { ?>
                                La habitación no se puede cambiar porque el internado no está activo.
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Médico -->
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante <?php echo $esEditable ? '(*)' : ''; ?>:</label>
                        <select class="form-select" id="idMedico" name="idMedico" <?php echo $esEditable ? 'required' : 'disabled'; ?>>
                            <option value="">Seleccione un médico</option>
                            <?php 
                            if (count($medicos) > 0) {
                                foreach ($medicos as $medico) { 
                                    $selected = ($medico['id_medico'] == $internado['id_medico']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo htmlspecialchars($medico['id_medico']); ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($medico['nombre_completo']); ?>
                                    </option>
                                <?php }
                            } else { ?>
                                <option value="" disabled>No hay médicos disponibles</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Fechas y Estado -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Ingreso:</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?php echo date('d/m/Y H:i', strtotime($internado['fecha_ingreso'])); ?>" 
                               readonly>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="fechaAlta" class="form-label">Fecha de Alta:</label>
                        <input type="datetime-local" class="form-control" id="fechaAlta" name="fechaAlta" 
                               value="<?php echo $internado['fecha_alta'] ? date('Y-m-d\TH:i', strtotime($internado['fecha_alta'])) : ''; ?>"
                               <?php echo !$esEditable ? 'disabled' : ''; ?>>
                        <div class="form-text">Complete solo si da de alta al paciente.</div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado <?php echo $esEditable ? '(*)' : ''; ?>:</label>
                        <select class="form-select" id="estado" name="estado" <?php echo $esEditable ? 'required' : 'disabled'; ?>>
                            <option value="Activo" <?php echo $internado['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="Alta" <?php echo $internado['estado'] == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                            <option value="Derivado" <?php echo $internado['estado'] == 'Derivado' ? 'selected' : ''; ?>>Derivado</option>
                            <option value="Fallecido" <?php echo $internado['estado'] == 'Fallecido' ? 'selected' : ''; ?>>Fallecido</option>
                        </select>
                    </div>
                </div>

                <!-- Diagnóstico y Observaciones -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="diagnostico" class="form-label">Diagnóstico de Ingreso <?php echo $esEditable ? '(*)' : ''; ?>:</label>
                        <textarea class="form-control" id="diagnostico" name="diagnostico" 
                                  rows="3" <?php echo $esEditable ? 'required' : 'disabled'; ?>
                                  placeholder="Describa el diagnóstico del paciente..."><?php echo htmlspecialchars($internado['diagnostico_ingreso'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="observaciones" class="form-label">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" <?php echo !$esEditable ? 'disabled' : ''; ?>
                                  placeholder="Observaciones adicionales..."><?php echo htmlspecialchars($internado['observaciones'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Información de Cambios de Estado -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle me-2"></i>Importante:</h6>
                            <ul class="mb-0">
                                <li>Al cambiar el estado a <strong>"Alta"</strong>, <strong>"Derivado"</strong> o <strong>"Fallecido"</strong>, la habitación actual será liberada automáticamente.</li>
                                <li>Si cambia de habitación, la habitación anterior se liberará y la nueva se ocupará.</li>
                                <li>Los internados en estado <strong>"Activo"</strong> pueden ser editados completamente.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <?php if ($esEditable) { ?>
                                <button type="submit" name="btnEditar" class="btn btn-warning btn-lg">
                                    <i class="bi bi-check-circle-fill me-2"></i>Guardar Cambios
                                </button>
                            <?php } else { ?>
                                <button type="button" class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-lock-fill me-2"></i>Edición Restringida
                                </button>
                            <?php } ?>
                            <a href="../indexGestionInternados.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left-circle-fill me-2"></i>Volver al Listado
                            </a>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- JavaScript para interactividad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectEstado = document.getElementById('estado');
    const selectHabitacion = document.getElementById('idHabitacion');
    const inputFechaAlta = document.getElementById('fechaAlta');
    const form = document.getElementById('formEditarInternado');

    // Mostrar/ocultar fecha de alta según estado
    function actualizarVisibilidadFechaAlta() {
        const estado = selectEstado.value;
        if (estado === 'Alta') {
            inputFechaAlta.required = true;
            // Si no tiene fecha de alta, establecer la actual
            if (!inputFechaAlta.value) {
                const now = new Date();
                inputFechaAlta.value = now.toISOString().slice(0, 16);
            }
        } else {
            inputFechaAlta.required = false;
        }
    }

    // Validar cambios de estado
    selectEstado.addEventListener('change', function() {
        actualizarVisibilidadFechaAlta();
        
        const nuevoEstado = this.value;
        const estadoAnterior = '<?php echo $internado['estado']; ?>';
        
        if (nuevoEstado !== 'Activo' && estadoAnterior === 'Activo') {
            if (!confirm(`¿Está seguro de cambiar el estado a "${nuevoEstado}"?\n\nEsta acción liberará la habitación actual.`)) {
                this.value = estadoAnterior;
                actualizarVisibilidadFechaAlta();
            }
        }
    });

    // Validar cambio de habitación
    selectHabitacion.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const estadoHabitacion = selectedOption.getAttribute('data-estado');
        const habitacionAnterior = '<?php echo $internado['id_habitacion']; ?>';
        const habitacionNueva = selectedOption.value;

        if (habitacionNueva !== habitacionAnterior && estadoHabitacion !== 'Disponible' && estadoHabitacion !== 'Ocupada') {
            alert('No puede seleccionar esta habitación. Solo están disponibles las habitaciones con estado "Disponible" o la habitación actual.');
            this.value = habitacionAnterior;
            return;
        }

        if (habitacionNueva !== habitacionAnterior) {
            if (!confirm('¿Está seguro de cambiar la habitación?\n\nLa habitación anterior será liberada y la nueva se ocupará.')) {
                this.value = habitacionAnterior;
            }
        }
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const estado = selectEstado.value;
        const fechaAlta = inputFechaAlta.value;
        const fechaAltaDateTime = fechaAlta ? new Date(fechaAlta) : null;
        const fechaActual = new Date();

        // Validar fecha de alta si el estado es "Alta"
        if (estado === 'Alta' && !fechaAlta) {
            e.preventDefault();
            alert('Error: Debe especificar una fecha de alta cuando el estado es "Alta".');
            inputFechaAlta.focus();
            return false;
        }

        // Validar que la fecha de alta no sea futura
        if (fechaAltaDateTime && fechaAltaDateTime > fechaActual) {
            e.preventDefault();
            alert('Error: La fecha de alta no puede ser futura.');
            inputFechaAlta.focus();
            return false;
        }

        // Validar que la fecha de alta no sea anterior a la fecha de ingreso
        const fechaIngreso = new Date('<?php echo $internado['fecha_ingreso']; ?>');
        if (fechaAltaDateTime && fechaAltaDateTime < fechaIngreso) {
            e.preventDefault();
            alert('Error: La fecha de alta no puede ser anterior a la fecha de ingreso.');
            inputFechaAlta.focus();
            return false;
        }

        // Confirmación final
        if (!confirm('¿Está seguro de guardar los cambios en este internado?')) {
            e.preventDefault();
            return false;
        }
    });

    // Inicializar visibilidad de fecha de alta
    actualizarVisibilidadFechaAlta();
});
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.form-control, .form-select {
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>

<?php
        $this->pieShow();
    }
}
?>