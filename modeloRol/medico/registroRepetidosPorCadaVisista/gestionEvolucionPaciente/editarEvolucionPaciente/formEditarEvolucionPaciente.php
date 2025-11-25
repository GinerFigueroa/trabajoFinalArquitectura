<?php

include_once("../../../../../shared/pantalla.php");
include_once("../../../../../modelo/EvolucionPacienteDAO.php");
include_once("../../../../../shared/mensajeSistema.php");

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Hereda de la clase 'pantalla' para definir el esqueleto de la vista.
 */
class formEditarEvolucionPaciente extends pantalla
{
    // Atributo: $objDAO (Receptor para la carga GET de datos)
    private $objDAO;

    // Método: Constructor
    public function __construct() {
        $this->objDAO = new EvolucionPacienteDAO();
    }

    // Método: formEditarEvolucionPacienteShow (Método del Template: Esqueleto de la página)
    public function formEditarEvolucionPacienteShow()
    {
        // TEMPLATE METHOD: Paso 1 - Cabecera
        $this->cabeceraShow("Editar Evolución Médica");

        // Obtener ID de evolución desde GET
        $idEvolucion = isset($_GET['evo_id']) ? (int)$_GET['evo_id'] : null;
        // Atributo: $objMensaje
        $objMensaje = new mensajeSistema();
        $rutaListado = "../indexEvolucionPaciente.php";

        if (!$idEvolucion) {
            $objMensaje->mensajeSistemaShow("ID de evolución no proporcionado.", $rutaListado, "error");
            return;
        }

        // Obtener datos de la evolución
        // Método: obtenerEvolucionPorId
        $evolucion = $this->objDAO->obtenerEvolucionPorId($idEvolucion);
        
        if (!$evolucion) {
            $objMensaje->mensajeSistemaShow("Evolución médica no encontrada.", $rutaListado, "error");
            return;
        }

        // Obtener información de la historia clínica para mostrar
        // Método: obtenerHistoriaPorId
        $historiaClinica = $this->objDAO->obtenerHistoriaPorId($evolucion['historia_clinica_id']);
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Evolución Médica</h4>
            <?php if ($historiaClinica): ?>
                <p class="mb-0">Paciente: <strong><?php echo htmlspecialchars($historiaClinica['nombre_paciente']); ?></strong> | 
                HC ID: <strong><?php echo htmlspecialchars($evolucion['historia_clinica_id']); ?></strong></p>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form action="./getEditarEvolucionPaciente.php" method="POST">
                <input type="hidden" name="id_evolucion" value="<?php echo htmlspecialchars($evolucion['id_evolucion']); ?>">
                <input type="hidden" name="historia_clinica_id" value="<?php echo htmlspecialchars($evolucion['historia_clinica_id']); ?>">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>Fecha de evolución:</strong> <?php echo date('d/m/Y H:i', strtotime($evolucion['fecha_evolucion'])); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>ID Evolución:</strong> <?php echo htmlspecialchars($evolucion['id_evolucion']); ?></small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nota_subjetiva" class="form-label">
                        <strong>S - Nota Subjetiva *</strong>
                        <small class="text-muted">(Síntomas o quejas referidas por el paciente)</small>
                    </label>
                    <textarea class="form-control" id="nota_subjetiva" name="nota_subjetiva" 
                                  rows="4" placeholder="Describa los síntomas que reporta el paciente..." required><?php echo htmlspecialchars($evolucion['nota_subjetiva']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="nota_objetiva" class="form-label">
                        <strong>O - Nota Objetiva</strong>
                        <small class="text-muted">(Hallazgos del examen físico o resultados de pruebas)</small>
                    </label>
                    <textarea class="form-control" id="nota_objetiva" name="nota_objetiva" 
                                  rows="4" placeholder="Registre los hallazgos objetivos encontrados..."><?php echo htmlspecialchars($evolucion['nota_objetiva']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="analisis" class="form-label">
                        <strong>A - Análisis</strong>
                        <small class="text-muted">(Evaluación y diagnóstico del médico)</small>
                    </label>
                    <textarea class="form-control" id="analisis" name="analisis" 
                                  rows="4" placeholder="Realice el análisis y evaluación del caso..."><?php echo htmlspecialchars($evolucion['analisis']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="plan_de_accion" class="form-label">
                        <strong>P - Plan de Acción</strong>
                        <small class="text-muted">(Tratamiento, medicamentos, interconsultas solicitadas)</small>
                    </label>
                    <textarea class="form-control" id="plan_de_accion" name="plan_de_accion" 
                                  rows="4" placeholder="Describa el plan de tratamiento y acciones a seguir..."><?php echo htmlspecialchars($evolucion['plan_de_accion']); ?></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" name="btnActualizar" class="btn btn-warning text-white me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Evolución
                    </button>
                    <a href="../indexEvolucionPaciente.php" class="btn btn-secondary">
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
        const notaSubjetiva = document.getElementById('nota_subjetiva').value.trim();
        
        if (!notaSubjetiva) {
            e.preventDefault();
            alert('La nota subjetiva (S) es obligatoria.');
            document.getElementById('nota_subjetiva').focus();
        }
    });
</script>

<?php
        // TEMPLATE METHOD: Paso 2 - Pie
        $this->pieShow();
    }
}
?>