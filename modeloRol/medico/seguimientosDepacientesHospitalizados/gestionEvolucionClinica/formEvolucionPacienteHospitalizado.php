<?php
include_once("../../../../shared/pantalla.php");
include_once("../../../../modelo/InternadoSeguimientoDAO.php"); 

class formEvolucionPacienteHospitalizado extends pantalla
{
    public function formEvolucionPacienteHospitalizadoShow()
    {
        $this->cabeceraShow("Gestión de Evolución Clínica");

        $objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $listaSeguimientos = $objSeguimientoDAO->obtenerTodosSeguimientos();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-heart-pulse-fill me-2"></i>Seguimiento Clínico de Pacientes Hospitalizados</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarEvolucionPacienteHospitalizado/indexaAgregarEvolucionPaciente.php" class="btn btn-success">
                        <i class="bi bi-journal-plus me-2"></i>Registrar Nueva Evolución
                    </a>
                    <a href="../misPacientesInternados/indexGestionInternados.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver Ec
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Seguimiento</th>
                            <th>Paciente</th>
                            <th>Fecha</th>
                            <th>Enfermera</th>
                            <th>Evolución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaSeguimientos) > 0) {
                            foreach ($listaSeguimientos as $seguimiento) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?></td>
                                    <td><?php echo htmlspecialchars($seguimiento['nombre_paciente'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime(htmlspecialchars($seguimiento['fecha']))); ?></td>
                                    <td><?php echo htmlspecialchars($seguimiento['nombre_enfermera'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($seguimiento['evolucion'], 0, 50) . (strlen($seguimiento['evolucion']) > 50 ? '...' : '')); ?></td>
                                    <td>
                                        <a href="./editarEvolucionPacienteHospitalizado/indexEditarPacienteHospitazado.php?id=<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay evoluciones clínicas registradas.</td>
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
        if (confirm('¿Está seguro de que desea eliminar este registro de evolución? Esta acción es irreversible.')) {
            window.location.href = `./getEvolucionPacienteHopitalizado.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>