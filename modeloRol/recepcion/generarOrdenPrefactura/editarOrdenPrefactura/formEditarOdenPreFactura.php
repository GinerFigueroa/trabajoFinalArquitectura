<?php
// FILE: formEditarOdenPreFactura.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/OrdenPagoDAO.php');

/**
 * PATRÓN: STATE (Contexto de Visualización)
 * Gestiona si la orden es editable basándose en su estado.
 */
class OrdenEditState {
    // MÉTODO (Lógica de Mapeo de Estado)
    public function esEditable(string $estado): bool {
        // ATRIBUTO implícito: Solo 'Pendiente' permite la edición.
        return $estado === 'Pendiente';
    }
}

/**
 * PATRÓN: TEMPLATE METHOD (Clase Concreta)
 * Define el esqueleto del proceso de visualización del formulario.
 */
class formEditarOdenPreFactura extends pantalla
{
    // ATRIBUTOS
    private $objOrdenDAO; // Modelo
    private $stateChecker;

    // MÉTODO (Constructor)
    public function __construct() {
        $this->objOrdenDAO = new OrdenPago();
        $this->stateChecker = new OrdenEditState();
    }
    
    // MÉTODO (El Template Method principal)
    public function formEditarOdenPreFacturaShow()
    {
        // PASO 1 (Plantilla): Cabecera
        $this->cabeceraShow('Editar Orden de Prefactura');

        $idOrden = $_GET['id'] ?? null;

        if (!$idOrden) {
            echo '<div class="alert alert-danger" role="alert">ID de Orden de Pago no proporcionado.</div>';
            // PASO FINAL (Plantilla): Pie
            $this->pieShow();
            return;
        }

        $orden = $this->objOrdenDAO->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            echo '<div class="alert alert-danger" role="alert">Orden de Pago no encontrada.</div>';
            $this->pieShow();
            return;
        }

        // Uso del PATRÓN STATE para determinar la editabilidad
        $esEditable = $this->stateChecker->esEditable($orden['estado']);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Orden de Prefactura N° <?php echo htmlspecialchars($orden['id_orden']); ?></h4>
        </div>
        <div class="card-body">
            <?php if (!$esEditable) { ?>
                <div class="alert alert-info text-center">
                    Esta orden se encuentra en estado **<?php echo htmlspecialchars($orden['estado']); ?>** y **no puede ser editada**.
                </div>
            <?php } ?>
            
            <form action="./getEditarOrdenPreFactura.php" method="POST">
                <input type="hidden" name="idOrden" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">

                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required <?php echo $esEditable ? '' : 'disabled'; ?>><?php echo htmlspecialchars($orden['concepto']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="<?php echo htmlspecialchars($orden['monto_estimado']); ?>" required min="0.01" <?php echo $esEditable ? '' : 'disabled'; ?>>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <?php if ($esEditable) { ?>
                        <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <?php } ?>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Volver al Listado</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
        // PASO FINAL (Plantilla): Pie de página
        $this->pieShow();
    }
}
?>