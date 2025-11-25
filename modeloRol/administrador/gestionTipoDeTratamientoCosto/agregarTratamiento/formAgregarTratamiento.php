<?php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/TratamientoDAO.php'); 
// Se asume que TratamientoDAO.php contiene o incluye EspecialidadDAO

/**
 * Clase AuxiliarIterator (PATRÓN: ITERATOR) 
 * Si no está incluido en TratamientoDAO.php, defínelo aquí.
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


class formAgregarTratamiento extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // Usamos STATE de forma implícita para la vista: 'registro_activo'

    public function formAgregarTratamientoShow()
    {
        $this->cabeceraShow('Registrar Nuevo Tratamiento');

        $objEspecialidad = new EspecialidadDAO(); 

        $listaEspecialidadesArray = $objEspecialidad->obtenerTodasEspecialidades();

        // PATRÓN: ITERATOR
        $listaEspecialidadesIterator = new AuxiliarIterator($listaEspecialidadesArray);

        // Los valores por defecto pueden venir de un DTO de inicialización si fuera necesario,
        // pero aquí los dejamos vacíos para el registro.
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Tratamiento</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarTratamiento.php" method="POST">
                
                <input type="hidden" name="action" value="registrar">
                
                <div class="mb-3">
                    <label for="regNombre" class="form-label">Nombre del Tratamiento:</label>
                    <input type="text" class="form-control" id="regNombre" name="regNombre" required>
                </div>
                
                <div class="mb-3">
                    <label for="regEspecialidad" class="form-label">Especialidad:</label>
                    <select class="form-select" id="regEspecialidad" name="regEspecialidad" required>
                        <option value="" selected disabled>Seleccione una Especialidad</option>
                        <?php 
                        // USANDO EL ITERATOR
                        foreach ($listaEspecialidadesIterator as $especialidad) { ?>
                            <option value="<?php echo htmlspecialchars($especialidad['id_especialidad']); ?>">
                                <?php echo htmlspecialchars($especialidad['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="regDescripcion" class="form-label">Descripción:</label>
                    <textarea class="form-control" id="regDescripcion" name="regDescripcion" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="regDuracion" class="form-label">Duración (minutos):</label>
                    <input type="number" class="form-control" id="regDuracion" name="regDuracion" required min="1">
                </div>
                <div class="mb-3">
                    <label for="regCosto" class="form-label">Costo (S/):</label>
                    <input type="number" class="form-control" id="regCosto" name="regCosto" step="0.01" required min="0">
                </div>
                <div class="mb-3">
                    <label for="regRequisitos" class="form-label">Requisitos:</label>
                    <textarea class="form-control" id="regRequisitos" name="regRequisitos" rows="3"></textarea>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-success">Registrar Tratamiento</button>
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