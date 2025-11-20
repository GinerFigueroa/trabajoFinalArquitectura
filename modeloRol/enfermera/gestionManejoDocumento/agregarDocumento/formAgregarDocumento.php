<?php
// Archivo: formAgregarNuevoDocumento.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/DocumentoDAO.php'); 


// Clase AuxiliarIterator (PATRÓN: ITERATOR) 
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

class formAgregarNuevoDocumento extends pantalla // PATRÓN: TEMPLATE METHOD
{
    public function formAgregarNuevoDocumentoShow()
    {
        $this->cabeceraShow('Subir Nuevo Documento');

        $objEntidad = new EntidadesDAO();
        $pacientesArray = $objEntidad->obtenerPacientesDisponibles();
        $pacientesIterator = new AuxiliarIterator($pacientesArray);
        
        $this->renderFormulario($pacientesIterator);
        $this->pieShow();
    }

    protected function renderFormulario($pacientesIterator)
    {
        $tipos = ['Radiografía', 'Consentimiento', 'Historial', 'Otro'];
        $iteratorTipos = new AuxiliarIterator($tipos);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-earmark-plus me-2"></i>Subir Documento Clínico</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarDocumento.php" method="POST" enctype="multipart/form-data">
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
                        <label for="tipo" class="form-label">Tipo de Documento (*):</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccione un Tipo</option>
                            <?php 
                            // USANDO EL ITERATOR para tipos
                            foreach ($iteratorTipos as $t) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($t); ?>">
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre/Título del Documento (*):</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Radiografía de Tórax 2025" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label for="archivo" class="form-label">Archivo (PDF, JPG, PNG) (*):</label>
                    <input class="form-control" type="file" id="archivo" name="archivo" required>
                </div>
                
                <div class="mb-3">
                    <label for="notas" class="form-label">Notas:</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnRegistrar" class="btn btn-primary">Subir Documento</button>
                    <a href="../indexDumento.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    }
}
?>