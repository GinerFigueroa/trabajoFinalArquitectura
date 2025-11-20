<?php
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/InternadoDAO.php');

class formRegistroInternado extends pantalla
{
    public function formRegistroInternadoShow()
    {
        $this->cabeceraShow("Registrar Nuevo Internado");

        // Obtener datos para los selects
        $objAuxiliar = new InternadoAuxiliarDAO();
        $pacientes = $objAuxiliar->obtenerPacientesNoInternados();
        $medicos = $objAuxiliar->obtenerMedicos();
        $habitaciones = $objAuxiliar->obtenerHabitacionesDisponibles();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Internado</h4>
        </div>
        <div class="card-body">
            <form action="./getRegistroInternado.php" method="POST" id="formRegistroInternado">
                
                <!-- Datos del Paciente -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">
                            <i class="bi bi-person-vcard me-2"></i>Datos del Paciente
                        </h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione un paciente</option>
                            <?php foreach ($pacientes as $paciente) { ?>
                                <option value="<?php echo htmlspecialchars($paciente['id_paciente']); ?>">
                                    <?php echo htmlspecialchars($paciente['nombre_completo'] . ' - DNI: ' . $paciente['dni']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="form-text">Solo se muestran pacientes no internados actualmente.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Información del Paciente:</label>
                        <div id="infoPaciente" class="p-3 bg-light rounded">
                            <small class="text-muted">Seleccione un paciente para ver su información</small>
                        </div>
                    </div>
                </div>

                <!-- Datos del Internado -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">
                            <i class="bi bi-hospital me-2"></i>Datos del Internado
                        </h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="idHabitacion" class="form-label">Habitación (*):</label>
                        <select class="form-select" id="idHabitacion" name="idHabitacion" required>
                            <option value="">Seleccione una habitación</option>
                            <?php foreach ($habitaciones as $habitacion) { ?>
                                <option value="<?php echo htmlspecialchars($habitacion['id_habitacion']); ?>"
                                        data-tipo="<?php echo htmlspecialchars($habitacion['tipo']); ?>"
                                        data-piso="<?php echo htmlspecialchars($habitacion['piso']); ?>">
                                    <?php echo htmlspecialchars(
                                        'Habitación ' . $habitacion['numero_puerta'] . 
                                        ' - Piso ' . $habitacion['piso'] . 
                                        ' (' . $habitacion['tipo'] . ')'
                                    ); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="form-text">Solo se muestran habitaciones disponibles.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante (*):</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">Seleccione un médico</option>
                            <?php foreach ($medicos as $medico) { ?>
                                <option value="<?php echo htmlspecialchars($medico['id_medico']); ?>">
                                    <?php echo htmlspecialchars($medico['nombre_completo']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Fecha y Diagnóstico -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="fechaIngreso" class="form-label">Fecha y Hora de Ingreso (*):</label>
                        <input type="datetime-local" class="form-control" id="fechaIngreso" name="fechaIngreso" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="diagnostico" class="form-label">Diagnóstico de Ingreso (*):</label>
                        <textarea class="form-control" id="diagnostico" name="diagnostico" 
                                  rows="3" placeholder="Describa el diagnóstico del paciente..." required></textarea>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" name="btnRegistrar" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle-fill me-2"></i>Registrar Internado
                            </button>
                            <a href="../indexGestionInternados.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-arrow-left-circle-fill me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>



<?php
        $this->pieShow();
    }
}
?>