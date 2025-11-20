<?php

include_once('../../../../shared/pantalla.php');
include_once("../../../modelo/ExamenClinicoDAO.php");
include_once('../../../../shared/mensajeSistema.php');

class formEditarPacienteHospitalizado extends pantalla
{
    public function formEditarPacienteHospitalizadoShow()
    {
        $this->cabeceraShow('Editar Evolución Clínica');

        $idSeguimiento = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $objMensaje = new mensajeSistema();

        if (!$idSeguimiento) {
            $objMensaje->mensajeSistemaShow("ID de registro de seguimiento no proporcionado para edición.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }

        $objSeguimiento = new ExamenClinicoDAO();
        $objAuxiliar = new ExamenCLinicoDAO();

        // 1. Obtener el registro a editar
        $seguimiento = $objSeguimiento->obtenerSeguimientoPorId($idSeguimiento);
        
        if (!$seguimiento) {
            $objMensaje->mensajeSistemaShow("Registro de seguimiento no encontrado. ID: $idSeguimiento.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }

        // 2. Obtener listas auxiliares
        $internados = $objAuxiliar->obtenerInternadosActivosConNombrePaciente();
        $medicos = $objAuxiliar->obtenerMedicosActivos();
        $enfermeros = $objAuxiliar->obtenerEnfermerosActivos();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-fill me-2"></i>Actualizar Evolución Clínica (ID: <?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>)</h4>
        </div>
        <div class="card-body">
            <form action="./getEditaraPacienteHospitazado.php" method="POST">
                
                <input type="hidden" name="idSeguimiento" value="<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>">
                
                <div class="mb-3">
                    <label for="idInternado" class="form-label">Paciente Hospitalizado:</label>
                    <select class="form-select" id="idInternado" name="idInternado" required>
                        <option value="">-- Seleccione un Paciente (Internado Activo) --</option>
                        <?php foreach ($internados as $internado) { 
                            $selected = ($internado['id_internado'] == $seguimiento['id_internado']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($internado['id_internado']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars("ID Internado: {$internado['id_internado']} - {$internado['nombre_completo']}"); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante:</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach ($medicos as $medico) { 
                                $selected = ($medico['id_usuario'] == $seguimiento['id_medico']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($medico['id_usuario']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars("{$medico['nombre']} {$medico['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idEnfermera" class="form-label">Enfermera (Opcional):</label>
                        <select class="form-select" id="idEnfermera" name="idEnfermera">
                            <option value="">-- Seleccione una Enfermera --</option>
                            <?php foreach ($enfermeros as $enfermero) { 
                                $selected = ($enfermero['id_usuario'] == $seguimiento['id_enfermera']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($enfermero['id_usuario']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars("{$enfermero['nombre']} {$enfermero['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evolucion" class="form-label">Evolución Clínica:</label>
                    <textarea class="form-control" id="evolucion" name="evolucion" rows="5" required><?php echo htmlspecialchars($seguimiento['evolucion']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Tratamiento/Indicaciones:</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5"><?php echo htmlspecialchars($seguimiento['tratamiento']); ?></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-warning text-white"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Evolución</button>
                    <a href="../indexEvolucionClinicaPacienteHospitalizado.php" class="btn btn-secondary">Cancelar y Volver</a>
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