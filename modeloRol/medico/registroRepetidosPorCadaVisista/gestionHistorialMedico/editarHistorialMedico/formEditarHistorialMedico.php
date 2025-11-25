
<?php

include_once("../../../../../shared/pantalla.php"); 
include_once("../../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../../shared/mensajeSistema.php");

// ==========================================================
// 0. ESTRUCTURAS DE PATRONES: ITERATOR
// ==========================================================

// Patrón: ITERATOR 🔄
// Interfaz: Iterator
interface CustomIterator {
    // Método: hasNext (Verifica si hay más elementos)
    public function hasNext(): bool;
    // Método: next (Obtiene el siguiente elemento y avanza)
    public function next();
    // Método: current (Obtiene el elemento actual)
    public function current();
    // Método: rewind (Reinicia el iterador)
    public function rewind();
}

// Clase Concreta: Colección de Historias Clínicas (Agregado Concreto)
class HistoriasClinicasCollection implements \IteratorAggregate {
    // Atributo: $items (El array de datos)
    private $items = [];

    // Método: Constructor
    public function __construct(array $items) {
        $this->items = $items;
    }

    // Método: getIterator (Método para obtener el Iterador Concreto)
    public function getIterator(): \Traversable {
        // Devuelve el Iterador Concreto
        return new HistoriasClinicasIterator($this->items);
    }
}

// Clase Concreta: Iterador para Historias Clínicas
class HistoriasClinicasIterator implements CustomIterator {
    // Atributo: $collection (La colección de datos)
    private $collection = [];
    // Atributo: $position (Posición actual en la colección)
    private $position = 0;

    // Método: Constructor
    public function __construct(array $collection) {
        $this->collection = $collection;
    }

    // Método: rewind
    public function rewind(): void {
        $this->position = 0;
    }

    // Método: current
    public function current() {
        return $this->collection[$this->position];
    }

    // Método: key
    public function key(): int {
        return $this->position;
    }

    // Método: next
    public function next(): void {
        $this->position++;
    }

    // Método: hasNext (Válido, cumple la función de validar el índice)
    public function hasNext(): bool {
        return isset($this->collection[$this->position]);
    }
    
    // Método: valid (Para compatibilidad con \Iterator)
    public function valid(): bool {
        return isset($this->collection[$this->position]);
    }
}

// ==========================================================
// 1. VISTA / TEMPLATE METHOD
// ==========================================================

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Hereda de la clase 'pantalla' para definir el esqueleto de la vista.
 */
class formEditarHistorialPaciente extends pantalla
{
    // Atributo: $objDAO (Receptor para la carga GET de datos)
    private $objDAO;
    
    // Método: Constructor
    public function __construct() {
        $this->objDAO = new RegistroMedicoDAO();
    }

    // Método: formEditarHistorialPacienteShow (Método del Template: Esqueleto de la página)
    public function formEditarHistorialPacienteShow()
    {
        // TEMPLATE METHOD: Paso 1 - Cabecera
        $this->cabeceraShow("Editar Consulta Médica");

        // Lógica de obtención de datos para la vista
        $idRegistro = isset($_GET['reg_id']) ? (int)$_GET['reg_id'] : null;

        if (!$idRegistro) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("ID de registro no proporcionado.", "../indexHistorialMedico.php", "error");
            $this->pieShow();
            return;
        }

        $registro = $this->objDAO->obtenerRegistroPorId($idRegistro);
        
