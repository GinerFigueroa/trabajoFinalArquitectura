<?php
// formAgregarHistorialPaciente.php

include_once("../../../../../shared/pantalla.php"); 
include_once("../../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../../shared/mensajeSistema.php");

class formAgregarHistorialPaciente extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new RegistroMedicoDAO();
    }

    public function formAgregarHistorialPacienteShow()
    {
        $this->cabeceraShow("Nueva Consulta Médica - Historial Clínico");

        // Obtener historias clínicas disponibles para el selector
        $historiasClinicas = $this->objDAO->obtenerHistoriasClinicas();

        // Manejo de mensajes de error si los hay
        $error = $_GET['error'] ?? null;
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-plus-circle me-2"></i>Nueva Consulta Médica</h4>
            <p class="mb-0">Registrar nueva consulta en el historial clínico</p>
        </div>
        <div class="card-body">
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="./getAgregarHistorialMedico.php" method="POST">
                <!-- Selección de Historia Clínica -->
                <div class="mb-3">
                    <label for="historia_clinica_id" class="form-label">
                        <strong>Seleccionar Paciente (Historia Clínica) *</strong>
                    </label>
                    <select class="form-select" id="historia_clinica_id" name="historia_clinica_id" required>
                        <option value="">-- Seleccione un paciente --</option>
                        <?php foreach ($historiasClinicas as $hc): ?>
                            <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>">
                                <?php echo htmlspecialchars("HC-{$hc['historia_clinica_id']} - {$hc['nombre_paciente']} (DNI: {$hc['dni']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Seleccione la historia clínica del paciente</div>
                </div>

                <hr>

                <!-- Motivo de Consulta -->
                <div class="mb-3">
                    <label for="motivo_consulta" class="form-label">
                        <strong>Motivo de Consulta *</strong>
                    </label>
                    <textarea class="form-control" id="motivo_consulta" name="motivo_consulta" 
                              rows="3" placeholder="Describa el motivo principal de la consulta..." required></textarea>
                </div>

                <!-- Enfermedad Actual -->
                <div class="mb-3">
                    <label for="enfermedad_actual" class="form-label">
                        <strong>Enfermedad Actual</strong>
                    </label>
                    <textarea class="form-control" id="enfermedad_actual" name="enfermedad_actual" 
                              rows="3" placeholder="Describa la enfermedad actual del paciente..."></textarea>
                </div>

                <!-- Tiempo de Enfermedad -->
                <div class="mb-3">
                    <label for="tiempo_enfermedad" class="form-label">
                        <strong>Tiempo de Enfermedad</strong>
                    </label>
                    <input type="text" class="form-control" id="tiempo_enfermedad" name="tiempo_enfermedad" 
                           placeholder="Ej: 3 días, 2 semanas, 1 mes...">
                </div>

                <!-- Signos y Síntomas -->
                <div class="mb-3">
                    <label for="signos_sintomas" class="form-label">
                        <strong>Signos y Síntomas</strong>
                    </label>
                    <textarea class="form-control" id="signos_sintomas" name="signos_sintomas" 
                              rows="3" placeholder="Describa los signos y síntomas presentes..."></textarea>
                </div>

                <!-- Riesgos -->
                <div class="mb-3">
                    <label for="riesgos" class="form-label">
                        <strong>Factores de Riesgo</strong>
                    </label>
                    <textarea class="form-control" id="riesgos" name="riesgos" 
                              rows="2" placeholder="Describa los factores de riesgo identificados..."></textarea>
                </div>

                <!-- Motivo Última Visita -->
                <div class="mb-3">
                    <label for="motivo_ultima_visita" class="form-label">
                        <strong>Motivo de la Última Visita</strong>
                    </label>
                    <textarea class="form-control" id="motivo_ultima_visita" name="motivo_ultima_visita" 
                              rows="2" placeholder="Describa el motivo de la última visita médica..."></textarea>
                </div>

                <!-- Última Visita Médica -->
                <div class="mb-4">
                    <label for="ultima_visita_medica" class="form-label">
                        <strong>Fecha de Última Visita Médica</strong>
                    </label>
                    <input type="date" class="form-control" id="ultima_visita_medica" name="ultima_visita_medica">
                    <div class="form-text">Dejar vacío si no aplica</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Guardar Registro
                    </button>
                    <a href="../indexHistorialMedico.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Validación básica del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const historiaClinica = document.getElementById('historia_clinica_id').value;
        const motivoConsulta = document.getElementById('motivo_consulta').value.trim();
        
        if (!historiaClinica) {
            e.preventDefault();
            alert('Debe seleccionar un paciente (historia clínica).');
            document.getElementById('historia_clinica_id').focus();
            return;
        }
        
        if (!motivoConsulta) {
            e.preventDefault();
            alert('El motivo de consulta es obligatorio.');
            document.getElementById('motivo_consulta').focus();
        }
    });

    // Limitar la fecha máxima a hoy
    document.getElementById('ultima_visita_medica').max = new Date().toISOString().split('T')[0];
</script>

<?php
        $this->pieShow();
    }
}
?>