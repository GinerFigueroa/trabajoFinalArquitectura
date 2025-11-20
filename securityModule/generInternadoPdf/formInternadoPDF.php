<?php
include_once("../../../../../shared/pantalla.php");
include_once("../../../../../modelo/InternadoDAO.php");

class formInternadoPDF extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new InternadoDAO();
    }

    public function formInternadoPDFShow()
    {
        $this->cabeceraShow("Generar Reporte PDF de Internado");

        $idInternado = $_GET['id'] ?? null;

        if (!$idInternado) {
            echo '<div class="alert alert-danger">ID de internado no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $internado = $this->objDAO->obtenerInternadoPorId($idInternado);

        if (!$internado) {
            echo '<div class="alert alert-danger">Internado no encontrado.</div>';
            $this->pieShow();
            return;
        }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar PDF - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .hospital-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }
        .btn-hospital {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-hospital:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
        }
        .patient-card {
            border-left: 4px solid #1e3c72;
        }
    </style>
</head>
<body>
    <!-- Encabezado Hospitalario -->
    <div class="hospital-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-pdf-fill me-2"></i>Generar Reporte PDF</h4>
                    <small>Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></small>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../indexGestionInternados.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left-circle me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="bi bi-person-circle me-2"></i>
                            <?php echo htmlspecialchars($internado['nombre_paciente'] ?? 'Paciente'); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Información del Internado -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card patient-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Información del Internado</h6>
                                        <p class="mb-1"><strong>ID:</strong> #<?php echo htmlspecialchars($internado['id_internado']); ?></p>
                                        <p class="mb-1"><strong>Ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($internado['fecha_ingreso'])); ?></p>
                                        <p class="mb-1"><strong>Alta:</strong> <?php echo $internado['fecha_alta'] ? date('d/m/Y H:i', strtotime($internado['fecha_alta'])) : 'Pendiente'; ?></p>
                                        <p class="mb-1"><strong>Habitación:</strong> <?php echo htmlspecialchars($internado['habitacion_numero'] ?? 'N/A'); ?></p>
                                        <p class="mb-0"><strong>Estado:</strong> 
                                            <span class="badge bg-<?php echo $this->obtenerClaseEstado($internado['estado']); ?>">
                                                <?php echo htmlspecialchars($internado['estado']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card patient-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Datos Médicos</h6>
                                        <p class="mb-1"><strong>Diagnóstico:</strong></p>
                                        <p class="text-muted small"><?php echo htmlspecialchars($internado['diagnostico_ingreso'] ?? 'No especificado'); ?></p>
                                        <p class="mb-1"><strong>Observaciones:</strong></p>
                                        <p class="text-muted small"><?php echo htmlspecialchars($internado['observaciones'] ?? 'No hay observaciones'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de PDF -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="bi bi-gear-fill me-2"></i>Opciones de Reporte</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <i class="bi bi-file-text display-4 text-primary mb-3"></i>
                                                <h6 class="card-title">Reporte Completo</h6>
                                                <p class="card-text small text-muted">Incluye todos los datos del internado, evolución y tratamientos</p>
                                                <a href="./controlInternadoPDF.php?action=completo&id=<?php echo htmlspecialchars($internado['id_internado']); ?>" 
                                                   class="btn btn-hospital btn-sm w-100" target="_blank">
                                                    <i class="bi bi-download me-1"></i>Descargar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <i class="bi bi-clipboard-pulse display-4 text-success mb-3"></i>
                                                <h6 class="card-title">Resumen Clínico</h6>
                                                <p class="card-text small text-muted">Información médica esencial y evolución del paciente</p>
                                                <a href="./controlInternadoPDF.php?action=resumen&id=<?php echo htmlspecialchars($internado['id_internado']); ?>" 
                                                   class="btn btn-success btn-sm w-100" target="_blank">
                                                    <i class="bi bi-download me-1"></i>Descargar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                  
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle-fill me-2"></i>Información del Sistema</h6>
                                    <p class="mb-0 small">
                                        Los reportes se generan en formato PDF y se descargan automáticamente. 
                                        Asegúrese de tener un lector de PDF instalado en su dispositivo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-shield-lock-fill me-1"></i>
                                    Documento confidencial - Clínica González
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Generado: <?php echo date('d/m/Y H:i'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
        $this->pieShow();
    }

    private function obtenerClaseEstado($estado)
    {
        switch ($estado) {
            case 'Activo': return 'success';
            case 'Alta': return 'primary';
            case 'Derivado': return 'warning';
            case 'Fallecido': return 'danger';
            default: return 'secondary';
        }
    }
}
?>