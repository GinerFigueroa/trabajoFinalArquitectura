<?php
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/citasPacientesDAO.php');

class formConsultarCitas extends pantalla
{
    public function formConsultarCitasShow()
    {
        $this->cabeceraShow("Mis Citas Médicas");

        // Obtener id_usuario del paciente desde la sesión
        $idUsuario = $_SESSION['id_usuario'] ?? null;
        
        if (!$idUsuario) {
            echo '<div class="alert alert-danger">Error: No se pudo identificar al paciente.</div>';
            $this->pieShow();
            return;
        }

        $objCitas = new CitasPacientesDAO();
        $idPaciente = $objCitas->obtenerIdPacientePorUsuario($idUsuario);
        
        if (!$idPaciente) {
            echo '<div class="alert alert-danger">Error: No se encontró información del paciente.</div>';
            $this->pieShow();
            return;
        }

        // Obtener información del paciente, citas y estadísticas
        $infoPaciente = $objCitas->obtenerInfoPaciente($idPaciente);
        $citas = $objCitas->obtenerCitasPorPaciente($idPaciente);
        $citasFuturas = $objCitas->obtenerCitasFuturas($idPaciente);
        $estadisticas = $objCitas->obtenerEstadisticasCitas($idPaciente);
?>

<div class="container mt-4">
    <!-- Header con Información del Paciente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($infoPaciente['nombre_completo']); ?></h4>
                            <p class="mb-1">DNI: <?php echo htmlspecialchars($infoPaciente['dni']); ?></p>
                            <p class="mb-0">Teléfono: <?php echo htmlspecialchars($infoPaciente['telefono']); ?></p>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <h3 class="text-primary"><?php echo $estadisticas['total_citas'] ?? 0; ?></h3>
                                <small>Total Citas</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-warning bg-opacity-25">
                                <h3 class="text-warning"><?php echo $estadisticas['pendientes'] ?? 0; ?></h3>
                                <small>Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-success bg-opacity-25">
                                <h3 class="text-success"><?php echo $estadisticas['confirmadas'] ?? 0; ?></h3>
                                <small>Confirmadas</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-info bg-opacity-25">
                                <h3 class="text-info"><?php echo $estadisticas['completadas'] ?? 0; ?></h3>
                                <small>Completadas</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-danger bg-opacity-25">
                                <h3 class="text-danger"><?php echo $estadisticas['canceladas'] ?? 0; ?></h3>
                                <small>Canceladas</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border rounded p-3 bg-secondary bg-opacity-25">
                                <h3 class="text-secondary"><?php echo $estadisticas['vencidas'] ?? 0; ?></h3>
                                <small>Vencidas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Citas -->
    <?php if (count($citasFuturas) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check"></i> 
                        Próximas Citas Programadas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($citasFuturas as $cita): ?>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?php echo $this->formatearFecha($cita['fecha_cita']); ?></strong>
                                            <span class="badge bg-<?php echo $this->obtenerClaseEstado($cita['estado']); ?>">
                                                <?php echo $cita['estado']; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted"><?php echo substr($cita['hora_cita'], 0, 5); ?> • <?php echo $cita['duracion']; ?> min</small>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($cita['tratamiento_nombre']); ?></h6>
                                        <p class="card-text mb-1">
                                            <i class="bi bi-person-badge"></i> 
                                            <?php echo htmlspecialchars($cita['nombre_medico']); ?>
                                        </p>
                                        <?php if ($cita['especialidad']): ?>
                                            <p class="card-text mb-2">
                                                <i class="bi bi-star"></i> 
                                                <?php echo htmlspecialchars($cita['especialidad']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="card-text">
                                            <strong>S/ <?php echo $cita['tratamiento_costo']; ?></strong>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="btn-group w-100">
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="verDetalleCita(<?php echo $cita['id_cita']; ?>)">
                                                <i class="bi bi-eye"></i> Detalles
                                            </button>
                                            <?php if ($cita['estado'] == 'Pendiente' || $cita['estado'] == 'Confirmada'): ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="cancelarCita(<?php echo $cita['id_cita']; ?>)">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historial Completo de Citas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> 
                        Historial Completo de Citas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($citas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Tratamiento</th>
                                        <th>Médico</th>
                                        <th>Especialidad</th>
                                        <th>Duración</th>
                                        <th>Costo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citas as $cita): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $this->formatearFecha($cita['fecha_cita']); ?></strong><br>
                                                <small class="text-muted"><?php echo substr($cita['hora_cita'], 0, 5); ?></small><br>
                                                <span class="badge bg-secondary"><?php echo $cita['dia_semana']; ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($cita['tratamiento_nombre']); ?>
                                                <?php if ($cita['notas']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($cita['notas'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($cita['nombre_medico']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['especialidad'] ?? 'General'); ?></td>
                                            <td><?php echo $cita['duracion']; ?> min</td>
                                            <td>S/ <?php echo $cita['tratamiento_costo']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $this->obtenerClaseEstado($cita['estado_visual']); ?>">
                                                    <?php echo $cita['estado_visual']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" 
                                                            onclick="verDetalleCita(<?php echo $cita['id_cita']; ?>)"
                                                            data-bs-toggle="tooltip" title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if (($cita['estado'] == 'Pendiente' || $cita['estado'] == 'Confirmada') && strtotime($cita['fecha_hora']) > time()): ?>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="cancelarCita(<?php echo $cita['id_cita']; ?>)"
                                                                data-bs-toggle="tooltip" title="Cancelar cita">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-1 text-muted"></i>
                            <h3 class="text-muted mt-3">No tienes citas registradas</h3>
                            <p class="text-muted">Solicita tu primera cita médica para comenzar.</p>
                            <a href="../solicitarCita/indexSolicitarCita.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Solicitar Primera Cita
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalles de Cita -->
<div class="modal fade" id="modalDetalleCita" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleCitaContent">
                <!-- Contenido cargado via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function verDetalleCita(idCita) {
    // Implementación básica - puedes expandir con AJAX
    alert('Detalles de cita ID: ' + idCita + '\nEn una implementación real, esto cargaría detalles completos via AJAX.');
}

function cancelarCita(idCita) {
    if (confirm('¿Está seguro de que desea cancelar esta cita?\nEsta acción no se puede deshacer.')) {
        window.location.href = './getCitas.php?action=cancelar&id=' + idCita;
    }
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.badge {
    font-size: 0.75em;
}
.table th {
    border-top: none;
    background: #f8f9fa;
}
</style>

<?php
        $this->pieShow();
    }

    private function formatearFecha($fecha)
    {
        return date('d/m/Y', strtotime($fecha));
    }

    private function obtenerClaseEstado($estado)
    {
        switch ($estado) {
            case 'Pendiente': return 'warning';
            case 'Confirmada': return 'success';
            case 'Completada': return 'info';
            case 'Cancelada': return 'danger';
            case 'Vencida': return 'secondary';
            default: return 'secondary';
        }
    }
}
?>