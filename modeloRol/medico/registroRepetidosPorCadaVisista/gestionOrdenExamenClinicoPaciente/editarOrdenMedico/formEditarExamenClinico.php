<?php
include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/OrdenExamenDAO.php');

class formEditarExamenClinico extends pantalla
{
    public function formEditarExamenClinicoShow()
    {
        $this->cabeceraShow("Editar Orden de Examen");

        // Validar que se proporcionó el ID de la orden
        if (!isset($_GET['id_orden']) || !is_numeric($_GET['id_orden'])) {
            include_once('../../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("ID de orden no válido", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        $idOrden = (int)$_GET['id_orden'];
        $objDAO = new OrdenExamenDAO();
        
        // Obtener la orden específica
        $orden = $objDAO->obtenerOrdenPorId($idOrden);
        
        if (!$orden) {
            include_once('../../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("Orden no encontrada", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        // Obtener datos para los selects (solo historias clínicas, no médicos)
        $historiasClinicas = $objDAO->obtenerHistoriasClinicas();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Orden de Examen (ID: <?php echo htmlspecialchars($orden['id_orden']); ?>)</h4>
        </div>
        <div class="card-body">
            <form action="./getEditarExamenClinico.php" method="POST">
                <input type="hidden" name="id_orden" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">
                
                <!-- Información de solo lectura -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-person-badge me-2"></i>Información del Médico</h6>
                                <p class="card-text mb-1">
                                    <strong>Médico:</strong> <?php echo htmlspecialchars($orden['nombre_medico']); ?>
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Paciente:</strong> <?php echo htmlspecialchars($orden['nombre_paciente']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Información de la Orden</h6>
                                <p class="card-text mb-1">
                                    <strong>ID Orden:</strong> <?php echo htmlspecialchars($orden['id_orden']); ?>
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Fecha creación:</strong> <?php echo date('d/m/Y', strtotime($orden['fecha'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="historia_clinica_id" class="form-label">Historia Clínica:</label>
                        <select class="form-select" id="historia_clinica_id" name="historia_clinica_id" required>
                            <option value="">-- Seleccione Historia Clínica --</option>
                            <?php foreach ($historiasClinicas as $historia): 
                                $selected = ($historia['historia_clinica_id'] == $orden['historia_clinica_id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($historia['historia_clinica_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars("HC-{$historia['historia_clinica_id']} - {$historia['nombre_paciente']}"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha del Examen:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" 
                               value="<?php echo htmlspecialchars($orden['fecha']); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="tipo_examen" class="form-label">Tipo de Examen:</label>
                        <input type="text" class="form-control" id="tipo_examen" name="tipo_examen" 
                               value="<?php echo htmlspecialchars($orden['tipo_examen']); ?>" required 
                               placeholder="Ej: Hemograma, Radiografía, Ecografía">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="indicaciones" class="form-label">Indicaciones Médicas:</label>
                    <textarea class="form-control" id="indicaciones" name="indicaciones" 
                              rows="4" placeholder="Indicaciones específicas para el examen"><?php echo htmlspecialchars($orden['indicaciones']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado del Examen:</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Pendiente" <?php echo $orden['estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Realizado" <?php echo $orden['estado'] == 'Realizado' ? 'selected' : ''; ?>>Realizado</option>
                            <option value="Entregado" <?php echo $orden['estado'] == 'Entregado' ? 'selected' : ''; ?>>Entregado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="resultados" class="form-label">Resultados del Examen:</label>
                    <textarea class="form-control" id="resultados" name="resultados" 
                              rows="5" placeholder="Ingrese los resultados del examen cuando estén disponibles"><?php echo htmlspecialchars($orden['resultados']); ?></textarea>
                    <div class="form-text">
                        Complete este campo cuando el estado sea "Realizado" o "Entregado"
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-warning text-white me-md-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Orden
                    </button>
                    <a href="../indexOrdenExamenClinico.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
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