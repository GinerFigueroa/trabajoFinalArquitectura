<?php
// C:\...\agregarEvolucionPaciente\formEvolucionPaciente.php

include_once("../../../../../shared/pantalla.php"); 
include_once("../../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../../shared/mensajeSistema.php");

class formEvolucionPaciente extends pantalla
{
    public function formEvolucionPacienteShow() 
    {
        $objMensaje = new mensajeSistema();
        
        // Obtener ID del Médico de la Sesión
        $idMedico = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0; 
        
        // ✅ Obtener pacientes CON historia clínica asignada
        //obtenerHistoriasClinicas();
        $objHistoriaDAO = new RegistroMedicoDAO();
        $pacientesConHistoria = $objHistoriaDAO->obtenerPacientesConHistoriaAsignada();

        // Si no hay médico logueado, redirigir
        if ($idMedico == 0) {
            $objMensaje->mensajeSistemaShow(
                "Debe iniciar sesión para registrar una evolución.", 
                "../../../../../vista/login.php", 
                "error"
            );
            exit();
        }

        $this->cabeceraShow("Registrar Nota de Evolución (SOAP)");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-journal-medical me-2"></i>Registrar Nota de Evolución (SOAP)</h4>
            <p class="mb-0">Médico ID: <?php echo htmlspecialchars($idMedico); ?></p>
        </div>
        <div class="card-body">
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                </div>
            <?php endif; ?>
            
            <form action="./getEvolucionPaciente.php" method="POST">
                
                <input type="hidden" name="id_medico" value="<?php echo htmlspecialchars($idMedico); ?>">
                
                <div class="mb-3">
                    <label for="id_paciente" class="form-label text-primary fw-bold">Paciente con Historia Clínica (*):</label>
                    <select class="form-select" id="id_paciente" name="historia_clinica_id" required 
                        <?php echo empty($pacientesConHistoria) ? 'disabled' : ''; ?>>
                        <option value="">-- Seleccione un Paciente --</option>
                        
                        <?php if (empty($pacientesConHistoria)): ?>
                            <option disabled>No hay pacientes con historia clínica registrada.</option>
                        <?php else: ?>
                            <?php foreach ($pacientesConHistoria as $paciente): ?>
                                <option value="<?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>">
                                    <?php echo htmlspecialchars($paciente['nombre_completo']); ?> 
                                    (HC ID: <?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($pacientesConHistoria)): ?>
                        <small class="form-text text-muted">No se encontraron pacientes con historia clínica.</small>
                    <?php endif; ?>
                </div>

                <p class="text-muted fst-italic">Complete las secciones de la nota SOAP (* Campos Requeridos)</p>

                <div class="mb-3">
                    <label for="nota_subjetiva" class="form-label text-primary fw-bold">S: Nota Subjetiva (*)</label>
                    <textarea class="form-control" id="nota_subjetiva" name="nota_subjetiva" rows="4" placeholder="Síntomas, quejas referidas por el paciente, evolución desde la última consulta." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="nota_objetiva" class="form-label text-success fw-bold">O: Nota Objetiva (*)</label>
                    <textarea class="form-control" id="nota_objetiva" name="nota_objetiva" rows="4" placeholder="Hallazgos del examen físico, resultados de laboratorio o imágenes." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="analisis" class="form-label text-danger fw-bold">A: Análisis y Evaluación</label>
                    <textarea class="form-control" id="analisis" name="analisis" rows="3" placeholder="Diagnóstico diferencial, impresión diagnóstica, evaluación de la respuesta al tratamiento."></textarea>
                </div>

                <div class="mb-3">
                    <label for="plan_de_accion" class="form-label text-info fw-bold">P: Plan de Acción (*)</label>
                    <textarea class="form-control" id="plan_de_accion" name="plan_de_accion" rows="4" placeholder="Tratamiento, medicamentos, estudios adicionales solicitados, interconsultas, citas de seguimiento." required></textarea>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="action" value="registrar" class="btn btn-primary btn-lg"
                        <?php echo empty($pacientesConHistoria) ? 'disabled' : ''; ?>>
                        <i class="bi bi-save me-2"></i>Registrar Evolución
                    </button>
                    <a href="../indexEvolucionPaciente.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
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