
<?php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/CitasDAO.php'); // Contiene o accede a EntidadesDAO

/**
 * Clase AuxiliarIterator (PATRÓN: ITERATOR) 
 * Implementa la interfaz Iterator para permitir el recorrido explícito de las colecciones de datos.
 */
class AuxiliarIterator implements Iterator {
    private $data = [];
    private $position = 0;
    
    public function __construct(array $array) { $this->data = $array; }
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->data[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->data[$this->position]); }
}


class formAgregarNuevaCita extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // El PATRÓN STATE se implementa en la vista a través del campo 'estado'.

    public function formAgregarNuevaCitaShow()
    {
        // 1. TEMPLATE METHOD: Paso de la cabecera
        $this->cabeceraShow('Programar Nueva Cita');

        $objEntidad = new EntidadesDAO();
        
        // Obtención de colecciones
        $pacientesArray = $objEntidad->obtenerPacientesDisponibles();
        $tratamientosArray = $objEntidad->obtenerTratamientosActivos();
        $medicosArray = $objEntidad->obtenerMedicosDisponibles();

        // 2. ITERATOR: Creación de iteradores para cada colección
        $pacientesIterator = new AuxiliarIterator($pacientesArray);
        $tratamientosIterator = new AuxiliarIterator($tratamientosArray);
        $medicosIterator = new AuxiliarIterator($medicosArray);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-calendar-plus me-2"></i>Programar Nueva Cita</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarNuevaCita.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione un Paciente</option>
                            <?php 
                            // USANDO EL ITERATOR para pacientes
                            foreach ($pacientesIterator as $p) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($p['id_paciente']); ?>">
                                    <?php echo htmlspecialchars($p['nombre_completo'] . ' (DNI: ' . $p['dni'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idTratamiento" class="form-label">Tratamiento (*):</label>
                        <select class="form-select" id="idTratamiento" name="idTratamiento" required>
                            <option value="">Seleccione un Tratamiento</option>
                            <?php 
                            // USANDO EL ITERATOR para tratamientos
                            foreach ($tratamientosIterator as $t) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($t['id_tratamiento']); ?>" data-duracion="<?php echo htmlspecialchars($t['duracion_estimada']); ?>">
                                    <?php echo htmlspecialchars($t['nombre'] . ' (' . $t['duracion_estimada'] . ' min)'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico (*):</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">Seleccione un Médico</option>
                            <?php 
                            // USANDO EL ITERATOR para médicos
                            foreach ($medicosIterator as $m) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($m['id_medico']); ?>">
                                    <?php echo htmlspecialchars($m['nombre_completo']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fechaHora" class="form-label">Fecha y Hora (*):</label>
                        <input type="datetime-local" class="form-control" id="fechaHora" name="fechaHora" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="duracion" class="form-label">Duración (min) (*):</label>
                        <input type="number" class="form-control" id="duracion" name="duracion" value="30" min="5" required>
                        <small class="form-text text-muted">Se sugiere la duración estimada del tratamiento.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado (*):</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Pendiente" selected>Pendiente</option>
                            <option value="Confirmada">Confirmada</option>
                            <option value="Cancelada">Cancelada</option>
                             <option value="Cancelada">Completada</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas:</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnAgregar" class="btn btn-success">Programar Cita</button>
                    <a href="../indexCita.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script para autocompletar la duración según el tratamiento
    document.getElementById('idTratamiento').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var duracionEstimada = selectedOption.getAttribute('data-duracion');
        if (duracionEstimada) {
            document.getElementById('duracion').value = duracionEstimada;
        } else {
            document.getElementById('duracion').value = 30; // Valor por defecto
        }
    });
</script>

<?php
        // 1. TEMPLATE METHOD: Paso del pie de página
        $this->pieShow();
    }
}
?>