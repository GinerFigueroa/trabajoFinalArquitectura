<?php
// Archivo: formEditarEmisionBoleta.php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/BoletaDAO.php'); 
include_once('../../../../shared/mensajeSistema.php'); 

class formEditarEmisionBoleta extends pantalla
{
    public function formEditarEmisionBoletaShow() 
    {
        // 1. Obtener y validar el ID directamente desde el GET
        $idBoleta = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $urlVolver = "../indexEmisionBoletaFinal.php"; 
        $objMensaje = new mensajeSistema();
        
        if (!$idBoleta) {
            $objMensaje->mensajeSistemaShow("ID de Boleta/Factura no proporcionado o inválido.", $urlVolver, "error");
            return;
        }

        // 2. Cargar datos necesarios
        $objBoletaDAO = new BoletaDAO();
        $objAuxiliarDAO = new BoletaAuxiliarDAO();

        $boleta = $objBoletaDAO->obtenerBoletaPorId($idBoleta);
        
        $tiposBoleta = $objAuxiliarDAO::obtenerTiposBoleta();
        $metodosPago = $objAuxiliarDAO::obtenerMetodosPago();
        
        if (!$boleta) {
            $objMensaje->mensajeSistemaShow("Boleta/Factura no encontrada.", $urlVolver, "error");
            return;
        }
        
        $this->cabeceraShow("Editar Comprobante de Pago");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Comprobante #<?php echo htmlspecialchars($idBoleta); ?> (Orden #<?php echo htmlspecialchars($boleta['id_orden']); ?>)</h4>
        </div>
        <div class="card-body">
            <form action="./getEmisionBoleta.php" method="POST">
                <input type="hidden" name="id_boleta" value="<?php echo htmlspecialchars($idBoleta); ?>">
                
                <div class="alert alert-info">
                    Solo puede editar los detalles del comprobante; el **ID de Orden** asociado no es modificable.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Comprobante (*):</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <?php foreach ($tiposBoleta as $tipo) { ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>"
                                    <?php echo ($boleta['tipo'] == $tipo ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="numero_boleta" class="form-label">N° de Comprobante (*):</label>
                        <input type="text" class="form-control" id="numero_boleta" name="numero_boleta" required maxlength="20" value="<?php echo htmlspecialchars($boleta['numero_boleta']); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto_total" class="form-label">Monto Final Cobrado (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto_total" name="monto_total" required min="0.01" value="<?php echo htmlspecialchars($boleta['monto_total']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="metodo_pago" class="form-label">Método de Pago (*):</label>
                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                            <option value="" disabled>Seleccione</option>
                            <?php foreach ($metodosPago as $metodo) { ?>
                                <option value="<?php echo htmlspecialchars($metodo); ?>"
                                    <?php echo ($boleta['metodo_pago'] == $metodo ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($metodo); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-warning me-md-2"><i class="bi bi-save me-1"></i>Guardar Cambios</button>
                    <a href="<?php echo $urlVolver; ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
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