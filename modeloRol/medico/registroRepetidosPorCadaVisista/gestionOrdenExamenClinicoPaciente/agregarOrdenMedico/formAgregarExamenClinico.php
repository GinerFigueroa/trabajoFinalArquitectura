<?php
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/OrdenExamenDAO.php'); 

class formAgregarExamenClinico extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new OrdenExamenDAO();
    }

    public function formAgregarExamenClinicoShow()
    {
        $this->cabeceraShow("Nueva Orden de Examen");

        // Obtener el ID del médico desde la sesión
        $idUsuarioMedico = $_SESSION['id_usuario'] ?? null;
        
        if (!$idUsuarioMedico) {
            include_once('../../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("No se pudo identificar al médico. Por favor, inicie sesión nuevamente.", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        // Obtener datos para los selects (solo historias clínicas)
        $historiasClinicas = $this->objDAO->obtenerHistoriasClinicas();
        
        // Obtener información del médico para mostrar
        $medico = $this->objDAO->obtenerMedicoPorIdUsuario($idUsuarioMedico);
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-plus-circle me-2"></i>Nueva Orden de Examen</h4>
            <p class="mb-0">Médico: <strong><?php echo htmlspecialchars($medico['nombre_completo'] ?? 'Médico'); ?></strong></p>
        </div>
        <div class="card-body">
            <form action="./getAgregarExamenClinico.php" method="POST">
                
                <!-- Campo oculto con el ID del médico -->
                <input type="hidden" name="id_medico" value="<?php echo htmlspecialchars($idUsuarioMedico); ?>">

                <!-- Selección de Paciente (Historia Clínica) -->
                <div class="mb-3">
                    <label for="historia_clinica_id" class="form-label">Paciente *</label>
                    <select class="form-select" id="historia_clinica_id" name="historia_clinica_id" required>
                        <option value="">-- Seleccione un paciente --</option>
                        <?php foreach ($historiasClinicas as $hc): ?>
                            <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>">
                                <?php echo htmlspecialchars("HC-{$hc['historia_clinica_id']} - {$hc['nombre_paciente']} (DNI: {$hc['dni']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Información del Médico (solo lectura) -->
                <div class="mb-3">
                    <label class="form-label">Médico Solicitante</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($medico['nombre_completo'] ?? 'Médico'); ?>" readonly>
                    <div class="form-text">Usted es el médico que solicita el examen</div>
                </div>

                <!-- Fecha del Examen -->
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha del Examen *</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <!-- Tipo de Examen -->
                <div class="mb-3">
                    <label for="tipo_examen" class="form-label">Tipo de Examen *</label>
                    <input type="text" class="form-control" id="tipo_examen" name="tipo_examen" 
                           placeholder="Ej: Hemograma, Radiografía, Ecografía..." required>
                </div>

                <!-- Indicaciones -->
                <div class="mb-3">
                    <label for="indicaciones" class="form-label">Indicaciones</label>
                    <textarea class="form-control" id="indicaciones" name="indicaciones" 
                              rows="4" placeholder="Describa las indicaciones para el examen..."></textarea>
                </div>

                <!-- Estado (oculto, por defecto Pendiente) -->
                <input type="hidden" name="estado" value="Pendiente">

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Guardar Orden
                    </button>
                    <a href="../indexOrdenExamenClinico.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const historiaClinica = document.getElementById('historia_clinica_id').value;
        const tipoExamen = document.getElementById('tipo_examen').value.trim();
        const fecha = document.getElementById('fecha').value;

        if (!historiaClinica) {
            e.preventDefault();
            alert('Debe seleccionar un paciente.');
            document.getElementById('historia_clinica_id').focus();
            return;
        }

        if (!tipoExamen) {
            e.preventDefault();
            alert('El tipo de examen es obligatorio.');
            document.getElementById('tipo_examen').focus();
            return;
        }

        if (!fecha) {
            e.preventDefault();
            alert('La fecha es obligatoria.');
            document.getElementById('fecha').focus();
            return;
        }

        // Validar que la fecha no sea futura
        const fechaSeleccionada = new Date(fecha);
        const hoy = new Date();
        if (fechaSeleccionada > hoy) {
            e.preventDefault();
            alert('La fecha no puede ser futura.');
            document.getElementById('fecha').focus();
        }
    });

    // Limitar fecha máxima a hoy
    document.getElementById('fecha').max = new Date().toISOString().split('T')[0];
</script>

<?php
        $this->pieShow();
    }
}
?>