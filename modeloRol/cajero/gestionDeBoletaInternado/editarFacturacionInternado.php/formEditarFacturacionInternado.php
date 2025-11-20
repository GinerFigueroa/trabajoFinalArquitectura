<?php
// Archivo: formEditarFacturacionInternado.php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/FacturacionInternadoDAO.php'); 

class formEditarFacturacionInternado extends pantalla
{
    public function formEditarFacturacionInternadoShow()
    {
        $idFactura = isset($_GET['id']) ? (int)$_GET['id'] : null;

        $objFacturaDAO = new FacturacionInternadoDAO();
        $objAuxiliarDAO = new FacturacionInternadoAuxiliarDAO();

        $factura = $objFacturaDAO->obtenerFacturaInternadoPorId($idFactura);
        $estadosFactura = $objAuxiliarDAO::obtenerEstadosFactura();
        $infoInternado = $factura ? $objAuxiliarDAO->obtenerInfoInternado($factura['id_internado']) : null;
        
        $urlVolver = "../indexFacturacionInternado.php";

        if (!$factura || !$infoInternado) {
            $this->cabeceraShow("Error");
            echo '<div class="alert alert-danger container mt-4" role="alert">Factura o información de Internado no encontrada.</div>';
            echo '<div class="container"><a href="' . $urlVolver . '" class="btn btn-secondary">Volver</a></div>';
            $this->pieShow();
            return;
        }
        
        $this->cabeceraShow("Editar Factura de Internado");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Factura de Internado #<?php echo htmlspecialchars($idFactura); ?></h4>
            <small>Paciente: **<?php echo htmlspecialchars($infoInternado['nombre_paciente']); ?>** (ID Internado: <?php echo htmlspecialchars($factura['id_internado']); ?>)</small>
        </div>
        <div class="card-body">
            <form action="./getEditarFactruacionInternado.php" method="POST">
                <input type="hidden" name="id_factura" value="<?php echo htmlspecialchars($idFactura); ?>">
                <input type="hidden" name="id_internado" value="<?php echo htmlspecialchars($factura['id_internado']); ?>">
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="fecha_emision" class="form-label">Fecha de Emisión (*):</label>
                        <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" required value="<?php echo htmlspecialchars($factura['fecha_emision']); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="dias_internado" class="form-label">Días Internado (*):</label>
                        <input type="number" class="form-control" id="dias_internado" name="dias_internado" required min="1" value="<?php echo htmlspecialchars($factura['dias_internado']); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="costo_habitacion" class="form-label">Costo Habitación (*):</label>
                        <input type="number" step="0.01" class="form-control" id="costo_habitacion" name="costo_habitacion" required min="0" value="<?php echo htmlspecialchars($factura['costo_habitacion']); ?>" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="costo_tratamientos" class="form-label">Costo Tratamientos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_tratamientos" name="costo_tratamientos" value="<?php echo htmlspecialchars($factura['costo_tratamientos']); ?>" min="0" oninput="calcularTotal()">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="costo_medicamentos" class="form-label">Costo Medicamentos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_medicamentos" name="costo_medicamentos" value="<?php echo htmlspecialchars($factura['costo_medicamentos']); ?>" min="0" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="costo_otros" class="form-label">Otros Costos:</label>
                        <input type="number" step="0.01" class="form-control" id="costo_otros" name="costo_otros" value="<?php echo htmlspecialchars($factura['costo_otros']); ?>" min="0" oninput="calcularTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado (*):</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <?php foreach ($estadosFactura as $estado) { ?>
                                <option value="<?php echo htmlspecialchars($estado); ?>"
                                    <?php echo ($factura['estado'] == $estado ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($estado); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="alert alert-success text-center mt-3">
                    Monto Total a Facturar: <strong>$<span id="total_factura"><?php echo number_format($factura['total'], 2); ?></span></strong>
                    <input type="hidden" name="total" id="total_hidden" value="<?php echo htmlspecialchars($factura['total']); ?>">
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

<script>
    function calcularTotal() {
        const costoHabitacion = parseFloat(document.getElementById('costo_habitacion').value) || 0;
        const costoTratamientos = parseFloat(document.getElementById('costo_tratamientos').value) || 0;
        const costoMedicamentos = parseFloat(document.getElementById('costo_medicamentos').value) || 0;
        const costoOtros = parseFloat(document.getElementById('costo_otros').value) || 0;
        
        const total = costoHabitacion + costoTratamientos + costoMedicamentos + costoOtros;
        
        document.getElementById('total_factura').textContent = total.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);
    }
    
    // Ejecutar al cargar para asegurar que el total visible coincida con el hidden
    document.addEventListener('DOMContentLoaded', calcularTotal);
</script>

<?php
        $this->pieShow();
    }
}
?>