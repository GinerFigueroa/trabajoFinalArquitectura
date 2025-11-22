<?php

include_once('../../../../shared/pantalla.php');
include_once("../../../../modelo/HistoriaClinicaDAO.php"); 

class formEditarHistorialPaciente extends pantalla
{
    public function formEditarHistorialPacienteShow()
    {
        // Verificar sesión
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: ../../../../vista/login.php");
            exit();
        }
        
        $idHistoriaClinica = $_GET['id'] ?? null;

        if (!$idHistoriaClinica) {
            echo '<div class="alert alert-danger" role="alert">Error: ID de Historia Clínica no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objHistoriaDAO = new HistoriaClinicaDAO();
        $historia = $objHistoriaDAO->obtenerHistoriaPorId($idHistoriaClinica);

        if (!$historia) {
            echo '<div class="alert alert-danger" role="alert">Error: Historia Clínica no encontrada.</div>';
            $this->pieShow();
            return;
        }

        // Obtener lista de personal tratante para el dropdown
        $personalTratante = $objHistoriaDAO->obtenerPersonalTratante();

        $this->cabeceraShow('Editar Historia Clínica');
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Historia Clínica</h4>
        </div>
        <div class="card-body">
            <form action="./getEditarHistorialPaciente.php" method="POST">
                <input type="hidden" name="historia_clinica_id" value="<?php echo htmlspecialchars($historia['historia_clinica_id']); ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paciente" class="form-label">Paciente:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($historia['nombre_paciente']); ?>" disabled>
                        <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($historia['id_paciente']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dr_tratante_id" class="form-label">Personal Tratante (*):</label>
                        <select class="form-select" id="dr_tratante_id" name="dr_tratante_id" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($personalTratante as $personal) { 
                                $selected = ($personal['id_usuario'] == $historia['dr_tratante_id']) ? 'selected' : '';
                                $nombreCompleto = htmlspecialchars($personal['nombre_completo']);
                                $rol = ($personal['id_rol'] == 2) ? ' (Médico)' : ' (Enfermero)';
                            ?>
                                <option value="<?php echo $personal['id_usuario']; ?>" <?php echo $selected; ?>>
                                    <?php echo $nombreCompleto . $rol; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_creacion" class="form-label">Fecha de Creación (*):</label>
                        <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" 
                               value="<?php echo htmlspecialchars($historia['fecha_creacion']); ?>" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexHistoriaClinica.php" class="btn btn-secondary">Cancelar</a>
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