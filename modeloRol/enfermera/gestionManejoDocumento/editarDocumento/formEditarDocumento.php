
<?php
// Archivo: formEditarDocumento.php
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

class formEditarDocumento extends pantalla // PATRÓN: TEMPLATE METHOD
{
    public function formEditarDocumentoShow()
    {
        $idDocumento = $_GET['id'] ?? null;
        if (!$idDocumento || !is_numeric($idDocumento)) {
            $this->cabeceraShow('Error');
            echo '<div class="alert alert-danger" role="alert">ID de documento no válido.</div>';
            $this->pieShow();
            return;
        }

        $objDAO = new DocumentosDAO();
        $objEntidad = new EntidadesDAO();
        
        $documento = $objDAO->obtenerDocumentoPorId($idDocumento);
        $pacientes = $objEntidad->obtenerPacientesDisponibles(); // Asumo que existe

        if (!$documento) {
            $this->cabeceraShow('Error');
            echo '<div class="alert alert-danger" role="alert">Documento no encontrado.</div>';
            $this->pieShow();
            return;
        }

        $this->cabeceraShow('Editar Documento');
        $this->renderFormulario($documento, $pacientes);
        $this->pieShow();
    }

    protected function renderFormulario($documento, $pacientes)
    {
        $tipos = ['Radiografía', 'Consentimiento', 'Historial', 'Otro'];
        $iteratorTipos = new AuxiliarIterator($tipos);
        $subidoEn = date('d/m/Y H:i', strtotime($documento['subido_en']));
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Documento #<?php echo htmlspecialchars($documento['id_documento']); ?></h4>
        </div>
        <div class="card-body">
            <form action="./getEditarDocumento.php" method="POST">
                <input type="hidden" name="idDocumento" value="<?php echo htmlspecialchars($documento['id_documento']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione un Paciente</option>
                            <?php foreach ($pacientes as $p) { ?>
                                <option value="<?php echo htmlspecialchars($p['id_paciente']); ?>" <?php echo ($documento['id_paciente'] == $p['id_paciente']) ? 'selected' : ''; ?>>
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
                            // 🚀 USO DEL ITERATOR PARA TIPOS
                            foreach ($iteratorTipos as $t) { 
                                $selected = ($documento['tipo'] == $t) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre/Título del Documento (*):</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($documento['nombre']); ?>" maxlength="100" required>
                </div>
                
                <div class="mb-3">
                    <label for="ruta_archivo" class="form-label">Ruta del Archivo (Solo Lectura):</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($documento['ruta_archivo']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas:</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"><?php echo htmlspecialchars($documento['notas'] ?? ''); ?></textarea>
                </div>

                <p class="text-muted small">Subido el: <?php echo htmlspecialchars($subidoEn); ?></p>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexDocumento.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    }
}
?>