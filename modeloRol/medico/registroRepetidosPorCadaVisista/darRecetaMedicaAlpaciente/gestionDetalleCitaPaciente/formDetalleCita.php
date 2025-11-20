<?php
session_start();
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/RecetaDetalleDAO.php');

class formDetalleCita extends pantalla
{
    public function formDetalleCitaShow()
    {
        $this->cabeceraShow("Gestión de Detalles de Receta Médica");

        // Verificar que el usuario sea médico
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            include_once('../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Acceso denegado. Solo el personal médico puede gestionar detalles de recetas.', 
                '../../../index.php', 
                'error'
            );
            exit();
        }

        $objDetalle = new RecetaDetalleDAO();
        $listaDetalles = $objDetalle->obtenerTodosDetalles();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="bi bi-capsule-pill me-2"></i>
                        Detalles de Recetas Médicas
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="./agregarCitaDetalle/indexAgregarDetalleCita.php" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Detalle
                    </a>
                    <a href="../gestionOrdenRecetaMedica/indexRecetaMedica.php" class="btn btn-secondary">

                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar medicamento...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="filterReceta">
                        <option value="">Todas las recetas</option>
                        <!-- Opciones dinámicas de recetas -->
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                    </button>
                </div>
            </div>

            <!-- Tabla de detalles -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="detallesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Receta</th>
                            <th>Paciente</th>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Duración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaDetalles) > 0) {
                            foreach ($listaDetalles as $detalle) { 
                                $puedeEditar = $this->puedeEditarDetalle($detalle['id_receta']);
                                ?>
                                <tr data-receta="<?php echo htmlspecialchars($detalle['id_receta']); ?>">
                                    <td class="fw-bold"><?php echo htmlspecialchars($detalle['id_detalle']); ?></td>
                                    <td>
                                        <span class="badge bg-info">#<?php echo htmlspecialchars($detalle['id_receta']); ?></span>
                                        <br>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($detalle['fecha_receta'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($detalle['nombre_paciente']); ?></strong>
                                        <br>
                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($detalle['dni']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($detalle['medicamento']); ?></strong>
                                        <?php if (!empty($detalle['notas'])): ?>
                                            <br>
                                            <small class="text-muted" title="<?php echo htmlspecialchars($detalle['notas']); ?>">
                                                <i class="bi bi-info-circle"></i> Notas
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($detalle['dosis']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['frecuencia']); ?></td>
                                    <td>
                                        <?php if (!empty($detalle['duracion'])): ?>
                                            <span class="badge bg-warning"><?php echo htmlspecialchars($detalle['duracion']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No espec.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($puedeEditar): ?>
                                                <a href="./editarCitaDetalle/indexEditarDetalleCita.php?id=<?php echo htmlspecialchars($detalle['id_detalle']); ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <button class="btn btn-danger" 
                                                        title="Eliminar" 
                                                        onclick="confirmarEliminar(<?php echo htmlspecialchars($detalle['id_detalle']); ?>)">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Solo lectura</span>
                                            <?php endif; ?>
                                            <a href="./generaCitaMedicaPDF/indexCitaMedicaPDF.php?id=<?php echo htmlspecialchars($detalle['id_receta']); ?>" 
                                               target="_blank" class="btn btn-info" title="Ver Receta Completa">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No hay detalles de recetas registrados.
                                        <br>
                                        <a href="./agregarCitaDetalle/indexAgregarDetalleCita.php" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar el primer detalle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Estadísticas -->
            <?php if (count($listaDetalles) > 0): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <strong><?php echo count($listaDetalles); ?></strong>
                                    <br>
                                    <small class="text-muted">Total Detalles</small>
                                </div>
                                <div class="col-md-3">
                                    <strong><?php echo count(array_unique(array_column($listaDetalles, 'id_receta'))); ?></strong>
                                    <br>
                                    <small class="text-muted">Recetas</small>
                                </div>
                                <div class="col-md-3">
                                    <strong><?php echo count(array_unique(array_column($listaDetalles, 'medicamento'))); ?></strong>
                                    <br>
                                    <small class="text-muted">Medicamentos Únicos</small>
                                </div>
                                <div class="col-md-3">
                                    <strong><?php echo count(array_unique(array_column($listaDetalles, 'nombre_paciente'))); ?></strong>
                                    <br>
                                    <small class="text-muted">Pacientes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de que desea eliminar este detalle de receta?\nEsta acción no se puede deshacer.')) {
        window.location.href = `./getDetalleCita.php?action=eliminar&id=${id}`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterReceta').value = '';
    filterTable();
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const filterReceta = document.getElementById('filterReceta').value;
    const rows = document.querySelectorAll('#detallesTable tbody tr');
    
    rows.forEach(row => {
        const medicamento = row.cells[3].textContent.toLowerCase();
        const recetaId = row.getAttribute('data-receta');
        const showBySearch = medicamento.includes(searchText);
        const showByReceta = filterReceta === '' || recetaId === filterReceta;
        
        row.style.display = (showBySearch && showByReceta) ? '' : 'none';
    });
}

// Inicializar filtros
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('filterReceta').addEventListener('change', filterTable);
    
    // Cargar opciones de recetas para el filtro
    const recetas = <?php echo json_encode(array_unique(array_column($listaDetalles, 'id_receta'))); ?>;
    const select = document.getElementById('filterReceta');
    recetas.forEach(recetaId => {
        const option = document.createElement('option');
        option.value = recetaId;
        option.textContent = `Receta #${recetaId}`;
        select.appendChild(option);
    });
});
</script>

<style>
.table th {
    background-color: #2c3e50;
    color: white;
    font-weight: 600;
}
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php
        $this->pieShow();
    }

    private function puedeEditarDetalle($idReceta)
    {
        $objDetalle = new RecetaDetalleDAO();
        $idUsuario = $_SESSION['id_usuario'];
        $idUsuarioReceta = $objDetalle->obtenerIdUsuarioPorIdReceta($idReceta);
        
        return $idUsuarioReceta == $idUsuario;
    }
}
?>