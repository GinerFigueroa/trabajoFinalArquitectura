<?php


include_once("../../../../../shared/pantalla.php"); 
include_once("../../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../../shared/mensajeSistema.php");

class formEditarHistorialPaciente extends pantalla
{
    private $objDAO;
   
    public function __construct() {
        $this->objDAO = new RegistroMedicoDAO();
    }

    public function formEditarHistorialPacienteShow()
    {
        $this->cabeceraShow("Editar Consulta Médica");

        // Obtener ID del registro desde GET
        $idRegistro = isset($_GET['reg_id']) ? (int)$_GET['reg_id'] : null;

        if (!$idRegistro) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("ID de registro no proporcionado.", "../indexHistorialMedico.php", "error");
            return;
        }

        // Obtener datos del registro
        $registro = $this->objDAO->obtenerRegistroPorId($idRegistro);
        
        if (!$registro) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("Registro médico no encontrado.", "../indexHistorialClinico.php", "error");
            return;
        }

        // Obtener historias clínicas disponibles para el selector
        $historiasClinicas = $this->objDAO->obtenerHistoriasClinicas();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Consulta Médica</h4>
            <p class="mb-0">Paciente: <strong><?php echo htmlspecialchars($registro['nombre_paciente']); ?></strong></p>
        </div>
        <div class="card-body">
            <form action="./getEditarHistorialMedico.php" method="POST">
                <input type="hidden" name="registro_medico_id" value="<?php echo htmlspecialchars($registro['registro_medico_id']); ?>">
                <input type="hidden" name="historia_clinica_id" value="<?php echo htmlspecialchars($registro['historia_clinica_id']); ?>">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>Fecha de registro:</strong> <?php echo date('d/m/Y H:i', strtotime($registro['fecha_registro'])); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>ID Registro:</strong> <?php echo htmlspecialchars($registro['registro_medico_id']); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Motivo de Consulta -->
                <div class="mb-3">
                    <label for="motivo_consulta" class="form-label">
                        <strong>Motivo de Consulta *</strong>
                    </label>
                    <textarea class="form-control" id="motivo_consulta" name="motivo_consulta" 
                              rows="3" placeholder="Describa el motivo principal de la consulta..." required><?php echo htmlspecialchars($registro['motivo_consulta']); ?></textarea>
                </div>

                <!-- Enfermedad Actual -->
                <div class="mb-3">
                    <label for="enfermedad_actual" class="form-label">
                        <strong>Enfermedad Actual</strong>
                    </label>
                    <textarea class="form-control" id="enfermedad_actual" name="enfermedad_actual" 
                              rows="3" placeholder="Describa la enfermedad actual del paciente..."><?php echo htmlspecialchars($registro['enfermedad_actual']); ?></textarea>
                </div>

                <!-- Tiempo de Enfermedad -->
                <div class="mb-3">
                    <label for="tiempo_enfermedad" class="form-label">
                        <strong>Tiempo de Enfermedad</strong>
                    </label>
                    <input type="text" class="form-control" id="tiempo_enfermedad" name="tiempo_enfermedad" 
                           placeholder="Ej: 3 días, 2 semanas, 1 mes..."
                           value="<?php echo htmlspecialchars($registro['tiempo_enfermedad']); ?>">
                </div>

                <!-- Signos y Síntomas -->
                <div class="mb-3">
                    <label for="signos_sintomas" class="form-label">
                        <strong>Signos y Síntomas</strong>
                    </label>
                    <textarea class="form-control" id="signos_sintomas" name="signos_sintomas" 
                              rows="3" placeholder="Describa los signos y síntomas presentes..."><?php echo htmlspecialchars($registro['signos_sintomas']); ?></textarea>
                </div>

                <!-- Riesgos -->
                <div class="mb-3">
                    <label for="riesgos" class="form-label">
                        <strong>Factores de Riesgo</strong>
                    </label>
                    <textarea class="form-control" id="riesgos" name="riesgos" 
                              rows="2" placeholder="Describa los factores de riesgo identificados..."><?php echo htmlspecialchars($registro['riesgos']); ?></textarea>
                </div>

                <!-- Motivo Última Visita -->
                <div class="mb-3">
                    <label for="motivo_ultima_visita" class="form-label">
                        <strong>Motivo de la Última Visita</strong>
                    </label>
                    <textarea class="form-control" id="motivo_ultima_visita" name="motivo_ultima_visita" 
                              rows="2" placeholder="Describa el motivo de la última visita médica..."><?php echo htmlspecialchars($registro['motivo_ultima_visita']); ?></textarea>
                </div>

                <!-- Última Visita Médica -->
                <div class="mb-4">
                    <label for="ultima_visita_medica" class="form-label">
                        <strong>Fecha de Última Visita Médica</strong>
                    </label>
                    <input type="date" class="form-control" id="ultima_visita_medica" name="ultima_visita_medica" 
                           value="<?php echo $registro['ultima_visita_medica'] ? date('Y-m-d', strtotime($registro['ultima_visita_medica'])) : ''; ?>">
                    <div class="form-text">Dejar vacío si no aplica</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-warning text-white me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Registro
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
        const motivoConsulta = document.getElementById('motivo_consulta').value.trim();
        
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