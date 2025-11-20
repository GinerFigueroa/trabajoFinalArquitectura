<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/BoletaDAO.php'); 

class formAgregarEmisionBoleta extends pantalla
{
    public function formAgregarEmisionBoletaShow() // El parámetro ya no se usa, pero se mantiene la firma
    {
        $objAuxiliarDAO = new BoletaAuxiliarDAO();
        
        // 1. Obtener listado de todas las órdenes pendientes para la selección
        $ordenesPendientes = $objAuxiliarDAO->obtenerOrdenesPendientes(); 
        
        $tiposBoleta = $objAuxiliarDAO::obtenerTiposBoleta();
        $metodosPago = $objAuxiliarDAO::obtenerMetodosPago();

        $urlVolver = "../indexEmisionBoletaFinal.php"; // Volver al listado de Boletas emitidas

        $this->cabeceraShow("Emitir Nueva Boleta/Factura");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-receipt me-2"></i>Generar Comprobante de Pago a partir de Orden Pendiente</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarEmisionBoleta.php" method="POST">
                
                <div class="mb-3">
                    <label for="id_orden" class="form-label">Orden de Pago a Facturar (*):</label>
                    <select class="form-select" id="id_orden" name="id_orden" required>
                        <option value="" disabled selected>Seleccione una Orden Pendiente</option>
                        <?php if (count($ordenesPendientes) == 0): ?>
                            <option value="" disabled>No hay órdenes pendientes para facturar.</option>
                        <?php endif; ?>
                        
                        <?php foreach ($ordenesPendientes as $orden) { ?>
                            <option 
                                value="<?php echo htmlspecialchars($orden['id_orden']); ?>"
                                data-monto="<?php echo htmlspecialchars($orden['monto_estimado']); ?>"
                            >
                                <?php echo "Orden #{$orden['id_orden']} - {$orden['nombre_paciente']} ({$orden['concepto']})"; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Comprobante (*):</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <?php foreach ($tiposBoleta as $tipo) { ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo ($tipo == 'Boleta' ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="numero_boleta" class="form-label">N° de Comprobante (*):</label>
                        <input type="text" class="form-control" id="numero_boleta" name="numero_boleta" required maxlength="20">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto_total" class="form-label">Monto Final Cobrado (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto_total" name="monto_total" required min="0.01" value="0.00">
                        <small class="text-muted" id="monto_hint">Monto Sugerido: $0.00</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="metodo_pago" class="form-label">Método de Pago (*):</label>
                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                            <option value="" disabled selected>Seleccione</option>
                            <?php foreach ($metodosPago as $metodo) { ?>
                                <option value="<?php echo htmlspecialchars($metodo); ?>"><?php echo htmlspecialchars($metodo); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-success me-md-2" id="btn-submit" disabled>
                        <i class="bi bi-file-earmark-check me-1"></i>Emitir y Registrar
                    </button>
                    <a href="<?php echo $urlVolver; ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectOrden = document.getElementById('id_orden');
        const inputMonto = document.getElementById('monto_total');
        const hintMonto = document.getElementById('monto_hint');
        const btnSubmit = document.getElementById('btn-submit');
        
        function actualizarFormulario() {
            const selectedOption = selectOrden.options[selectOrden.selectedIndex];
            const montoSugerido = selectedOption.dataset.monto;
            
            if (montoSugerido) {
                // Actualizar el monto sugerido y el campo de input
                hintMonto.textContent = `Monto Sugerido: $${parseFloat(montoSugerido).toFixed(2)}`;
                inputMonto.value = parseFloat(montoSugerido).toFixed(2);
                btnSubmit.disabled = false; // Habilitar el botón si hay una orden seleccionada
            } else {
                // Estado inicial o sin selección válida
                hintMonto.textContent = `Monto Sugerido: $0.00`;
                inputMonto.value = '0.00';
                btnSubmit.disabled = true;
            }
        }
        
        selectOrden.addEventListener('change', actualizarFormulario);
        
        // Inicializar el formulario si ya hay una selección (aunque en este caso siempre comienza deseleccionado)
        actualizarFormulario();
    });
</script>

<?php
        $this->pieShow();
    }
}
?>