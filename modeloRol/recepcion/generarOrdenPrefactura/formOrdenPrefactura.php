<?php

include_once('../../../shared/pantalla.php');
// El archivo incluido ahora contiene la clase OrdenPagoDAO
include_once('../../../modelo/ordenPagoDAO.php');

class formOrdenPrefactura extends pantalla
{
    public function formOrdenPrefacturaShow()
    {
        $this->cabeceraShow("Gestión de Órdenes de Prefactura");

        // CORRECCIÓN: Se cambió OrdenPago a OrdenPagoDAO
        $objOrden = new OrdenPagoDAO();
        $listaOrdenes = $objOrden->obtenerTodasOrdenes();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-earmark-text-fill me-2"></i>Lista de Órdenes de Prefactura</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarOrdenPreFactura/indexAgregarOrdenPreFactura.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nueva Orden
                    </a>
                    <a href="../gestionTotalPacientes/indexTotalPaciente.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver 
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (DNI)</th>
                            <th>Concepto</th>
                            <th>Monto Estimado</th>
                            <th>F. Emisión</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaOrdenes) > 0) {
                            foreach ($listaOrdenes as $orden) { 
                                $esPendiente = ($orden['estado'] == 'Pendiente');
                                ?>
                                <tr class="<?php echo $esPendiente ? 'table-warning' : 'table-light'; ?>">
                                    <td><?php echo htmlspecialchars($orden['id_orden']); ?></td>
                                    <td><?php echo htmlspecialchars($orden['nombre_paciente'] . ' (' . $orden['dni_paciente'] . ')'); ?></td>
                                    <td><?php echo htmlspecialchars($orden['concepto']); ?></td>
                                    <td><?php echo 'S/ ' . number_format($orden['monto_estimado'], 2); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($orden['fecha_emision'])); ?></td>
                                    <td><?php echo htmlspecialchars($orden['tipo_servicio']); ?></td>
                                    <td><span class="badge bg-<?php echo $this->obtenerClaseEstado($orden['estado']); ?>"><?php echo htmlspecialchars($orden['estado']); ?></span></td>
                                    <td>
                                        <?php if ($esPendiente) { ?>
                                            <a href="./editarOrdenPrefactura/indexEditarOrdenPreFactura.php?id=<?php echo htmlspecialchars($orden['id_orden']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($orden['id_orden']); ?>)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php } ?>
                                        
                                        <a href="./emitirPreFactura/indexOndenPDF.php?id=<?php echo htmlspecialchars($orden['id_orden']); ?>" target="_blank" class="btn btn-sm btn-info" title="Generar PDF (Prefactura)">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay órdenes de prefactura registradas.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        // Usamos un modal o un mensaje personalizado en lugar de confirm(), ya que no se permiten
        // Por ahora, mantengo la estructura de window.location.href para la acción.
        // NOTA: Si este entorno prohíbe 'confirm', deberías reemplazar esto con un modal.
        if (confirm('¿Está seguro de que desea eliminar esta orden de pago? Solo se permite eliminar órdenes con estado Pendiente.')) {
            window.location.href = `./getOrdenPrefactura.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }

    private function obtenerClaseEstado($estado)
    {
        switch ($estado) {
            case 'Pendiente': return 'warning';
            case 'Facturada': return 'success';
            case 'Anulada': return 'danger';
            default: return 'secondary';
        }
    }
}
?>