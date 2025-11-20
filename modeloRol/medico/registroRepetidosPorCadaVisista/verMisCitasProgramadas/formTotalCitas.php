<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/misCitasDAO.php');

class formTotalCitas extends pantalla
{
    public function formTotalCitasShow()
    {
        $this->cabeceraShow("Mis Citas Programadas");

        // Obtener id_usuario del médico desde la sesión
        $idUsuario = $_SESSION['id_usuario'] ?? null;
        
        if (!$idUsuario) {
            echo '<div class="alert alert-danger">Error: No se pudo identificar al médico.</div>';
            $this->pieShow();
            return;
        }

        $objCitas = new MisCitasDAO();
        $idMedico = $objCitas->obtenerIdMedicoPorUsuario($idUsuario);
        
        if (!$idMedico) {
            echo '<div class="alert alert-danger">Error: No se encontró información del médico.</div>';
            $this->pieShow();
            return;
        }

        // Obtener citas y estadísticas
        $citas = $objCitas->obtenerCitasPorMedico($idMedico);
        $estadisticas = $objCitas->obtenerEstadisticasCitas($idMedico);
        $citasPorDia = $objCitas->obtenerCitasAgrupadasPorDia($idMedico);

        // Obtener fecha actual y próximos 7 días
        $fechaActual = date('Y-m-d');
        $diasSemana = [];
        
        for ($i = 0; $i < 7; $i++) {
            $fecha = date('Y-m-d', strtotime("+$i days"));
            $diasSemana[] = [
                'fecha' => $fecha,
                'dia_nombre' => $this->obtenerNombreDia($fecha),
                'dia_numero' => date('d', strtotime($fecha)),
                'mes' => $this->obtenerNombreMes($fecha),
                'citas' => $citasPorDia[$fecha] ?? null
            ];
        }
?>

<div class="container mt-4">
    <!-- Header con Estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4><?php echo $estadisticas['total_citas'] ?? 0; ?></h4>
                            <p class="mb-0">Total Citas</p>
                        </div>
                        <div class="col-md-3">
                            <h4><?php echo $estadisticas['pendientes'] ?? 0; ?></h4>
                            <p class="mb-0">Pendientes</p>
                        </div>
                        <div class="col-md-3">
                            <h4><?php echo $estadisticas['confirmadas'] ?? 0; ?></h4>
                            <p class="mb-0">Confirmadas</p>
                        </div>
                        <div class="col-md-3">
                            <h4><?php echo $estadisticas['completadas'] ?? 0; ?></h4>
                            <p class="mb-0">Completadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendario Semanal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week"></i> 
                        Calendario Semanal - Próximos 7 Días
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($diasSemana as $dia): ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card h-100 border-0 shadow-sm 
                                    <?php echo $dia['fecha'] == $fechaActual ? 'border-primary' : ''; ?>">
                                    <div class="card-header text-center py-2 
                                        <?php echo $dia['fecha'] == $fechaActual ? 'bg-primary text-white' : 'bg-light'; ?>">
                                        <strong><?php echo $dia['dia_nombre']; ?></strong><br>
                                        <small><?php echo $dia['dia_numero'] . ' ' . $dia['mes']; ?></small>
                                    </div>
                                    <div class="card-body p-2">
                                        <?php if ($dia['citas']): ?>
                                            <div class="text-center mb-2">
                                                <span class="badge bg-success">
                                                    <?php echo $dia['citas']['total_citas']; ?> citas
                                                </span>
                                            </div>
                                            <?php 
                                            $detalles = explode(';', $dia['citas']['detalles_citas']);
                                            foreach (array_slice($detalles, 0, 3) as $detalle):
                                                list($hora, $paciente, $tratamiento) = explode('|', $detalle);
                                            ?>
                                                <div class="small mb-1 p-1 bg-light rounded">
                                                    <strong><?php echo substr($hora, 0, 5); ?></strong><br>
                                                    <?php echo $paciente; ?>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($detalles) > 3): ?>
                                                <div class="text-center">
                                                    <small class="text-muted">+<?php echo count($detalles) - 3; ?> más</small>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-calendar-x"></i><br>
                                                Sin citas
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista Detallada de Citas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 
                        Lista Detallada de Citas Programadas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($citas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Paciente</th>
                                        <th>DNI</th>
                                        <th>Tratamiento</th>
                                        <th>Duración</th>
                                        <th>Estado</th>
                                        <th>Notas</th>
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
                                            <td><?php echo htmlspecialchars($cita['nombre_paciente']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['dni']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($cita['tratamiento_nombre']); ?><br>
                                                <small class="text-muted">S/ <?php echo $cita['tratamiento_costo']; ?></small>
                                            </td>
                                            <td><?php echo $cita['duracion']; ?> min</td>
                                            <td>
                                                <span class="badge bg-<?php echo $this->obtenerClaseEstado($cita['estado']); ?>">
                                                    <?php echo $cita['estado']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($cita['notas']): ?>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="tooltip" 
                                                            title="<?php echo htmlspecialchars($cita['notas']); ?>">
                                                        <i class="bi bi-chat-text"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            onclick="verDetalleCita(<?php echo $cita['id_cita']; ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if ($cita['estado'] == 'Pendiente'): ?>
                                                        <button class="btn btn-outline-success" 
                                                                onclick="confirmarCita(<?php echo $cita['id_cita']; ?>)">
                                                            <i class="bi bi-check-lg"></i>
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
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No hay citas programadas</h4>
                            <p class="text-muted">No tienes citas pendientes o confirmadas para los próximos días.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalles de Cita -->
<div class="modal fade" id="modalDetalleCita" tabindex="-1">
    <div class="modal-dialog">
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
    // Aquí puedes implementar AJAX para cargar detalles completos
    alert('Detalles de cita ID: ' + idCita);
    // En una implementación real, harías una petición AJAX
}

function confirmarCita(idCita) {
    if (confirm('¿Confirmar esta cita?')) {
        window.location.href = './getCitas.php?action=confirmar&id=' + idCita;
    }
}

function cancelarCita(idCita) {
    if (confirm('¿Cancelar esta cita?')) {
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
}
</style>

<?php
        $this->pieShow();
    }

    private function obtenerNombreDia($fecha)
    {
        $dias = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes', 
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        
        $ingles = date('l', strtotime($fecha));
        return $dias[$ingles] ?? $ingles;
    }

    private function obtenerNombreMes($fecha)
    {
        $meses = [
            'January' => 'Ene', 'February' => 'Feb', 'March' => 'Mar',
            'April' => 'Abr', 'May' => 'May', 'June' => 'Jun',
            'July' => 'Jul', 'August' => 'Ago', 'September' => 'Sep',
            'October' => 'Oct', 'November' => 'Nov', 'December' => 'Dic'
        ];
        
        $ingles = date('F', strtotime($fecha));
        return $meses[$ingles] ?? $ingles;
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
            default: return 'secondary';
        }
    }
}
?>