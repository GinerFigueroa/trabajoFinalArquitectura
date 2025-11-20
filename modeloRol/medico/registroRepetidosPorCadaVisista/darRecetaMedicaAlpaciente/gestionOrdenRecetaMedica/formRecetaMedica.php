<?php
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/RecetaMedicaDAO.php');

class formRecetaMedica extends pantalla
{
    public function formRecetaMedicaShow()
    {
        $this->cabeceraShow("Gestión de Recetas Médicas");

        $objReceta = new RecetaMedicaDAO();
        $listaRecetas = $objReceta->obtenerTodasRecetas();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-prescription2 me-2"></i>Lista de Recetas Médicas</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarRecetaMedica/indexAgregarRecetaMedica.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nueva Receta
                    </a>
                      <a href="../gestionDetalleCitaPaciente/indexDetalleCita.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>DETALLE DE NUEVA RECETA
                    </a>
    
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (DNI)</th>
                            <th>Médico</th>
                            <th>Fecha</th>
                            <th>Indicaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaRecetas) > 0) {
                            foreach ($listaRecetas as $receta) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($receta['id_receta']); ?></td>
                                    <td><?php echo htmlspecialchars($receta['nombre_paciente'] . ' (' . $receta['dni'] . ')'); ?></td>
                                    <td><?php echo htmlspecialchars($receta['nombre_medico']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($receta['fecha'])); ?></td>
                                    <td><?php echo strlen($receta['indicaciones_generales']) > 50 ? 
                                        substr(htmlspecialchars($receta['indicaciones_generales']), 0, 50) . '...' : 
                                        htmlspecialchars($receta['indicaciones_generales']); ?></td>
                                    <td>
                                        <a href="./editarRecetaMedica/indexEditarRecetaMedica.php?id=<?php echo htmlspecialchars($receta['id_receta']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($receta['id_receta']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                       
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay recetas médicas registradas.</td>
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
        if (confirm('¿Está seguro de que desea eliminar esta receta médica?')) {
            window.location.href = `./getRecetaMedica.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>