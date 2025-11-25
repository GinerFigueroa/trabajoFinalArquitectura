

<?php
// Directorio: /vista/evolucion/agregarEvolucionPaciente/formEvolucionPaciente.php

include_once("../../../../../shared/pantalla.php"); 
include_once("../../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../../shared/mensajeSistema.php");

// ==========================================================
// ESTRUCTURAS DE PATRONES: ITERATOR
// ==========================================================

// Atributo: Interfaz Iterador
interface PacienteIterator {
    // Método: `rewind`
    public function rewind(): void;
    // Método: `current`
    public function current(): array;
    // Método: `key`
    public function key(): int;
    // Método: `next`
    public function next(): void;
    // Método: `valid`
    public function valid(): bool;
}

// Atributo: Iterador Concreto
class IteradorPacientes implements PacienteIterator {
    // Atributo: `$collection` (Referencia a la colección)
    private $collection;
    // Atributo: `$position` (Posición actual, estado interno)
    private $position = 0;

    // Método: Constructor
    public function __construct(ColeccionPacientes $collection) {
        $this->collection = $collection;
    }

    // Método: `rewind` (Reinicia la posición)
    public function rewind(): void {
        $this->position = 0;
    }

    // Método: `current` (Retorna el elemento actual)
    public function current(): array {
        return $this->collection->getPacientes()[$this->position];
    }

    // Método: `key` (Retorna la clave actual)
    public function key(): int {
        return $this->position;
    }

    // Método: `next` (Avanza al siguiente elemento)
    public function next(): void {
        $this->position++;
    }

    // Método: `valid` (Verifica si la posición es válida)
    public function valid(): bool {
        return isset($this->collection->getPacientes()[$this->position]);
    }
}

// Atributo: Colección (Iterable)
class ColeccionPacientes {
    // Atributo: `$pacientes` (Array de datos)
    private $pacientes = [];

    // Método: Constructor
    public function __construct(array $data) {
        $this->pacientes = $data;
    }

    // Método: `getPacientes` (Getter de datos)
    public function getPacientes(): array {
        return $this->pacientes;
    }

    // Método: `getIterator` (Crea y retorna el Iterador concreto)
    public function getIterator(): PacienteIterator {
        return new IteradorPacientes($this);
    }
    
    // Método: `isEmpty`
    public function isEmpty(): bool {
        return empty($this->pacientes);
    }
}

// ==========================================================
// VISTA (TEMPLATE METHOD)
// ==========================================================

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Hereda de 'pantalla' para definir la estructura de la página.
 */
class formEvolucionPaciente extends pantalla
{
    // Método: `formEvolucionPacienteShow` (Método del Template)
    public function formEvolucionPacienteShow() 
    {
        $objMensaje = new mensajeSistema();
        
        // Atributo: `$idMedico`
        $idMedico = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0; 
        
        // 1. Obtención de datos y creación de la Colección
        $objHistoriaDAO = new RegistroMedicoDAO();
        // Atributo: `$dataPacientes` (Datos crudos)
        $dataPacientes = $objHistoriaDAO->obtenerPacientesConHistoriaAsignada();
        
        // Atributo: `$coleccion` (La colección que será iterada)
        $coleccion = new ColeccionPacientes($dataPacientes);
        
        if ($idMedico == 0) {
            $objMensaje->mensajeSistemaShow(
                "Debe iniciar sesión para registrar una evolución.", 
                "../../../../../vista/login.php", 
                "error"
            );
            exit();
        }

        // TEMPLATE METHOD: Paso 1 - Cabecera
        $this->cabeceraShow("Registrar Nota de Evolución (SOAP)");
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-journal-medical me-2"></i>Registrar Nota de Evolución (SOAP)</h4>
            <p class="mb-0">Médico ID: <?php echo htmlspecialchars($idMedico); ?></p>
        </div>
        <div class="card-body">
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                </div>
            <?php endif; ?>
            
            <form action="./getEvolucionPaciente.php" method="POST">
                
                <input type="hidden" name="id_usuario_logueado" value="<?php echo htmlspecialchars($idMedico); ?>">
                
                <div class="mb-3">
                    <label for="id_paciente" class="form-label text-primary fw-bold">Paciente con Historia Clínica (*):</label>
                    <select class="form-select" id="id_paciente" name="historia_clinica_id" required 
                        <?php echo $coleccion->isEmpty() ? 'disabled' : ''; ?>>
                        <option value="">-- Seleccione un Paciente --</option>
                        
                        <?php 
                        // Patrón: ITERATOR 🔁 (Uso formal del iterador)
                        if (!$coleccion->isEmpty()):
                            // Atributo: `$iterator`
                            $iterator = $coleccion->getIterator();
                            // Método: `rewind`
                            $iterator->rewind(); 
                            
                            // Método: `valid` y `next`
                            while ($iterator->valid()):
                                // Atributo: `$paciente`
                                $paciente = $iterator->current();
                        ?>
                            <option value="<?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>">
                                <?php echo htmlspecialchars($paciente['nombre_completo']); ?> 
                                (HC ID: <?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>)
                            </option>
                        <?php 
                                $iterator->next(); // Método: `next`
                            endwhile;
                        else: 
                        ?>
                            <option disabled>No hay pacientes con historia clínica registrada.</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($coleccion->isEmpty()): ?>
                        <small class="form-text text-muted">No se encontraron pacientes con historia clínica.</small>
                    <?php endif; ?>
                </div>

                <p class="text-muted fst-italic">Complete las secciones de la nota SOAP (* Campos Requeridos)</p>

                <div class="mb-3">
                    <label for="nota_subjetiva" class="form-label text-primary fw-bold">S: Nota Subjetiva (*)</label>
                    <textarea class="form-control" id="nota_subjetiva" name="nota_subjetiva" rows="4" placeholder="Síntomas, quejas referidas por el paciente, evolución desde la última consulta." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="nota_objetiva" class="form-label text-success fw-bold">O: Nota Objetiva (*)</label>
                    <textarea class="form-control" id="nota_objetiva" name="nota_objetiva" rows="4" placeholder="Hallazgos del examen físico, resultados de laboratorio o imágenes." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="analisis" class="form-label text-danger fw-bold">A: Análisis y Evaluación</label>
                    <textarea class="form-control" id="analisis" name="analisis" rows="3" placeholder="Diagnóstico diferencial, impresión diagnóstica, evaluación de la respuesta al tratamiento."></textarea>
                </div>

                <div class="mb-3">
                    <label for="plan_de_accion" class="form-label text-info fw-bold">P: Plan de Acción (*)</label>
                    <textarea class="form-control" id="plan_de_accion" name="plan_de_accion" rows="4" placeholder="Tratamiento, medicamentos, estudios adicionales solicitados, interconsultas, citas de seguimiento." required></textarea>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="action" value="registrar" class="btn btn-primary btn-lg"
                        <?php echo $coleccion->isEmpty() ? 'disabled' : ''; ?>>
                        <i class="bi bi-save me-2"></i>Registrar Evolución
                    </button>
                    <a href="../indexEvolucionPaciente.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php
        // TEMPLATE METHOD: Paso 2 - Pie
        $this->pieShow();
    }
}
?>