<?php
// Fichero: gestionHistoriaClinica/editarHistorialPaciente/formEditarHistorialPaciente.php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/HistoriaClinicaDAO.php'); 
class formEditarHistorialPaciente extends pantalla
{
    public function formEditarHistorialPacienteShow($historiaClinicaId)
    {
        $objControl = new controlEditarHistorialPaciente();
        $objDAO = new HistoriaClinicaDAO();
        
        // Obtener datos actuales de la HC
        $hc = $objControl->obtenerHistoriaParaEdicion($historiaClinicaId);
        
        // Obtener la lista de usuarios elegibles para ser tratantes
        $personalTratante = $objDAO->obtenerPersonalTratante();
        
        if (!$hc) {
            $this->cabeceraShow('Error');
            echo '<div class="alert alert-danger">Historia Clínica no encontrada.</div>';
            $this->pieShow();
            return;
        }
        
        $nombrePaciente = $hc['nombre_paciente'] ?? 'N/A';
        
        $this->cabeceraShow('Editar Historia Clínica N° ' . htmlspecialchars($hc['historia_clinica_id']));
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Edición de Metadatos de HC</h4>
            <p class="mb-0">Paciente: **<?php echo htmlspecialchars($nombrePaciente); ?>**</p>
        </div>
        <div class="card-body">
            <form action="./getEditarHistorialPaciente.php" method="POST">
                
                <input type="hidden" name="historia_clinica_id" value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>">
                <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($hc['id_paciente']); ?>">
                
                <div class="mb-3">
                    <label for="dr_tratante_id" class="form-label">Dr/Enfermero Tratante Actual:</label>
                    
                    <select class="form-select" id="dr_tratante_id" name="dr_tratante_id" required>
                        <option value="">-- Seleccione un nuevo Tratante --</option>
                        
                        <?php foreach ($personalTratante as $personal) { ?>
                            <?php $selected = ($personal['id_usuario'] == $hc['dr_tratante_id']) ? 'selected' : ''; ?>
                            <option value="<?php echo htmlspecialchars($personal['id_usuario']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($personal['nombre_completo']); ?> (ID: <?php echo htmlspecialchars($personal['id_usuario']); ?>)
                            </option>
                        <?php } ?>
                    </select>
                    <small class="form-text text-muted">El tratante actual es: **<?php echo htmlspecialchars($hc['nombre_tratante']); ?>**</small>
                </div>

                <div class="mb-3">
                    <label for="fecha_creacion" class="form-label">Fecha de Creación (Metadato):</label>
                    <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" 
                           value="<?php echo htmlspecialchars($hc['fecha_creacion']); ?>" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat me-2"></i>Actualizar HC</button>
                    <a href="../indexHistoriaClinica.php" class="btn btn-secondary">Volver al Listado</a>
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