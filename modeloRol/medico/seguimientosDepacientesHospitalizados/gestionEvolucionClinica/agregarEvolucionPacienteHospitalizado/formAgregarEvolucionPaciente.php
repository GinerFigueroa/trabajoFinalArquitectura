<?php

include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/InternadoSeguimientoDAO.php');



class formAgregarEvolucionPaciente extends pantalla
{
    public function formAgregarEvolucionPacienteShow()
    {
        $this->cabeceraShow('Registrar Evolución Clínica');

        $objAuxiliar = new EntidadAuxiliarDAO();
        $internados = $objAuxiliar->obtenerInternadosActivosConNombrePaciente();
        $medicos = $objAuxiliar->obtenerMedicosActivos();
        $enfermeros = $objAuxiliar->obtenerEnfermerosActivos();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-journal-plus me-2"></i>Nuevo Registro de Evolución</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarEvolucionPaciente.php" method="POST">
                
                <div class="mb-3">
                    <label for="idInternado" class="form-label">Paciente Hospitalizado:</label>
                    <select class="form-select" id="idInternado" name="idInternado" required>
                        <option value="">-- Seleccione un Paciente (Internado Activo) --</option>
                        <?php foreach ($internados as $internado) { ?>
                            <option value="<?php echo htmlspecialchars($internado['id_internado']); ?>">
                                <?php echo htmlspecialchars("ID: {$internado['id_internado']} - {$internado['nombre_completo']} - Ingreso: " . date('d/m/Y', strtotime($internado['fecha_ingreso']))); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante:</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach ($medicos as $medico) { ?>
                                <option value="<?php echo htmlspecialchars($medico['id_usuario']); ?>">
                                    <?php echo htmlspecialchars("{$medico['nombre']} {$medico['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idEnfermera" class="form-label">Enfermera (Opcional):</label>
                        <select class="form-select" id="idEnfermera" name="idEnfermera">
                            <option value="">-- Seleccione una Enfermera --</option>
                            <?php foreach ($enfermeros as $enfermero) { ?>
                                <option value="<?php echo htmlspecialchars($enfermero['id_usuario']); ?>">
                                    <?php echo htmlspecialchars("{$enfermero['nombre']} {$enfermero['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evolucion" class="form-label">Evolución Clínica:</label>
                    <textarea class="form-control" id="evolucion" name="evolucion" rows="5" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Tratamiento/Indicaciones:</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Guardar Evolución</button>
                    <a href="../indexEvolucionClinicaPacienteHospitalizado.php" class="btn btn-secondary">Cancelar</a>
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