<?php
// Archivo: formFacturacionInternado.php

include_once("../../../shared/pantalla.php");
include_once("../../../modelo/FacturacionInternadoDAO.php"); 

class formFacturacionInternado extends pantalla
{
    public function formFacturacionInternadoShow()
    {
        $objFacturaDAO = new FacturacionInternadoDAO(); 
        $listaFacturas = $objFacturaDAO->obtenerTodasLasFacturasInternado();
        
        $this->cabeceraShow("Gestión de Facturación de Internados");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="bi bi-hospital-fill me-2"></i>Facturas Emitidas por Internado</h4>
        </div>
        
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarFacturacionInternado/indexAgregarFactruraInternado.php" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus me-2"></i>Generar Nueva Factura de Internado
                    </a>
                    
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Factura</th>
                            <th>ID Internado</th>
                            <th>Paciente</th>
                            <th>Días</th>
                            <th>Fecha Emisión</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaFacturas) > 0) {
                            foreach ($listaFacturas as $factura) { 
                                $badgeColor = match ($factura['estado']) {
                                    'Pagado' => 'success',
                                    'Pendiente' => 'warning',
                                    'Anulado' => 'danger',
                                    default => 'secondary',
                                };
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($factura['id_factura']); ?></td>
                                    <td><?php echo htmlspecialchars($factura['id_internado'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($factura['nombre_paciente'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($factura['dias_internado']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($factura['fecha_emision'])); ?></td>
                                    <td>$<?php echo number_format(htmlspecialchars($factura['total']), 2); ?></td>
                                    <td><span class="badge bg-<?php echo $badgeColor; ?>"><?php echo htmlspecialchars($factura['estado']); ?></span></td>
                                    <td>
                                        <a href="./editarFacturacionInternado.php/indexEditarFacturacionInternado.php?id=<?php echo htmlspecialchars($factura['id_factura']); ?>" 
                                           class="btn btn-sm btn-warning" title="Editar Factura">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <a href="./generarEmicionBoletaInternado/indexEmitirBoletaInternado.php?id_factura=<?php echo htmlspecialchars($factura['id_factura']); ?>" 
                                           class="btn btn-sm btn-info" title="Emitir Boleta" target="_blank">
                                            <i class="bi bi-file-pdf-fill"></i>
                                        </a>
                                        <a href="./generarFacturacionInternadoPDF/indexFacturacionPdf.php?id_factura=<?php echo htmlspecialchars($factura['id_factura']); ?>" 
                                        class="btn btn-sm btn-info" title="Generar Prefactura " target="_blank">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </a>


                                        

                                        <button class="btn btn-sm btn-danger" title="Eliminar Factura" 
                                                onclick="confirmarEliminar(<?php echo htmlspecialchars($factura['id_factura']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="8" class="text-center">No hay facturas de internado registradas.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(idFactura) {
        if (confirm('¿Está seguro de que desea eliminar esta factura de internado?')) {
            window.location.href = `./getFacturacionInternado.php?action=eliminar&id=${idFactura}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>