<?php

include_once('../../../shared/pantalla.php');
include_once('../../../modelo/PacienteDAO.php');

// ==========================================================
// ESTRUCTURA DE PATRONES: ITERATOR (Auxiliar)
// ==========================================================

/**
 * Clase AuxiliarIterator (PATRÓN: ITERATOR) 📜
 * Permite recorrer la colección de pacientes de forma abstracta.
 * Atributos: $data, $position
 * Métodos: __construct, rewind, current, key, next, valid
 */
class AuxiliarIterator implements Iterator {
    private $data = [];
    private $position = 0;
    
    public function __construct(array $array) { $this->data = array_values($array); } // Asegura índices numéricos
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->data[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->data[$this->position]); }
}


// ==========================================================
// ESTRUCTURA DE PATRONES: TEMPLATE METHOD y STATE (en la presentación)
// ==========================================================

/**
 * Clase formTotalPaciente (PATRÓN: TEMPLATE METHOD) 🎨
 * Hereda y utiliza métodos abstractos de 'pantalla' (cabeceraShow y pieShow).
 */
class formTotalPaciente extends pantalla 
{
    // Método: formTotalPacienteShow()
    public function formTotalPacienteShow()
    {
        // 1. TEMPLATE METHOD: Paso de la cabecera
        $this->cabeceraShow("Gestión de Pacientes");

        $objPacienteDAO = new PacienteDAO();
        $listaPacientes = $objPacienteDAO->obtenerTodosPacientes();

        // 2. ITERATOR: Creación del iterador para la lista de pacientes
        $pacientesIterator = new AuxiliarIterator($listaPacientes);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-person-lines-fill me-2"></i>Lista de Pacientes</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
               <div class="col-md-12 text-end">
                    <a href="./agregarPaciente/indexPacienteAgregar.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Registrar Datos Adicionales
                    </a>
                    <a href="../gestionProgramarCitas/indexCita.php" class="btn btn-warning text-dark">
                        <i class="bi bi-calendar-plus-fill me-2"></i>Programar Citas después de Registrar
                    </a>
                    <a href="../generarOrdenPrefactura/indexOdenPrefactura.php" class="btn btn-danger">
                        <i class="bi bi-file-earmark-text-fill me-2"></i>PreFactura después de Programar Citas
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Paciente</th>
                            <th>Usuario</th>
                            <th>Nombres y Apellidos</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th> <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // 3. ITERATOR: Recorrido de la lista de pacientes
                        foreach ($pacientesIterator as $paciente) { 
                            // 4. PATRÓN STATE (Representación visual)
                            $activo = $paciente['activo'] == 1;
                            $claseEstado = $activo ? 'bg-success' : 'bg-danger';
                            $textoEstado = $activo ? 'Activo' : 'Inactivo';
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($paciente['id_paciente']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['usuario_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . $paciente['apellido_materno']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['dni']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['email']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['telefono']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $claseEstado; ?>">
                                            <?php echo $textoEstado; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="./editarPaciente/indexEditarPaciente.php?id=<?php echo htmlspecialchars($paciente['id_paciente']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php if ($activo) { ?>
                                            <button class="btn btn-sm btn-danger" title="Eliminar/Desactivar" onclick="confirmarEliminar(<?php echo htmlspecialchars($paciente['id_paciente']); ?>)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn btn-sm btn-success" title="Reactivar" onclick="confirmarReactivar(<?php echo htmlspecialchars($paciente['id_paciente']); ?>)">
                                                <i class="bi bi-person-check-fill"></i>
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                        <?php } ?>
                        <?php if ($pacientesIterator->key() === 0 && !$pacientesIterator->valid()) { ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay pacientes registrados.</td>
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
        if (confirm('¿Está seguro de que desea ELIMINAR este paciente?\n\n• Si es RECIÉN registrado: se ELIMINARÁ completamente\n• Si tiene historial: se DESACTIVARÁ')) {
            // Llama al FRONT CONTROLLER (getPaciente.php) con el Comando 'eliminar'
            window.location.href = `./getPaciente.php?action=eliminar&id=${id}`; 
        }
    }

    function confirmarReactivar(id) {
        if (confirm('¿Está seguro de que desea REACTIVAR este paciente?')) {
            // Llama al FRONT CONTROLLER (getPaciente.php) con el Comando 'reactivar'
            window.location.href = `./getPaciente.php?action=reactivar&id=${id}`;
        }
    }
</script>

<?php
        // 1. TEMPLATE METHOD: Paso del pie de página
        $this->pieShow();
    }
}
?>