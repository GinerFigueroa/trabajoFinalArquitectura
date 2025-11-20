
<?php
// Archivo: formTotalDocumentos.php
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/DocumentoDAO.php'); 

/**
 * Clase DocumentoIterator (PATRÓN: ITERATOR) 
 */
class DocumentoIterator implements Iterator {
    private $documentos;
    private $position = 0;

    public function __construct(array $documentos) { $this->documentos = $documentos; }
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->documentos[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->documentos[$this->position]); }
}


class formTotalDocumentos extends pantalla // PATRÓN: TEMPLATE METHOD
{
    // Lógica simple de Visitor para el tipo de documento (solo un ícono)
    private function getIconForType($tipo) {
        switch ($tipo) {
            case 'Radiografía': return 'bi-file-medical';
            case 'Consentimiento': return 'bi-file-earmark-check';
            case 'Historial': return 'bi-journal-medical';
            default: return 'bi-file-text';
        }
    }

    public function formTotalDocumentosShow()
    {
        $this->cabeceraShow("Gestión de Documentos del Paciente");

        $objDAO = new DocumentosDAO();
        
        // 1. Obtener el Array de Documentos del DAO
        $listaDocumentosArray = $objDAO->obtenerTodosDocumentos();
        
        // 2. PATRÓN ITERATOR: Encapsular el Array
        $documentosIterator = new DocumentoIterator($listaDocumentosArray);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white text-center">
            <h4><i class="bi bi-folder-check me-2"></i>Documentos Subidos</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarDocumento/indexAgregarDocumento.php" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Subir Nuevo Documento
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Subido En</th>
                            <th>Notas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // 🚀 USANDO EL ITERATOR
                        if ($documentosIterator->valid()) {
                            foreach ($documentosIterator as $doc) { 
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['id_documento']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['nombre_paciente']); ?></td>
                                    <td>
                                        <i class="bi <?php echo $this->getIconForType($doc['tipo']); ?>"></i>
                                        <?php echo htmlspecialchars($doc['tipo']); ?>
                                    </td>
                                    <td>
                               <td>
 <a href="http://localhost/<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" 
       target="_blank" 
       title="Ver Documento">
        <?php echo htmlspecialchars($doc['nombre']); ?>
    </a>
</td>                            </td>
                                    <td><?php echo htmlspecialchars($doc['subido_en_formato']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($doc['notas'] ?? '', 0, 50) . '...'); ?></td>
                                    <td>
                                        <a href="./editarDocumento/indexEditarDocumento.php?id=<?php echo htmlspecialchars($doc['id_documento']); ?>" class="btn btn-sm btn-warning" title="Editar Metadatos">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($doc['id_documento']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay documentos subidos en el sistema.</td>
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
        if (confirm('¿Está seguro de que desea eliminar este documento? Esto eliminará el registro y el archivo.')) {
            // Se asume que getDocumentos.php es el dispatcher correcto
            window.location.href = `./getDocumento.php?action=eliminar&id=${id}`; 
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>