<?php
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/ConsentimientoInformadoDAO.php');

class formConsentimientoInformado extends pantalla
{
    public function formConsentimientoInformadoShow()
    {
        $this->cabeceraShow("Gestión de Consentimientos Informados");

        $objDAO = new ConsentimientoInformadoDAO();
        $listaConsentimientos = $objDAO->obtenerTodosConsentimientos();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-earmark-medical-fill me-2"></i>Lista de Consentimientos Informados</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./registrarConsentimientoInformado/indexRegistrarConsetimientoInfirmado.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Consentimiento
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (HC)</th>
                            <th>Diagnóstico</th>
                            <th>Dr. Tratante</th>
                            <th>F. Firma</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaConsentimientos) > 0) {
                            foreach ($listaConsentimientos as $c) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['consentimiento_id']); ?></td>
                                    <td><?php echo htmlspecialchars($c['nombre_paciente_hc']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($c['diagnostico_descripcion'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($c['nombre_medico']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($c['fecha_firma'])); ?></td>
                                    <td>
                                        <a href="./editarConsentimientoInformado.php/indexEditarConsentimientoInformado.php?id=<?php echo htmlspecialchars($c['consentimiento_id']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="./gerarConsentimientoInformadoPDF/indexConsentimientoInformadoPDF.php?id=<?php echo htmlspecialchars($c['consentimiento_id']); ?>" target="_blank" class="btn btn-sm btn-info" title="Generar PDF">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($c['consentimiento_id']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay consentimientos informados registrados.</td>
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
        if (confirm('¿Está seguro de que desea eliminar este Consentimiento Informado?')) {
            window.location.href = `./getConsentimientoInformado.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>