        if (!$registro) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("Registro médico no encontrado.", "../indexHistorialClinico.php", "error");
            $this->pieShow();
            return;
        }

        // --- Aplicación del Patrón ITERATOR ---
        // 1. Obtener datos del Agregado
        $historiasData = $this->objDAO->obtenerHistoriasClinicas();
        // 2. Crear la Colección (Agregado Concreto)
        // Atributo: $historiasClinicasCollection
        $historiasClinicasCollection = new HistoriasClinicasCollection($historiasData);
        // -------------------------------------
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Consulta Médica</h4>
            <p class="mb-0">Paciente: <strong><?php echo htmlspecialchars($registro['nombre_paciente']); ?></strong></p>
        </div>
        <div class="card-body">
            <form action="./getEditarHistorialMedico.php" method="POST">
                <input type="hidden" name="registro_medico_id" value="<?php echo htmlspecialchars($registro['registro_medico_id']); ?>">
                <input type="hidden" name="historia_clinica_id" value="<?php echo htmlspecialchars($registro['historia_clinica_id']); ?>">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>Fecha de registro:</strong> <?php echo date('d/m/Y H:i', strtotime($registro['fecha_registro'])); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small><strong>ID Registro:</strong> <?php echo htmlspecialchars($registro['registro_medico_id']); ?></small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ejemplo: Historias Clínicas disponibles (Iterator)</label>
                    <select class="form-control" disabled>
                        <?php
                        // --- Consumo del Patrón ITERATOR ---
                        // 3. Obtener el Iterador del Agregado
                        // Atributo: $iterator
                        $iterator = $historiasClinicasCollection->getIterator();
                        
                        // 4. Iterar usando el Iterador (Cliente del patrón)
                        foreach ($iterator as $hc) {
                            echo '<option value="' . htmlspecialchars($hc['historia_clinica_id']) . '">' . htmlspecialchars($hc['nombre_paciente']) . '</option>';
                        }
                        // -------------------------------------
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="motivo_consulta" class="form-label">
                        <strong>Motivo de Consulta *</strong>
                    </label>
                    <textarea class="form-control" id="motivo_consulta" name="motivo_consulta" 
                                  rows="3" placeholder="Describa el motivo principal de la consulta..." required><?php echo htmlspecialchars($registro['motivo_consulta']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="enfermedad_actual" class="form-label">
                        <strong>Enfermedad Actual</strong>
                    </label>
                    <textarea class="form-control" id="enfermedad_actual" name="enfermedad_actual" 
                                  rows="3" placeholder="Describa la enfermedad actual del paciente..."><?php echo htmlspecialchars($registro['enfermedad_actual']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="tiempo_enfermedad" class="form-label">
                        <strong>Tiempo de Enfermedad</strong>
                    </label>
                    <input type="text" class="form-control" id="tiempo_enfermedad" name="tiempo_enfermedad" 
                               placeholder="Ej: 3 días, 2 semanas, 1 mes..."
                               value="<?php echo htmlspecialchars($registro['tiempo_enfermedad']); ?>">
                </div>

                <div class="mb-3">
                    <label for="signos_sintomas" class="form-label">
                        <strong>Signos y Síntomas</strong>
                    </label>
                    <textarea class="form-control" id="signos_sintomas" name="signos_sintomas" 
                                  rows="3" placeholder="Describa los signos y síntomas presentes..."><?php echo htmlspecialchars($registro['signos_sintomas']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="riesgos" class="form-label">
                        <strong>Factores de Riesgo</strong>
                    </label>
                    <textarea class="form-control" id="riesgos" name="riesgos" 
                                  rows="2" placeholder="Describa los factores de riesgo identificados..."><?php echo htmlspecialchars($registro['riesgos']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="motivo_ultima_visita" class="form-label">
                        <strong>Motivo de la Última Visita</strong>
                    </label>
                    <textarea class="form-control" id="motivo_ultima_visita" name="motivo_ultima_visita" 
                                  rows="2" placeholder="Describa el motivo de la última visita médica..."><?php echo htmlspecialchars($registro['motivo_ultima_visita']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="ultima_visita_medica" class="form-label">
                        <strong>Fecha de Última Visita Médica</strong>
                    </label>
                    <input type="date" class="form-control" id="ultima_visita_medica" name="ultima_visita_medica" 
                               value="<?php echo $registro['ultima_visita_medica'] ? date('Y-m-d', strtotime($registro['ultima_visita_medica'])) : ''; ?>">
                    <div class="form-text">Dejar vacío si no aplica</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" name="btnActualizar" class="btn btn-warning text-white me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Registro
                    </button>
                    <a href="../indexHistorialMedico.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Validación básica del formulario (mantener la validación del lado del cliente)
    document.querySelector('form').addEventListener('submit', function(e) {
        const motivoConsulta = document.getElementById('motivo_consulta').value.trim();
        
        if (!motivoConsulta) {
            e.preventDefault();
            alert('El motivo de consulta es obligatorio.');
            document.getElementById('motivo_consulta').focus();
        }
    });

    // Limitar la fecha máxima a hoy
    document.getElementById('ultima_visita_medica').max = new Date().toISOString().split('T')[0];
</script>

<?php
        // TEMPLATE METHOD: Paso 2 - Pie
        $this->pieShow();
    }
}
?>