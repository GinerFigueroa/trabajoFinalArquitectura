<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/ConsentimientoInformadoDAO.php');

class formEditarConsentimientoInformado extends pantalla
{
    public function formEditarConsentimientoInformadoShow()
    {
        $this->cabeceraShow('Editar Consentimiento Informado');

        $idConsentimiento = $_GET['id'] ?? null;

        if (!$idConsentimiento) {
            echo '<div class="alert alert-danger" role="alert">ID de Consentimiento no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objDAO = new ConsentimientoInformadoDAO();
        $consentimiento = $objDAO->obtenerConsentimientoPorId($idConsentimiento);

        if (!$consentimiento) {
            echo '<div class="alert alert-danger" role="alert">Consentimiento Informado no encontrado.</div>';
            $this->pieShow();
            return;
        }
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Consentimiento N° <?php echo htmlspecialchars($consentimiento['consentimiento_id']); ?></h4>
        </div>
        <div class="card-body">
            <form action="./getEditarConsentimientoInformado.php" method="POST">
                <input type="hidden" name="idConsentimiento" value="<?php echo htmlspecialchars($consentimiento['consentimiento_id']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Paciente (HC):</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($consentimiento['nombre_paciente_completo'] . ' (HC: ' . $consentimiento['historia_clinica_id'] . ')'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dr. Tratante:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($consentimiento['nombre_medico_completo']); ?>" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="diagnostico" class="form-label">Diagnóstico / Motivo (*):</label>
                    <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3" required><?php echo htmlspecialchars($consentimiento['diagnostico_descripcion']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Procedimiento / Tratamiento Informado (*):</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5" required><?php echo htmlspecialchars($consentimiento['tratamiento_descripcion']); ?></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexConsentimientoInformado.php" class="btn btn-secondary">Volver al Listado</a>
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