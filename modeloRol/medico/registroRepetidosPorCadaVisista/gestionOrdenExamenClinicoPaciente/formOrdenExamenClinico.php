<?php
include_once("../../../../shared/pantalla.php");
include_once("../../../../modelo/OrdenExamenDAO.php"); // Cambié el nombre del DAO

class formOrdenExamenClinico extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new OrdenExamenDAO; // Inicializar el DAO correcto
    }

    public function formOrdenExamenClinicoShow()
    {
        $this->cabeceraShow("Gestión de Órdenes de Examen");

        try {
            // Obtener todas las órdenes directamente del DAO
            $listaOrdenes = $this->objDAO->obtenerTodasOrdenes();
            
            // Manejo de mensajes
            $msg = $_GET['success'] ?? ($_GET['error'] ?? null);
            $tipoMsg = isset($_GET['success']) ? 'success' : (isset($_GET['error']) ? 'error' : null);
?>
<!-- El resto del código HTML permanece igual -->
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-clipboard2-pulse me-2"></i>Órdenes de Examen Clínico</h4>
        </div>
        <div class="card-body">
            
            <?php if ($msg): ?>
                <div class="alert alert-<?php echo $tipoMsg == 'success' ? 'success' : 'danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarOrdenMedico/indexAgregarExamenClinico.php" class="btn btn-success">
                        <i class="bi bi-plus-lg me-2"></i>Nueva Orden
                    </a>
                    <a href="../gestionHistorialMedico/indexHistorialMedico.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Orden</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Tipo Examen</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaOrdenes) > 0): ?>
                            <?php foreach ($listaOrdenes as $orden): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($orden['id_orden']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($orden['fecha'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($orden['nombre_paciente']); ?></strong><br>
                                        <small class="text-muted">HC-<?php echo htmlspecialchars($orden['historia_clinica_id']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($orden['nombre_medico']); ?></td>
                                    <td><?php echo htmlspecialchars($orden['tipo_examen']); ?></td>
                                    <td>
                                        <?php 
                                        $estadoClass = [
                                            'Pendiente' => 'warning',
                                            'Realizado' => 'success', 
                                            'Entregado' => 'info'
                                        ];
                                        $clase = $estadoClass[$orden['estado']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $clase; ?>">
                                            <?php echo htmlspecialchars($orden['estado']); ?>
                                        </span>
                                    </td>
                                   <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="./editarOrdenMedico/indexEditarExamenClinico.php?id_orden=<?php echo htmlspecialchars($orden['id_orden']); ?>" 
                                                class="btn btn-warning text-white" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <!-- BOTÓN PARA GENERAR PDF -->
                                                <a href="./generarOrdenExamenClinicoPDF/indexOrdenExamenClincioPDF.php?id=<?php echo htmlspecialchars($orden['id_orden']); ?>" 
                                                target="_blank" class="btn btn-info" title="Generar PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                <button onclick="confirmarEliminar(<?php echo htmlspecialchars($orden['id_orden']); ?>)" 
                                                        class="btn btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                             </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No hay órdenes de examen registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(idOrden) {
        if (confirm('¿Está seguro de que desea ELIMINAR esta orden de examen?\n\nEsta acción no se puede deshacer.')) {
            window.location.href = `getOrdenExamenClinico.php?action=eliminar&id_orden=${idOrden}`;
        }
    }
</script>

<?php
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        $this->pieShow();
    }
}
?>