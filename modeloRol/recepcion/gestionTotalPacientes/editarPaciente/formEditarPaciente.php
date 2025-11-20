<?php
// Archivo: formEditarPaciente.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/pacienteDAO.php');

// ==========================================================
// PATRÓN: ITERATOR (Clase Auxiliar)
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
// PATRÓN: STATE (Para la presentación del DNI)
// ==========================================================
interface DniState {
    public function renderDniInput(string $dni): string;
}

class ValidDniState implements DniState {
    public function renderDniInput(string $dni): string {
        return '<input type="text" class="form-control is-valid" id="dni" name="dni" value="' . htmlspecialchars($dni) . '" required pattern="[0-9]{8,20}">';
    }
}

class InvalidDniState implements DniState {
    public function renderDniInput(string $dni): string {
        return '<input type="text" class="form-control is-invalid" id="dni" name="dni" value="' . htmlspecialchars($dni) . '" required pattern="[0-9]{8,20}">';
    }
}

class DniStateContext {
    private DniState $state;

    public function __construct(string $dni) {
        // Lógica simple para determinar el estado: si tiene 8 dígitos, se considera válido (solo para vista)
        if (is_numeric($dni) && strlen($dni) >= 8) {
            $this->state = new ValidDniState();
        } else {
            $this->state = new InvalidDniState();
        }
    }

    public function render(string $dni): string {
        return $this->state->renderDniInput($dni);
    }
}
// ==========================================================


class formEditarPaciente extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // ... (Métodos obtenerDatosPacienteStrategy y formEditarPacienteShow se mantienen) ...
    public function formEditarPacienteShow()
    {
        // 1. Paso del Template: Mostrar Cabecera
        $this->cabeceraShow('Editar Paciente');

        $idPaciente = isset($_GET['id']) ? $_GET['id'] : null;

        if (!$idPaciente) {
            echo '<div class="alert alert-danger" role="alert">ID de paciente no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        // 2. Paso del Template: Obtener datos
        $paciente = $this->obtenerDatosPacienteStrategy($idPaciente);

        if (!$paciente) {
            echo '<div class="alert alert-danger" role="alert">Paciente no encontrado.</div>';
            $this->pieShow();
            return;
        }

        // 3. Paso del Template: Renderizar el formulario con los datos
        $this->renderFormulario($paciente);

        // 4. Paso del Template: Mostrar Pie
        $this->pieShow();
    }
    
    protected function obtenerDatosPacienteStrategy($idPaciente)
    {
        $objPaciente = new PacienteDAO();
        return $objPaciente->obtenerPacientePorId($idPaciente);
    }
    
    // Función que renderiza la vista
    protected function renderFormulario($paciente)
    {
        // Uso del ITERATOR para las opciones de selección
        $opcionesSexoData = [
            ['valor' => 'Masculino', 'etiqueta' => 'Masculino'],
            ['valor' => 'Femenino', 'etiqueta' => 'Femenino'],
            ['valor' => 'Otro', 'etiqueta' => 'Otro']
        ];
        $iteratorSexo = new AuxiliarIterator($opcionesSexoData);
        
        $opcionesEstadoCivilData = [
            ['valor' => 'Soltero', 'etiqueta' => 'Soltero(a)'],
            ['valor' => 'Casado', 'etiqueta' => 'Casado(a)'],
            ['valor' => 'Divorciado', 'etiqueta' => 'Divorciado(a)'],
            ['valor' => 'Viudo', 'etiqueta' => 'Viudo(a)']
        ];
        $iteratorEstadoCivil = new AuxiliarIterator($opcionesEstadoCivilData);

        // PATRÓN STATE: Determinar la clase de entrada del DNI
        $dniContext = new DniStateContext($paciente['dni']);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Paciente</h4>
        </div>
        <div class="card-body">
            <form action="./getEditarPaciente.php" method="POST">
                <input type="hidden" name="idPaciente" value="<?php echo htmlspecialchars($paciente['id_paciente']); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">Usuario Asociado:</label>
                        <input type="text" class="form-control" id="usuario" value="<?php echo htmlspecialchars($paciente['usuario_usuario']); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dni" class="form-label">DNI (*):</label>
                        <?php 
                        // 🌟 USANDO EL STATE CONTEXT
                        echo $dniContext->render($paciente['dni']); 
                        ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" value="<?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="lugarNacimiento" class="form-label">Lugar de Nacimiento:</label>
                        <input type="text" class="form-control" id="lugarNacimiento" name="lugarNacimiento" value="<?php echo htmlspecialchars($paciente['lugar_nacimiento']); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ocupacion" class="form-label">Ocupación:</label>
                        <input type="text" class="form-control" id="ocupacion" name="ocupacion" value="<?php echo htmlspecialchars($paciente['ocupacion']); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="domicilio" class="form-label">Domicilio:</label>
                        <input type="text" class="form-control" id="domicilio" name="domicilio" value="<?php echo htmlspecialchars($paciente['domicilio']); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="distrito" class="form-label">Distrito:</label>
                        <input type="text" class="form-control" id="distrito" name="distrito" value="<?php echo htmlspecialchars($paciente['distrito']); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edad" class="form-label">Edad:</label>
                        <input type="number" class="form-control" id="edad" name="edad" value="<?php echo htmlspecialchars($paciente['edad']); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sexo" class="form-label">Sexo:</label>
                        <select class="form-select" id="sexo" name="sexo">
                            <option value="">Seleccione...</option>
                            <?php 
                            // 🚀 USO DEL ITERATOR PARA SEXO
                            foreach ($iteratorSexo as $opcion) { 
                                $selected = ($paciente['sexo'] == $opcion['valor']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($opcion['valor']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($opcion['etiqueta']); ?>
                                </option>
                            <?php 
                            } 
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="estadoCivil" class="form-label">Estado Civil:</label>
                        <select class="form-select" id="estadoCivil" name="estadoCivil">
                            <option value="">Seleccione...</option>
                            <?php 
                            // 🚀 USO DEL ITERATOR PARA ESTADO CIVIL
                            foreach ($iteratorEstadoCivil as $opcion) { 
                                $selected = ($paciente['estado_civil'] == $opcion['valor']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($opcion['valor']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($opcion['etiqueta']); ?>
                                </option>
                            <?php 
                            } 
                            ?>
                        </select>
                    </div>
                </div>
                
                <hr>
                <h5 class="mt-4">Información del Apoderado (si aplica)</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nombreApoderado" class="form-label">Nombres Apoderado:</label>
                        <input type="text" class="form-control" id="nombreApoderado" name="nombreApoderado" value="<?php echo htmlspecialchars($paciente['nombre_apoderado'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="apellidoPaternoApoderado" class="form-label">Apellido Paterno Apoderado:</label>
                        <input type="text" class="form-control" id="apellidoPaternoApoderado" name="apellidoPaternoApoderado" value="<?php echo htmlspecialchars($paciente['apellido_paterno_apoderado'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="apellidoMaternoApoderado" class="form-label">Apellido Materno Apoderado:</label>
                        <input type="text" class="form-control" id="apellidoMaternoApoderado" name="apellidoMaternoApoderado" value="<?php echo htmlspecialchars($paciente['apellido_materno_apoderado'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="parentescoApoderado" class="form-label">Parentesco:</label>
                        <input type="text" class="form-control" id="parentescoApoderado" name="parentescoApoderado" value="<?php echo htmlspecialchars($paciente['parentesco_apoderado'] ?? ''); ?>">
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexTotalPaciente.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    }
}
?>