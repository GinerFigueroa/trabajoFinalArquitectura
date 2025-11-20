<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/ConsentimientoInformadoDAO.php');

class formRegistrarConsentimientoInformado extends pantalla
{
    public function formRegistrarConsentimientoInformadoShow()
    {
        $this->cabeceraShow('Registrar Consentimiento Informado');

        $objHC = new EntidadHistoriaClinica();
        $historiasDisponibles = $objHC->obtenerHistoriasClinicasDisponibles();

        $objMedico = new EntidadMedico();
        $medicosDisponibles = $objMedico->obtenerMedicosDisponibles();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-earmark-plus-fill me-2"></i>Nuevo Consentimiento Informado</h4>
        </div>
        <div class="card-body">
            <form action="./getRegistrarConsentimientoInformado.php" method="POST">
                <input type="hidden" name="idPaciente" id="idPaciente" value="">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="historiaClinicaId" class="form-label">Historia Clínica (*):</label>
                        <select class="form-select" id="historiaClinicaId" name="historiaClinicaId" required>
                            <option value="">Seleccione Historia Clínica</option>
                            <?php foreach ($historiasDisponibles as $hc) { ?>
                                <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>">
                                    HC N° <?php echo htmlspecialchars($hc['historia_clinica_id']) . ' - ' . htmlspecialchars($hc['nombre_paciente']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="drTratanteId" class="form-label">Doctor Tratante (*):</label>
                        <select class="form-select" id="drTratanteId" name="drTratanteId" required>
    <option value="">Seleccione Doctor</option>
    <?php foreach ($medicosDisponibles as $dr) { ?>
        <option value="<?php echo htmlspecialchars($dr['id_dr_fk']); ?>">
            <?php echo htmlspecialchars($dr['nombre_medico']); ?>
        </option>
    <?php } ?>
</select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pacienteNombre" class="form-label">Paciente Asignado:</label>
                    <input type="text" class="form-control" id="pacienteNombre" disabled value="Seleccione una Historia Clínica">
                </div>
                
                <div class="mb-3">
                    <label for="diagnostico" class="form-label">Diagnóstico / Motivo (*):</label>
                    <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Procedimiento / Tratamiento Informado (*):</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5" required></textarea>
                    <small class="form-text text-muted">Detallar los riesgos y beneficios del procedimiento a realizar.</small>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnRegistrar" class="btn btn-success">Guardar Consentimiento</button>
                    <a href="../indexConsentimientoInformado.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('historiaClinicaId').addEventListener('change', function() {
    const idHC = this.value;
    const inputNombre = document.getElementById('pacienteNombre');
    const inputPacienteId = document.getElementById('idPaciente');
    
    inputNombre.value = "Cargando...";
    inputPacienteId.value = "";

    if (idHC) {
        // Lógica AJAX para obtener el ID de paciente a partir del ID de HC
        fetch('getRegistrarConsentimientoInformado.php?action=infoHC&id=' + idHC)
            .then(response => response.json())
            .then(data => {
                if (data && data.id_paciente) {
                    inputNombre.value = data.nombre_completo;
                    inputPacienteId.value = data.id_paciente;
                } else {
                    inputNombre.value = "Error: Paciente no encontrado.";
                }
            })
            .catch(error => {
                console.error('Error al cargar info HC:', error);
                inputNombre.value = "Error de conexión o datos.";
            });
    } else {
        inputNombre.value = "Seleccione una Historia Clínica";
    }
});
</script>

<?php
        $this->pieShow();
    }
}
?>