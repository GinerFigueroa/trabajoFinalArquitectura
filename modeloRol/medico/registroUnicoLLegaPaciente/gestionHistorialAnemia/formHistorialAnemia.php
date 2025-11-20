<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/HistorialAnemiaPacienteDAO.php');

class formHistorialAnemia extends pantalla
{
    public function formHistorialAnemiaShow()
    {
        $this->cabeceraShow("Gestión de Historial de Anemia y Antecedentes");

        $objHistorial = new HistorialAnemiaPacienteDAO();
        $listaHistoriales = $objHistorial->obtenerTodosHistoriales();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-medical-fill me-2"></i>Historial de Anemia y Antecedentes Médicos</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar por paciente o DNI..." id="inputBusqueda">
                        <button class="btn btn-outline-secondary" type="button" onclick="buscarHistoriales()">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="./agregarHistorialAnemia/indexAgregarHistorialAnemia.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Historial
                    </a>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <?php 
            $estadisticas = $objHistorial->obtenerEstadisticasFactoresRiesgo();
            if ($estadisticas && $estadisticas['total_pacientes'] > 0) { ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <strong>Total:</strong> <?php echo $estadisticas['total_pacientes']; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>Con Tumor:</strong> <?php echo $estadisticas['con_tumor']; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>Con Hemorragia:</strong> <?php echo $estadisticas['con_hemorragia']; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>Fumadores:</strong> <?php echo $estadisticas['fumadores']; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>Embarazadas:</strong> <?php echo $estadisticas['embarazadas']; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>Lactancia:</strong> <?php echo $estadisticas['en_lactancia']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (DNI)</th>
                            <th>Alergias</th>
                            <th>Enfermedades Crónicas</th>
                            <th>Medicación</th>
                            <th>Factores Riesgo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyHistoriales">
                        <?php if (count($listaHistoriales) > 0) {
                            foreach ($listaHistoriales as $historial) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($historial['anamnesis_id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($historial['nombre_paciente']); ?></strong><br>
                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($historial['dni']); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($historial['alergias'])) {
                                            echo htmlspecialchars($historial['alergias']);
                                        } else {
                                            echo '<span class="text-muted">Ninguna registrada</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $enfermedades = [];
                                        if (!empty($historial['enfermedades_cardiacas'])) $enfermedades[] = 'Cardíacas';
                                        if (!empty($historial['enfermedades_pulmonares'])) $enfermedades[] = 'Pulmonares';
                                        if (!empty($historial['enfermedades_renales'])) $enfermedades[] = 'Renales';
                                        if (!empty($historial['enfermedades_hepaticas'])) $enfermedades[] = 'Hepáticas';
                                        if (!empty($historial['enfermedades_neurologicas'])) $enfermedades[] = 'Neurológicas';
                                        if (!empty($historial['enfermedades_endocrinas'])) $enfermedades[] = 'Endocrinas';
                                        
                                        if (count($enfermedades) > 0) {
                                            echo implode(', ', $enfermedades);
                                        } else {
                                            echo '<span class="text-muted">No registradas</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($historial['medicacion'])) {
                                            echo '<span class="badge bg-warning">En tratamiento</span>';
                                        } else {
                                            echo '<span class="text-muted">No registrada</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($historial['ha_tenido_tumor']) { ?>
                                                <span class="badge bg-danger" title="Ha tenido tumor">Tumor</span>
                                            <?php } ?>
                                            <?php if ($historial['ha_tenido_hemorragia']) { ?>
                                                <span class="badge bg-danger" title="Ha tenido hemorragia">Hemorragia</span>
                                            <?php } ?>
                                            <?php if ($historial['fuma']) { ?>
                                                <span class="badge bg-warning" title="Fumador">Fuma</span>
                                            <?php } ?>
                                            <?php if ($historial['esta_embarazada']) { ?>
                                                <span class="badge bg-info" title="Embarazada">Embarazo</span>
                                            <?php } ?>
                                            <?php if ($historial['periodo_lactancia']) { ?>
                                                <span class="badge bg-info" title="En periodo de lactancia">Lactancia</span>
                                            <?php } ?>
                                            <?php if (!$historial['ha_tenido_tumor'] && !$historial['ha_tenido_hemorragia'] && !$historial['fuma'] && !$historial['esta_embarazada'] && !$historial['periodo_lactancia']) { ?>
                                                <span class="text-muted">Sin factores</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="./editarHistorialAnemia/indexEditarHistorialAnemia.php?id=<?php echo htmlspecialchars($historial['anamnesis_id']); ?>" class="btn btn-warning" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="./gererarHistorialAnemiaPDF/indexHistorialAnemiaPDF.php?id=<?php echo htmlspecialchars($historial['anamnesis_id']); ?>" target="_blank" class="btn btn-info" title="Generar PDF">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                            <button class="btn btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($historial['anamnesis_id']); ?>)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-file-medical display-4 text-muted"></i>
                                    <h5 class="text-muted mt-3">No hay historiales de anemia registrados</h5>
                                    <p class="text-muted">Comience registrando el primer historial de antecedentes médicos.</p>
                                    <a href="./agregarHistorialAnemia/indexAgregarHistorialAnemia.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Primer Historial
                                    </a>
                                </td>
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
    if (confirm('¿Está seguro de que desea eliminar este historial de anemia? Esta acción no se puede deshacer.')) {
        window.location.href = `./getHistorialAnemia.php?action=eliminar&id=${id}`;
    }
}

function buscarHistoriales() {
    const termino = document.getElementById('inputBusqueda').value.trim();
    
    if (termino.length === 0) {
        location.reload();
        return;
    }
    
    // Aquí podrías implementar búsqueda AJAX o redirección
    window.location.href = `./getHistorialAnemia.php?action=buscar&termino=${encodeURIComponent(termino)}`;
}

// Búsqueda al presionar Enter
document.getElementById('inputBusqueda').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarHistoriales();
    }
});
</script>

<?php
        $this->pieShow();
    }
}
?>