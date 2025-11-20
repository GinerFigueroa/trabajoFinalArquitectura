<?php
// Archivo: formEditarCitas.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/CitasDAO.php'); // CitasDAO y EntidadesDAO

// ==========================================================
// PATRÓN: ITERATOR (Clase Auxiliar para recorridos)
// ==========================================================
if (!class_exists('AuxiliarIterator')) {
    class AuxiliarIterator implements Iterator {
        private $items; private $position = 0;
        public function __construct(array $items) { $this->items = $items; }
        public function rewind(): void { $this->position = 0; }
        public function current(): mixed { return $this->items[$this->position]; }
        public function key(): mixed { return $this->position; }
        public function next(): void { ++$this->position; }
        public function valid(): bool { return isset($this->items[$this->position]); }
    }
}
// ==========================================================

// ==========================================================
// PATRÓN: STATE (Para la visualización del estado)
// ==========================================================
interface CitaDisplayState {
    public function getBadgeClass(): string;
    public function getHeaderClass(): string;
}

class PendienteState implements CitaDisplayState {
    public function getBadgeClass(): string { return 'badge bg-info'; }
    public function getHeaderClass(): string { return 'bg-primary'; }
}

class ConfirmadaState implements CitaDisplayState {
    public function getBadgeClass(): string { return 'badge bg-success'; }
    public function getHeaderClass(): string { return 'bg-success'; }
}

class CanceladaState implements CitaDisplayState {
    public function getBadgeClass(): string { return 'badge bg-danger'; }
    public function getHeaderClass(): string { return 'bg-danger'; }
}

// Contexto que usa el estado para renderizar
class CitaStateContext {
    private CitaDisplayState $state;

    public function __construct(string $estado) {
        switch ($estado) {
            case 'Confirmada':
                $this->state = new ConfirmadaState();
                break;
            case 'Cancelada':
            case 'No asistió':
                $this->state = new CanceladaState();
                break;
            case 'Completada':
                $this->state = new ConfirmadaState(); // Visualmente similar a Confirmada/Éxito
                break;
            default:
                $this->state = new PendienteState();
                break;
        }
    }

    public function getBadge(string $estado): string {
        return '<span class="' . $this->state->getBadgeClass() . '">' . htmlspecialchars($estado) . '</span>';
    }
    public function getHeaderColor(): string {
        return $this->state->getHeaderClass();
    }
}
// ==========================================================


class formEditarCitas extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // Método abstracto (Template Method): Define el flujo
    public function formEditarCitasShow()
    {
        // 1. Obtener Datos
        $idCita = $_GET['id'] ?? null;
        if (!$idCita || !is_numeric($idCita)) {
            $this->cabeceraShow('Error');
            echo '<div class="alert alert-danger" role="alert">ID de cita no válido.</div>';
            $this->pieShow();
            return;
        }

        $objCita = new CitasDAO();
        $objEntidad = new EntidadesDAO();
        
        $cita = $objCita->obtenerCitaPorId($idCita);
        $pacientes = $objEntidad->obtenerPacientesDisponibles(); // Asumo que existe en EntidadesDAO
        $tratamientos = $objEntidad->obtenerTratamientosActivos();
        $medicos = $objEntidad->obtenerMedicosDisponibles();

        if (!$cita) {
            $this->cabeceraShow('Error');
            echo '<div class="alert alert-danger" role="alert">Cita no encontrada.</div>';
            $this->pieShow();
            return;
        }

        // 2. Mostrar Cabecera
        $this->cabeceraShow('Editar Cita');

        // 3. Renderizar Formulario
        $this->renderFormulario($cita, $pacientes, $tratamientos, $medicos);

        // 4. Mostrar Pie
        $this->pieShow();
    }

    // Paso del Template: Renderizar la vista con datos
    protected function renderFormulario($cita, $pacientes, $tratamientos, $medicos)
    {
        $fechaHoraLocal = date('Y-m-d\TH:i', strtotime($cita['fecha_hora']));
        $estados = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada', 'No asistió'];
        
        // Uso del ITERATOR para los estados
        $iteratorEstados = new AuxiliarIterator($estados);

        // Uso del STATE CONTEXT para el color de la cabecera
        $stateContext = new CitaStateContext($cita['estado']);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header <?php echo $stateContext->getHeaderColor(); ?> text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Cita #<?php echo htmlspecialchars($cita['id_cita']); ?></h4>
        </div>
        <div class="card-body">
            <form action="./getEditarCitas.php" method="POST">
                <input type="hidden" name="idCita" value="<?php echo htmlspecialchars($cita['id_cita']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione un Paciente</option>
                            <?php foreach ($pacientes as $p) { ?>
                                <option value="<?php echo htmlspecialchars($p['id_paciente']); ?>" <?php echo ($cita['id_paciente'] == $p['id_paciente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nombre_completo'] . ' (DNI: ' . $p['dni'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idTratamiento" class="form-label">Tratamiento (*):</label>
                        <select class="form-select" id="idTratamiento" name="idTratamiento" required>
                            <option value="">Seleccione un Tratamiento</option>
                            <?php foreach ($tratamientos as $t) { ?>
                                <option value="<?php echo htmlspecialchars($t['id_tratamiento']); ?>" data-duracion="<?php echo htmlspecialchars($t['duracion_estimada']); ?>" <?php echo ($cita['id_tratamiento'] == $t['id_tratamiento']) ? 'selected' : ''; ?>>
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
                            <?php foreach ($medicos as $m) { ?>
                                <option value="<?php echo htmlspecialchars($m['id_medico']); ?>" <?php echo ($cita['id_medico'] == $m['id_medico']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['nombre_completo']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fechaHora" class="form-label">Fecha y Hora (*):</label>
                        <input type="datetime-local" class="form-control" id="fechaHora" name="fechaHora" value="<?php echo htmlspecialchars($fechaHoraLocal); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="duracion" class="form-label">Duración (min) (*):</label>
                        <input type="number" class="form-control" id="duracion" name="duracion" value="<?php echo htmlspecialchars($cita['duracion']); ?>" min="5" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado (*):</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <?php 
                            // 🚀 USO DEL ITERATOR PARA ESTADOS
                            foreach ($iteratorEstados as $e) { 
                                $selected = ($cita['estado'] == $e) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($e); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="creadoEn" class="form-label">Estado Actual:</label>
                        <div class="p-2 border rounded">
                            <?php echo $stateContext->getBadge($cita['estado']); ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas:</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"><?php echo htmlspecialchars($cita['notas'] ?? ''); ?></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexCita.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('idTratamiento').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var duracionEstimada = selectedOption.getAttribute('data-duracion');
        if (duracionEstimada) {
            document.getElementById('duracion').value = duracionEstimada;
        }
    });
</script>

<?php
    }
}
?>