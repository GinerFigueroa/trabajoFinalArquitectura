<?php
// Archivo: formTotalCitas.php
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/CitasDAO.php'); 

/**
 * Clase CitaIterator (PATRÓN: ITERATOR) 
 * Definida aquí para que esté disponible sin necesidad de incluirla por separado.
 */
class CitaIterator implements Iterator {
    private $citas;
    private $position = 0;

    public function __construct(array $citas) { $this->citas = $citas; }
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->citas[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->citas[$this->position]); }
}


class formTotalCitas extends pantalla // PATRÓN: TEMPLATE METHOD
{
    /**
     * PATRÓN: VISITOR (Lógica de presentación/formato para el estado)
     */
    private function acceptVisitor($cita)
    {
        $estado = $cita['estado'];
        $clase = 'light';
        
        switch ($estado) {
            case 'Confirmada': $clase = 'success'; break;
            case 'Pendiente': $clase = 'warning'; break;
            case 'Completada': $clase = 'primary'; break;
            case 'Cancelada': $clase = 'danger'; break;
            case 'No asistió': $clase = 'secondary'; break;
        }
        return ['clase' => $clase, 'texto' => $estado];
    }

    public function formTotalCitasShow()
    {
        $this->cabeceraShow("Gestión de Citas Médicas");

       $objCita = new CitasDAO();
        
        // 1. Obtener el Array de Citas del DAO
        $listaCitasArray = $objCita->obtenerTodasCitas();
        
        // 2. PATRÓN ITERATOR: Encapsular el Array en el Iterator
        $citasIterator = new CitaIterator($listaCitasArray);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white text-center">
            <h4><i class="bi bi-calendar-check me-2"></i>Agenda de Citas</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarNuevasCitas/indexAgregarNuevaCita.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Programar Nueva Cita
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
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Tratamiento</th>
                            <th>Fecha y Hora</th>
                            <th>Duración (min)</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // 🚀 USANDO EL ITERATOR
                        if ($citasIterator->valid()) {
                            foreach ($citasIterator as $cita) { 
                                // 🎨 USANDO EL VISITOR para obtener el formato del estado
                                $estadoVisitado = $this->acceptVisitor($cita);
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cita['id_cita']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['nombre_paciente']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['nombre_medico']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['nombre_tratamiento']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha_hora'])); ?></td>
                                    <td><?php echo htmlspecialchars($cita['duracion']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $estadoVisitado['clase']; ?>">
                                            <?php echo htmlspecialchars($estadoVisitado['texto']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="./editarCitas/indexEditarCitas.php?id=<?php echo htmlspecialchars($cita['id_cita']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($cita['id_cita']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay citas programadas.</td>
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
        if (confirm('¿Está seguro de que desea eliminar esta cita? Esta acción es irreversible.')) {
            // Se asume que getCitas.php es el dispatcher correcto
            window.location.href = `./getCitas.php?action=eliminar&id=${id}`; 
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>