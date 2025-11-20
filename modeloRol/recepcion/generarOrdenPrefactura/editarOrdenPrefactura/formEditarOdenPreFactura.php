<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/OrdenPagoDAO.php');

class formEditarOdenPreFactura extends pantalla
{
    public function formEditarOdenPreFacturaShow()
    {
        $this->cabeceraShow('Editar Orden de Prefactura');

        $idOrden = $_GET['id'] ?? null;

        if (!$idOrden) {
            echo '<div class="alert alert-danger" role="alert">ID de Orden de Pago no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objOrden = new OrdenPago();
        $orden = $objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            echo '<div class="alert alert-danger" role="alert">Orden de Pago no encontrada.</div>';
            $this->pieShow();
            return;
        }

        $esEditable = ($orden['estado'] == 'Pendiente');
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Orden de Prefactura N° <?php echo htmlspecialchars($orden['id_orden']); ?></h4>
        </div>
        <div class="card-body">
            <?php if (!$esEditable) { ?>
                <div class="alert alert-info text-center">
                    Esta orden se encuentra en estado **<?php echo htmlspecialchars($orden['estado']); ?>** y **no puede ser editada**.
                </div>
            <?php } ?>
            
            <form action="./getEditarOrdenPreFactura.php" method="POST">
                <input type="hidden" name="idOrden" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paciente" class="form-label">Paciente:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['nombre_paciente_completo'] . ' (DNI: ' . $orden['dni_paciente'] . ')'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado Actual:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['estado']); ?>" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idCita" class="form-label">ID Cita:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_cita'] ?? 'N/A'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idInternado" class="form-label">ID Internamiento:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_internado'] ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required <?php echo $esEditable ? '' : 'disabled'; ?>><?php echo htmlspecialchars($orden['concepto']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="<?php echo htmlspecialchars($orden['monto_estimado']); ?>" required min="0.01" <?php echo $esEditable ? '' : 'disabled'; ?>>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <?php if ($esEditable) { ?>
                        <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <?php } ?>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Volver al Listado</a>
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