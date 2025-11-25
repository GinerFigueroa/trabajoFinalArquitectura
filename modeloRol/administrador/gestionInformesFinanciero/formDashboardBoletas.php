<?php
// Archivo: formDashboardBoletas.php

include_once("../../../shared/pantalla.php");

class formDashboardBoletas extends pantalla
{
    public function formDashboardBoletasShow($datos)
    {
        // Método: cabeceraShow (Heredado de pantalla - Parte del Template Method)
        $this->cabeceraShow("Dashboard de Boletas - Análisis Financiero");
        
        // Extracción de datos (Prepara las variables locales para la renderización)
        $estadisticas = $datos['estadisticas']; // Variable local
        $ingresosMensuales = $datos['ingresos_mensuales']; // Variable local
        $distribucionTipo = $datos['distribucion_tipo']; // Variable local
        $distribucionMetodoPago = $datos['distribucion_metodo_pago']; // Variable local
        $boletasRecientes = $datos['boletas_recientes']; // Variable local
        $aniosDisponibles = $datos['anios_disponibles']; // Variable local
        $tendencia = $datos['tendencia']; // Variable local
        $filtros = $datos['filtros']; // Variable local
?>

<div class="container-fluid mt-4">
    <!-- Header del Dashboard -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-graph-up me-2"></i>Dashboard Financiero
        </h1>
        <div>
            <a href="../gestionUsuario/indexGestionUsuario.php" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Volver a Reportes
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="./indexDashboardBoletas.php" class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                           value="<?php echo htmlspecialchars($filtros['fecha_inicio'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                           value="<?php echo htmlspecialchars($filtros['fecha_fin'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="anio" class="form-label">Año</label>
                    <select class="form-select" id="anio" name="anio">
                        <?php foreach ($aniosDisponibles as $anio): ?>
                            <option value="<?php echo $anio['anio']; ?>" 
                                <?php echo ($anio['anio'] == $filtros['anio']) ? 'selected' : ''; ?>>
                                <?php echo $anio['anio']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter-circle me-1"></i>Aplicar Filtros
                    </button>
                    <a href="./indexDashboardBoletas.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <!-- Total Boletas -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Boletas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($estadisticas['total_boletas'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos Totales -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ingresos Totales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ <?php echo number_format($estadisticas['ingreso_total'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promedio por Boleta -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Promedio/Boleta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ <?php echo number_format($estadisticas['promedio_boleta'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pacientes Únicos -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pacientes Únicos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($estadisticas['pacientes_unicos'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boleta Mínima -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Boleta Mínima</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ <?php echo number_format($estadisticas['boleta_minima'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boleta Máxima -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Boleta Máxima</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ <?php echo number_format($estadisticas['boleta_maxima'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de Tendencia -->
    <?php if ($tendencia['variacion'] != 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-<?php echo $tendencia['variacion'] >= 0 ? 'success' : 'warning'; ?> d-flex align-items-center">
                <i class="bi bi-<?php echo $tendencia['variacion'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>-circle-fill me-2 fs-4"></i>
                <div>
                    <strong>Tendencia:</strong> 
                    Los ingresos del mes actual 
                    <strong><?php echo $tendencia['variacion'] >= 0 ? 'aumentaron' : 'disminuyeron'; ?></strong> 
                    en un <strong><?php echo number_format(abs($tendencia['variacion']), 2); ?>%</strong> 
                    comparado con el mes anterior.
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gráficas -->
    <div class="row mb-4">
        <!-- Gráfico de Ingresos Mensuales -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calendar-range me-2"></i>
                        Ingresos Mensuales - <?php echo $filtros['anio']; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="ingresosMensualesChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Distribución por Tipo -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>
                        Distribución por Tipo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="distribucionTipoChart" width="400" height="200"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach ($distribucionTipo as $tipo): ?>
                            <span class="mr-2">
                                <i class="bi bi-square-fill" style="color: <?php echo $this->getColorTipo($tipo['tipo']); ?>"></i>
                                <?php echo $tipo['tipo']; ?> (<?php echo $tipo['porcentaje']; ?>%)
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas Adicionales -->
    <div class="row mb-4">
        <!-- Distribución por Método de Pago -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-credit-card me-2"></i>
                        Distribución por Método de Pago
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="metodoPagoChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativa Mes Actual vs Mes Anterior -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bar-chart me-2"></i>
                        Comparativa Mensual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="comparativaMensualChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Boletas Recientes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-2"></i>
                Boletas Más Recientes
            </h6>
            
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th># Boleta</th>
                            <th>Paciente</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Método Pago</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($boletasRecientes) > 0): ?>
                            <?php foreach ($boletasRecientes as $boleta): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($boleta['numero_boleta']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($boleta['paciente']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo strlen($boleta['concepto']) > 50 ? 
                                                substr(htmlspecialchars($boleta['concepto']), 0, 50) . '...' : 
                                                htmlspecialchars($boleta['concepto']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $boleta['tipo'] == 'Factura' ? 'warning' : 'info'; ?>">
                                            <?php echo htmlspecialchars($boleta['tipo']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($boleta['metodo_pago']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        S/ <?php echo number_format($boleta['monto_total'], 2); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($boleta['fecha_emision'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                    No hay boletas registradas en el período seleccionado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para los gráficos
const ingresosMensuales = <?php echo json_encode($ingresosMensuales); ?>;
const distribucionTipo = <?php echo json_encode($distribucionTipo); ?>;
const distribucionMetodoPago = <?php echo json_encode($distribucionMetodoPago); ?>;

// Configuración de colores
const colores = {
    primary: '#4e73df',
    success: '#1cc88a',
    info: '#36b9cc',
    warning: '#f6c23e',
    danger: '#e74a3b',
    secondary: '#858796'
};

// Función para formatear moneda
const formatoMoneda = (valor) => {
    return 'S/ ' + valor.toLocaleString('es-PE', {minimumFractionDigits: 2});
};

// 1. Gráfico de Ingresos Mensuales
const ingresosCtx = document.getElementById('ingresosMensualesChart').getContext('2d');
const ingresosChart = new Chart(ingresosCtx, {
    type: 'line',
    data: {
        labels: ingresosMensuales.map(item => {
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            return meses[item.mes - 1];
        }),
        datasets: [{
            label: 'Ingresos Totales',
            data: ingresosMensuales.map(item => item.total_ingresos),
            borderColor: colores.primary,
            backgroundColor: colores.primary + '20',
            tension: 0.4,
            fill: true,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Ingresos: ' + formatoMoneda(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatoMoneda(value);
                    }
                }
            }
        }
    }
});

// 2. Gráfico de Distribución por Tipo
const tipoCtx = document.getElementById('distribucionTipoChart').getContext('2d');
const tipoChart = new Chart(tipoCtx, {
    type: 'doughnut',
    data: {
        labels: distribucionTipo.map(item => item.tipo),
        datasets: [{
            data: distribucionTipo.map(item => item.monto_total),
            backgroundColor: distribucionTipo.map((item, index) => {
                const coloresTipo = [colores.primary, colores.success, colores.warning, colores.info];
                return coloresTipo[index % coloresTipo.length];
            }),
            borderWidth: 1,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const item = distribucionTipo[context.dataIndex];
                        return `${item.tipo}: ${formatoMoneda(item.monto_total)} (${item.porcentaje}%)`;
                    }
                }
            }
        }
    }
});

// 3. Gráfico de Métodos de Pago
const metodoPagoCtx = document.getElementById('metodoPagoChart').getContext('2d');
const metodoPagoChart = new Chart(metodoPagoCtx, {
    type: 'bar',
    data: {
        labels: distribucionMetodoPago.map(item => item.metodo_pago),
        datasets: [{
            label: 'Monto Total',
            data: distribucionMetodoPago.map(item => item.monto_total),
            backgroundColor: distribucionMetodoPago.map((item, index) => {
                const coloresMetodo = [colores.info, colores.warning, colores.secondary];
                return coloresMetodo[index % coloresMetodo.length];
            }),
            borderColor: distribucionMetodoPago.map(() => '#fff'),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const item = distribucionMetodoPago[context.dataIndex];
                        return `${item.metodo_pago}: ${formatoMoneda(item.monto_total)} (${item.cantidad} transacciones)`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatoMoneda(value);
                    }
                }
            }
        }
    }
});

// 4. Gráfico de Comparativa Mensual
const comparativaCtx = document.getElementById('comparativaMensualChart').getContext('2d');
const comparativaChart = new Chart(comparativaCtx, {
    type: 'bar',
    data: {
        labels: ['Mes Actual', 'Mes Anterior'],
        datasets: [{
            label: 'Ingresos',
            data: [
                ingresosMensuales[ingresosMensuales.length - 1]?.total_ingresos || 0,
                ingresosMensuales[ingresosMensuales.length - 2]?.total_ingresos || 0
            ],
            backgroundColor: [colores.success, colores.secondary],
            borderColor: [colores.success, colores.secondary],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return formatoMoneda(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatoMoneda(value);
                    }
                }
            }
        }
    }
});

// Auto-aplicar filtro de año actual al cargar
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('fecha_inicio').value && !document.getElementById('fecha_fin').value) {
        // Mostrar año actual por defecto
        const añoActual = new Date().getFullYear();
        document.getElementById('anio').value = añoActual;
    }
});
</script>

<?php
        $this->pieShow();
    }

    /**
     * Obtiene color según el tipo de boleta
     */
    private function getColorTipo($tipo)
    {
        $colores = [
            'Boleta' => '#4e73df',
            'Factura' => '#1cc88a'
        ];
        
        return $colores[$tipo] ?? '#858796';
    }
}
?>