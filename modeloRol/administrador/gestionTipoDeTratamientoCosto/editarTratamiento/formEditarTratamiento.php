<?php
// C:\...\gestionTipoDeTratamientoCosto\editarTratamiento\formEditarTratamiento.php

include_once('../../../../shared/pantalla.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/TratamientoDAO.php'); 


/**
 * Clase AuxiliarIterator (PATRÓN: ITERATOR) 
 * Definida aquí para que esté disponible sin un include_once externo.
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


class formEditarTratamiento extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // PATRÓN: STATE
    private $estadoFormulario = 'inicial'; 

    private function setEstadoFormulario($estado) { $this->estadoFormulario = $estado; }

    /**
     * PATRÓN: VISITOR (Lógica para presentación del estado Activo/Inactivo)
     */
    private function acceptVisitor($tratamiento) {
        $estado = $tratamiento['activo'];
        $color = 'secondary';
        $texto = 'Inactivo';

        if ($estado == 1) {
            $color = 'success';
            $texto = 'Activo';
        } else {
            $color = 'danger';
            $texto = 'Inactivo';
        }
        return ['color' => $color, 'texto' => $texto];
    }

    public function formEditarTratamientoShow()
    {
        // 1. OBTENCIÓN DE DATOS (Paso del Template)
        $this->setEstadoFormulario('cargando_datos'); // STATE: Iniciando carga

        $idTratamiento = (int)($_GET['id'] ?? 0); 
        // Instancia ahora funciona porque mensajeSistema.php está incluido
        $objMensaje = new mensajeSistema(); 
        
        if ($idTratamiento <= 0) {
            $this->cabeceraShow('Error');
            $objMensaje->mensajeSistemaShow("ID de Tratamiento no especificado o inválido.", '../indexTipoTratamiento.php', "error");
            $this->pieShow();
            return;
        }

        $this->cabeceraShow('Editar Tratamiento');

        // DAO (Data Access Object)
        $objTratamiento = new TratamientoDAO();
        // Asumiendo que EspecialidadDAO está en TratamientoDAO.php o incluido
        $objEspecialidad = new EspecialidadDAO(); 

        $tratamiento = $objTratamiento->obtenerTratamientoPorId($idTratamiento);
        $listaEspecialidadesArray = $objEspecialidad->obtenerTodasEspecialidades();

        // PATRÓN: ITERATOR (Usa la clase AuxiliarIterator definida en este archivo)
        $listaEspecialidadesIterator = new AuxiliarIterator($listaEspecialidadesArray); // ⬅️ LÍNEA 53 (Resuelta)

        if (!$tratamiento) {
            $objMensaje->mensajeSistemaShow('Tratamiento no encontrado en la base de datos.', '../indexTipoTratamiento.php', 'error');
            $this->pieShow();
            return;
        }
        
        $this->setEstadoFormulario('edicion_activa'); // STATE
        $estadoActual = $this->acceptVisitor($tratamiento); // VISITOR
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Tratamiento ID: <?php echo htmlspecialchars($idTratamiento); ?> 
                (Modo: **<?php echo $this->estadoFormulario; ?>**)
            </h4>
        </div>
        <div class="card-body">
        
        <form action="./getEditarTratamiento.php" method="POST">
                
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="idTratamiento" value="<?php echo htmlspecialchars($tratamiento['id_tratamiento']); ?>">
                
                <div class="mb-3">
                    <label for="editNombre" class="form-label">Nombre del Tratamiento:</label>
                    <input type="text" class="form-control" id="editNombre" name="editNombre" value="<?php echo htmlspecialchars($tratamiento['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editEspecialidad" class="form-label">Especialidad:</label>
                    <select class="form-select" id="editEspecialidad" name="editEspecialidad" required>
                        <?php 
                        // USANDO EL ITERATOR
                        foreach ($listaEspecialidadesIterator as $especialidad) { ?>
                            <option value="<?php echo htmlspecialchars($especialidad['id_especialidad']); ?>" <?php echo ($tratamiento['id_especialidad'] == $especialidad['id_especialidad']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($especialidad['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editDescripcion" class="form-label">Descripción:</label>
                    <textarea class="form-control" id="editDescripcion" name="editDescripcion" rows="3"><?php echo htmlspecialchars($tratamiento['descripcion'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="editDuracion" class="form-label">Duración (minutos):</label>
                    <input type="number" class="form-control" id="editDuracion" name="editDuracion" value="<?php echo htmlspecialchars($tratamiento['duracion_estimada']); ?>" required min="1">
                </div>
                <div class="mb-3">
                    <label for="editCosto" class="form-label">Costo (S/):</label>
                    <input type="number" class="form-control" id="editCosto" name="editCosto" step="0.01" value="<?php echo htmlspecialchars($tratamiento['costo']); ?>" required min="0">
                </div>
                <div class="mb-3">
                    <label for="editRequisitos" class="form-label">Requisitos:</label>
                    <textarea class="form-control" id="editRequisitos" name="editRequisitos" rows="3"><?php echo htmlspecialchars($tratamiento['requisitos'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="editActivo" class="form-label">Estado Actual: 
                        <span class="badge rounded-pill bg-<?php echo $estadoActual['color']; ?>"><?php echo $estadoActual['texto']; ?></span>
                    </label>
                    <select class="form-select" id="editActivo" name="editActivo" required>
                        <option value="1" <?php echo $tratamiento['activo'] == 1 ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo $tratamiento['activo'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexTipoTratamiento.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
        $this->pieShow();
    }
}
?>