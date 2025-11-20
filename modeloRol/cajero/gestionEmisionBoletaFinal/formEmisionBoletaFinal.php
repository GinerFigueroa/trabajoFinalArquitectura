<?php
include_once("../../../shared/pantalla.php");
include_once("../../../modelo/BoletaDAO.php"); 
class formEmisionBoletaFinal extends pantalla
{
    public function formEmisionBoletaFinalShow()
    {
        $objBoletaDAO = new BoletaDAO(); 
        $listaBoletas = $objBoletaDAO->obtenerTodasLasBoletas();
        
        $this->cabeceraShow("Gestión de Comprobantes de Pago");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Boletas y Facturas Emitidas</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarEmicionBoleta/indexAgregarEmisionBoleta.php" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus me-2"></i>Generar Nueva boleta
                    </a>
                </div>
            </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Boleta</th>
                            <th>N° Comprobante</th>
                            <th>Tipo</th>
                            <th>Paciente</th>
                            <th>Concepto (Orden #)</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Fecha Emisión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaBoletas) > 0) {
                            foreach ($listaBoletas as $boleta) { 
                                $badgeType = ($boleta['tipo'] == 'Factura') ? 'primary' : 'secondary';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($boleta['id_boleta']); ?></td>
                                    <td><?php echo htmlspecialchars($boleta['numero_boleta']); ?></td>
                                    <td><span class="badge bg-<?php echo $badgeType; ?>"><?php echo htmlspecialchars($boleta['tipo']); ?></span></td>
                                    <td><?php echo htmlspecialchars($boleta['nombre_paciente'] . ' ' . $boleta['apellido_paciente']); ?></td>
                                    <td><?php echo htmlspecialchars($boleta['concepto']); ?> (<?php echo htmlspecialchars($boleta['id_orden']); ?>)</td>
                                    <td>$<?php echo number_format(htmlspecialchars($boleta['monto_total']), 2); ?></td>
                                    <td><?php echo htmlspecialchars($boleta['metodo_pago']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($boleta['fecha_emision'])); ?></td>
                                    <td>
                                        <a href="./emicionDeBoletaPDF/indexEmicionBoletaPDF.php?id_boleta=<?php echo htmlspecialchars($boleta['id_boleta']); ?>" 
                                           class="btn btn-sm btn-info" title="Emitir Boleta" target="_blank">
                                            <i class="bi bi-file-pdf-fill"></i>
                                        </a>
                                        
                                        <a href="./emirtirPrefactura/indexEmitirPrefactura.php?id_boleta=<?php echo htmlspecialchars($boleta['id_boleta']); ?>" 
                                            class="btn btn-sm btn-info" title="Emitir Prefactura PDF" target="_blank">
                                                <i class="bi bi-file-earmark-text-fill me-2"></i>
                                            </a>

                                           <a href="./editarEmisionBoleta/indexEditarEmisionBoleta.php?id=<?php echo htmlspecialchars($boleta['id_boleta']); ?>" 
                                           class="btn btn-sm btn-warning" title="Editar Boleta">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <button class="btn btn-sm btn-danger" title="Eliminar Boleta (Anular)" 
                                                onclick="confirmarEliminar(<?php echo htmlspecialchars($boleta['id_boleta']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="9" class="text-center">No hay boletas o facturas registradas.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(idBoleta) {
        if (confirm('¿Está seguro de que desea ELIMINAR/ANULAR esta boleta? Esto revertirá la Orden de Pago a estado "Pendiente".')) {
            window.location.href = `./getEmisionBoleta.php?action=eliminar&id=${idBoleta}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>