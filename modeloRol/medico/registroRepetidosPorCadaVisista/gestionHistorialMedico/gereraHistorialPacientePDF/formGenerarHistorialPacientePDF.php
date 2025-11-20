<?php
include_once("../../../../../shared/pantalla.php");
include_once("../../../../../modelo/HistorialClinicopdfDAO.php"); // DAO unificado

class formGenerarHistorialPacientePDF extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new HistorialClinicoDAO();
    }

    public function formGenerarHistorialPacientePDFShow()
    {
        $this->cabeceraShow("Generar Historial Clínico Completo - Clínica González");

        // Obtener pacientes con historial clínico
        $pacientes = $this->objDAO->obtenerPacientesConHistorial();
?>
    <!-- Encabezado Hospitalario -->
    <div class="hospital-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <i class="bi bi-hospital-fill display-1"></i>
                </div>
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">CLÍNICA GONZÁLEZ</h1>
                    <p class="lead mb-0">
                        <i class="bi bi-award-fill"></i> 90 años cuidando tu salud y la de los tuyos
                    </p>
                    <small>
                        <i class="bi bi-telephone-fill"></i> WSP 997584512 | 
                        <i class="bi bi-globe"></i> www.clinicagonzalez.com
                    </small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="badge bg-light text-dark p-2">
                        <i class="bi bi-shield-check"></i> 40+ Especialidades
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-white py-3">
                        <div class="logo-section">
                            <i class="bi bi-file-medical-fill hospital-logo"></i>
                            <h3 class="text-center mb-0" style="color: #1e3c72;">
                                GENERAR HISTORIAL CLÍNICO COMPLETO
                            </h3>
                            <p class="text-muted mb-0">
                                Sistema Integrado de Gestión de Historias Clínicas
                            </p>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Información del Sistema -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Sistema de Historial Clínico</h6>
                                    <p class="mb-0">
                                        Seleccione un paciente para generar su historial clínico completo incluyendo:
                                        <strong>Anamnesis, Registros Médicos, Evoluciones y Exámenes</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Pacientes -->
                        <div class="mb-4">
                            <h4 class="section-title">
                                <i class="bi bi-people-fill me-2"></i>Pacientes con Historial Clínico
                            </h4>
                            
                            <?php if (count($pacientes) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($pacientes as $paciente): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card patient-card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0 text-primary">
                                                            <?php echo htmlspecialchars($paciente['nombre_completo']); ?>
                                                        </h6>
                                                        <span class="badge bg-primary">HC-<?php echo htmlspecialchars($paciente['historia_clinica_id']); ?></span>
                                                    </div>
                                                    
                                                    <p class="card-text small mb-2">
                                                        <i class="bi bi-person-badge me-1"></i>
                                                        DNI: <?php echo htmlspecialchars($paciente['dni']); ?>
                                                    </p>
                                                    
                                                    <p class="card-text small mb-3">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        Creado: <?php echo date('d/m/Y', strtotime($paciente['fecha_creacion'])); ?>
                                                    </p>

                                                    <div class="d-grid gap-2">
                                                        <a href="./controlGenerarHistorialPacientePDF.php?action=generar&hc_id=<?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>" 
                                                           class="btn btn-hospital btn-sm" target="_blank">
                                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                                            Generar PDF Completo
                                                        </a>
                                                        
                                                        <a href="./controlGenerarHistorialPacientePDF.php?action=preview&hc_id=<?php echo htmlspecialchars($paciente['historia_clinica_id']); ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-eye-fill me-1"></i>
                                                            Vista Previa
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay pacientes con historial clínico registrado</h5>
                                    <p class="text-muted">Los historiales clínicos aparecerán aquí una vez creados.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Estadísticas Rápidas -->
                        <?php if (count($pacientes) > 0): ?>
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white text-center">
                                        <div class="card-body py-3">
                                            <i class="bi bi-people-fill display-6"></i>
                                            <h4 class="mt-2"><?php echo count($pacientes); ?></h4>
                                            <p class="mb-0">Pacientes Activos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white text-center">
                                        <div class="card-body py-3">
                                            <i class="bi bi-file-medical display-6"></i>
                                            <h4 class="mt-2"><?php echo $this->objDAO->obtenerTotalRegistros(); ?></h4>
                                            <p class="mb-0">Registros Médicos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white text-center">
                                        <div class="card-body py-3">
                                            <i class="bi bi-clipboard-pulse display-6"></i>
                                            <h4 class="mt-2"><?php echo $this->objDAO->obtenerTotalEvoluciones(); ?></h4>
                                            <p class="mb-0">Evoluciones</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white text-center">
                                        <div class="card-body py-3">
                                            <i class="bi bi-file-text display-6"></i>
                                            <h4 class="mt-2"><?php echo $this->objDAO->obtenerTotalAnamnesis(); ?></h4>
                                            <p class="mb-0">Anamnesis</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <a href="../indexHistorialMedico.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-2"></i>Volver al Historial Médico
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Actualizado: <?php echo date('d/m/Y H:i'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hospital-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .hospital-logo {
            font-size: 3rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        .patient-card {
            border-left: 4px solid #1e3c72;
            transition: all 0.3s ease;
        }
        .patient-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        .section-title {
            color: #1e3c72;
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
<?php
        $this->pieShow();
    }
}
?>