<?php
// formEvolucionPaciente.php - LISTADO GENERAL (CORREGIDO)

include_once("../../../../shared/pantalla.php");
include_once("../../../../modelo/EvolucionPacienteDAO.php"); 
include_once("../../../../shared/mensajeSistema.php");

class formEvolucionPaciente extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new EvolucionPacienteDAO(); 
    }

    public function formEvolucionPacienteShow()
    {
        $this->cabeceraShow("Gestión de Evoluciones Médicas");

        // Obtener TODAS las evoluciones
        $listaEvoluciones = $this->objDAO->obtenerTodasEvoluciones();
        
        // Manejo de mensajes
        $msg = $_GET['success'] ?? ($_GET['error'] ?? null);
        $tipoMsg = isset($_GET['success']) ? 'success' : (isset($_GET['error']) ? 'error' : null);
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white text-center">
            <h4><i class="bi bi-activity me-2"></i>Listado General de Evoluciones Médicas</h4>
        </div>
        <div class="card-body">
            
            <?php 
            if ($msg) {
                $alertClass = ($tipoMsg == 'success') ? 'alert-success' : 'alert-danger';
                echo "<div class='alert {$alertClass}' role='alert'>" . htmlspecialchars($msg) . "</div>";
            }
            ?>

            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarEvolucionPaciente/indexEvolucionPaciente.php" class="btn btn-success">
                        <i class="bi bi-plus-lg me-2"></i>Nueva Evolución
                    </a>
                    <a href="../gestionHistorialMedico/indexHistorialMedico.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a HM
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Evolución</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Nota Subjetiva (S)</th>
                            <th>HC ID</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaEvoluciones) > 0) {
                            foreach ($listaEvoluciones as $evo) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($evo['id_evolucion']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($evo['fecha_evolucion'])); ?></td>
                                    <td><?php echo htmlspecialchars($evo['nombre_paciente']); ?></td>
                                    <td><?php echo htmlspecialchars($evo['nombre_medico']); ?></td>
                                    <td>
                                        <?php 
                                        $notaSubjetiva = htmlspecialchars($evo['nota_subjetiva'] ?? '');
                                        echo strlen($notaSubjetiva) > 50 ? substr($notaSubjetiva, 0, 50) . '...' : $notaSubjetiva;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">HC-<?php echo htmlspecialchars($evo['historia_clinica_id']); ?></span>
                                    </td>
                                    <td>
                                        <!-- ✅ ENLACE CORREGIDO - Pasar evo_id -->
                                        <a href="./editarEvolucionPaciente/indexEvolucionPaciente.php?evo_id=<?php echo htmlspecialchars($evo['id_evolucion']); ?>" 
                                           class="btn btn-sm btn-warning text-white" title="Editar evolución">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>

                                        <button onclick="confirmarEliminarEvolucion(<?php echo htmlspecialchars($evo['id_evolucion']); ?>)" 
                                                class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No hay evoluciones médicas registradas.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminarEvolucion(idEvolucion) {
        if (confirm('¿Está seguro de que desea ELIMINAR esta evolución médica?\n\nEsta acción es irreversible.')) {
            window.location.href = `getEvolucionPaciente.php?action=eliminar&evo_id=${idEvolucion}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>