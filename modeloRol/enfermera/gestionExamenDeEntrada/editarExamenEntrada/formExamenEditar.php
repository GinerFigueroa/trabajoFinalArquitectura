<?php
// C:\...\editarExamenEntrada\formExamenEditar.php
include_once('../../../../shared/pantalla.php');
include_once("../../../../modelo/ExamenClinicoDAO.php");

class formExamenEditar extends pantalla
{
    public function formExamenEditarShow()
    {
        $this->cabeceraShow('Editar Examen Clínico');

        // Obtener el ID del examen a editar
        $examenId = $_GET['id'] ?? null;

        if (!$examenId) {
            echo '<div class="alert alert-danger" role="alert">Error: ID de Examen no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objExamenDAO = new ExamenClinicoDAO();
        $examen = $objExamenDAO->obtenerExamenPorId($examenId);

        if (!$examen) {
            echo '<div class="alert alert-danger" role="alert">Error: Examen no encontrado.</div>';
            $this->pieShow();
            return;
        }

        // Obtener listas para los dropdowns
        $historias = $objExamenDAO->obtenerHistoriasClinicasConNombrePaciente();
        $enfermeros = $objExamenDAO->obtenerEnfermerosActivos();

?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Examen Clínico de Entrada</h4>
        </div>
        <div class="card-body">
            <form action="./getExamenEditar.php" method="POST">
                <input type="hidden" name="examen_id" value="<?php echo htmlspecialchars($examen['examen_id']); ?>">
                
                <div class="mb-3">
                    <label for="historia_clinica_id" class="form-label">Paciente / Historia Clínica:</label>
                    <select class="form-select" id="historia_clinica_id" name="historia_clinica_id" required>
                        <option value="">-- Seleccione el Paciente (Historia Clínica) --</option>
                        <?php foreach ($historias as $hc) { 
                            $selected = ($hc['historia_clinica_id'] == $examen['historia_clinica_id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars("HC: {$hc['historia_clinica_id']} - Paciente: {$hc['nombre_completo']}"); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="peso" class="form-label">Peso (kg):</label>
                        <input type="number" step="0.01" class="form-control" id="peso" name="peso" 
                               value="<?php echo htmlspecialchars($examen['peso']); ?>" required min="0.1" max="500">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="talla" class="form-label">Talla (m):</label>
                        <input type="number" step="0.01" class="form-control" id="talla" name="talla" 
                               value="<?php echo htmlspecialchars($examen['talla']); ?>" required min="0.1" max="3.0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pulso" class="form-label">Pulso:</label>
                        <input type="text" class="form-control" id="pulso" name="pulso" 
                               value="<?php echo htmlspecialchars($examen['pulso']); ?>" required maxlength="20">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_enfermero" class="form-label">Enfermero que registra (Opcional):</label>
                    <select class="form-select" id="id_enfermero" name="id_enfermero">
                        <option value="">-- Seleccione una Enfermera/o --</option>
                        <?php foreach ($enfermeros as $enfermero) { 
                            $selected = ($enfermero['id_usuario'] == $examen['id_enfermero']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($enfermero['id_usuario']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars("{$enfermero['nombre']} {$enfermero['apellido_paterno']}"); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-2"></i>Guardar Cambios</button>
                    <a href="../indexExamenEntrada.php" class="btn btn-secondary">Cancelar</a>
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