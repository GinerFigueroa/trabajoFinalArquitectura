<?php
// C:\...\gestionTipoDeTratamientoCosto\formTratamiento.php
include_once("../../../shared/pantalla.php"); 
include_once("../../../modelo/TratamientoDAO.php"); // Incluye TratamientoDAO y EntidadDAO (para lookups)

/**
 * Clase TratamientoIterator (ITERATOR)
 */
if (!class_exists('TratamientoIterator')) {
    class TratamientoIterator implements Iterator {
        private $items; private $position = 0;
        public function __construct(array $items) { $this->items = $items; }
        public function rewind(): void { $this->position = 0; }
        public function current(): mixed { return $this->items[$this->position]; }
        public function key(): mixed { return $this->position; }
        public function next(): void { ++$this->position; }
        public function valid(): bool { return isset($this->items[$this->position]); }
    }
}

class formTratamiento extends pantalla // TEMPLATE METHOD
{
    public function formTratamientoShow()
    {
        $this->cabeceraShow("Gestión de Tipos de Tratamiento");

        $objTratamientoDAO = new TratamientoDAO(); 
        $listaTratamientos = $objTratamientoDAO->obtenerTodosTratamientos();
        
        $tratamientoIterator = new TratamientoIterator($listaTratamientos); // Uso del ITERATOR
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white text-center">
            <h4><i class="bi bi-gear-fill me-2"></i>Catálogo de Tratamientos y Costos</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarTratamiento/indexAgregarTratamiento.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Tratamiento
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th><th>Tratamiento</th><th>Especialidad</th>
                            <th>Duración (min.)</th><th>Costo</th><th>Estado</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($tratamientoIterator->valid()) {
                            foreach ($tratamientoIterator as $tratamiento) { 
                                $activo = $tratamiento['activo'] == 1;
                                $claseEstado = $activo ? 'bg-success' : 'bg-danger';
                                $textoEstado = $activo ? 'Activo' : 'Inactivo';
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tratamiento['id_tratamiento']); ?></td>
                                    <td><?php echo htmlspecialchars($tratamiento['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($tratamiento['nombre_especialidad'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($tratamiento['duracion_estimada']); ?></td>
                                    <td>S/ <?php echo number_format(htmlspecialchars($tratamiento['costo']), 2); ?></td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $claseEstado; ?>">
                                            <?php echo $textoEstado; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="./editarTratamiento/indexEditarTratamiento.php?id=<?php echo htmlspecialchars($tratamiento['id_tratamiento']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($tratamiento['id_tratamiento']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="7" class="text-center">No hay tratamientos registrados en el sistema.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        if (confirm('¿Está seguro de que desea ELIMINAR permanentemente este tratamiento?')) {
            window.location.href = `./getTratamiento.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>