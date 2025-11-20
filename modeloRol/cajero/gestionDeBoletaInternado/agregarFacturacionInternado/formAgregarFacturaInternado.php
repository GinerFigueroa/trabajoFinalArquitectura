<?php
// Archivo: formAgregarFacturaInternado.php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/FacturacionInternadoDAO.php'); 

class formAgregarFacturaInternado extends pantalla
{
    public function formAgregarFacturaInternadoShow()
    {
        $objAuxiliarDAO = new FacturacionInternadoAuxiliarDAO();
        $listaInternados = $objAuxiliarDAO->obtenerInternadosParaFacturar();
        $estadosFactura = $objAuxiliarDAO::obtenerEstadosFactura();

        $urlVolver = "../../indexFacturacionInternado.php";

        $this->cabeceraShow("Generar Nueva Factura de Internado");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-cash me-2"></i>Generar Factura de Estancia Hospitalaria</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarFacturaInternado.php" method="POST">
                
                <div class="mb-3">
                    <label for="id_internado" class="form-label">Internado a Facturar (*):</label>
                    <select class="form-select" id="id_internado" name="id_internado" required onchange="actualizarDias()">
                        <option value="" disabled selected>Seleccione un Internado</option>
                        <?php if (count($listaInternados) == 0): ?>
                            <option value="" disabled>No hay internados disponibles para facturar.</option>
                        <?php endif; ?>

                        <?php foreach ($listaInternados as $internado) { ?>
                            <option value="<?php echo htmlspecialchars($internado['id_internado']); ?>" 
                                    data-dias="<?php echo htmlspecialchars($internado['dias_estadia']); ?>">
                               <?php 
// CORRECCIÓN APLICADA: Usar 'fecha_alta' en lugar de 'fecha_egreso'
$fecha_salida = $internado['fecha_alta'] ?? 'ACTIVO';
echo "Internado #{$internado['id_internado']} - {$internado['nombre_paciente']} ({$internado['fecha_ingreso']} a " . ($fecha_salida == 'ACTIVO' ? 'HOY' : $fecha_salida) . ")";
?>

                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="fecha_emision" class="form-label">Fecha de Emisión (*):</label>
                        <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="dias_internado" class="form-label">Días Internado (*):</label>
                        <input type="number" class="form-control" id="dias_internado" name="dias_internado" required min="1" readonly title="Se calcula automáticamente al seleccionar el internado">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="costo_habitacion" class="form-label">Costo Habitación (*):</label>
                        <input type="number" step="0.01" class="form-control" id="costo_habitacion" name="costo_habitacion" required min="0" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="costo_tratamientos" class="form-label">Costo Tratamientos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_tratamientos" name="costo_tratamientos" value="0.00" min="0" oninput="calcularTotal()">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="costo_medicamentos" class="form-label">Costo Medicamentos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_medicamentos" name="costo_medicamentos" value="0.00" min="0" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="costo_otros" class="form-label">Otros Costos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_otros" name="costo_otros" value="0.00" min="0" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado Inicial (*):</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <?php foreach ($estadosFactura as $estado) { ?>
                                <option value="<?php echo htmlspecialchars($estado); ?>"
                                    <?php echo ($estado == 'Pendiente' ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($estado); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="alert alert-success text-center mt-3">
                    Monto Total a Facturar: <strong>$<span id="total_factura">0.00</span></strong>
                    <input type="hidden" name="total" id="total_hidden" value="0.00">
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary me-md-2" id="btn-submit" disabled>
                        <i class="bi bi-file-earmark-check me-1"></i>Emitir Factura
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
    function calcularTotal() {
        const costoHabitacion = parseFloat(document.getElementById('costo_habitacion').value) || 0;
        const costoTratamientos = parseFloat(document.getElementById('costo_tratamientos').value) || 0;
        const costoMedicamentos = parseFloat(document.getElementById('costo_medicamentos').value) || 0;
        const costoOtros = parseFloat(document.getElementById('costo_otros').value) || 0;
        
        const total = costoHabitacion + costoTratamientos + costoMedicamentos + costoOtros;
        
        document.getElementById('total_factura').textContent = total.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);

        // Habilitar/Deshabilitar el botón de submit
        const idInternado = document.getElementById('id_internado').value;
        document.getElementById('btn-submit').disabled = !idInternado || total <= 0;
    }

    function actualizarDias() {
        const select = document.getElementById('id_internado');
        const selectedOption = select.options[select.selectedIndex];
        const dias = selectedOption.getAttribute('data-dias');

        document.getElementById('dias_internado').value = dias;
        calcularTotal(); // Recalcula el total y verifica la habilitación del botón
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', () => {
        actualizarDias();
    });
</script>

<?php
        $this->pieShow();
    }
}
?>