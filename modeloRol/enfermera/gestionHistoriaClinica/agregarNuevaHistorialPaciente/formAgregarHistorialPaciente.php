<?php
include_once('../../../../shared/pantalla.php');
include_once("../../../../modelo/HistoriaClinicaDAO.php"); 

class formAgregarHistorialPaciente extends pantalla
{
    public function formAgregarHistorialPacienteShow()
    {
        // Verifica que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
             // Redirigir al login si no hay sesión
             header("Location: ../../../../vista/login.php");
             exit();
        }
        
        $idPersonalAsignado = $_SESSION['id_usuario']; 
        
        $objHistoriaDAO = new HistoriaClinicaDAO();
        $pacientesSinHistoria = $objHistoriaDAO->obtenerPacientesSinHistoriaAsignada();
        
        $this->cabeceraShow('Crear Nueva Historia Clínica');
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-medical me-2"></i>Crear Nueva Historia Clínica (Asignación)</h4>
            <p class="mb-0">Personal asignado (dr_tratante_id): **<?php echo htmlspecialchars($idPersonalAsignado); ?>**</p>
        </div>
        <div class="card-body">
            <form action="./getAgregarHistoriaPaciente.php" method="POST">
                
                <input type="hidden" name="dr_tratante_id" value="<?php echo htmlspecialchars($idPersonalAsignado); ?>">
                <input type="hidden" name="fecha_creacion" value="<?php echo date('Y-m-d'); ?>">
                
                <div class="mb-3">
                    <label for="id_paciente" class="form-label">Paciente (*):</label>
                    <select class="form-select" id="id_paciente" name="id_paciente" required>
                        <option value="">-- Seleccione un Paciente sin Historia Asignada --</option>
                        
                        <?php if (empty($pacientesSinHistoria)) { ?>
                            <option disabled>No hay pacientes sin historia clínica pendiente.</option>
                        <?php } ?>
                        
                        <?php foreach ($pacientesSinHistoria as $paciente) { ?>
                            <option value="<?php echo htmlspecialchars($paciente['id_paciente']); ?>">
                                <?php echo htmlspecialchars($paciente['nombre_completo']); ?> 
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Crear HC y Asignar</button>
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