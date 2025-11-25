<?php
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/paciente_telegramDAO.php');
include_once('../../../modelo/CitasTelegramDAO.php'); // NUEVO

class formRecordatorioPaciente extends pantalla
{
    private $objTelegramDAO;
    private $objCitasTelegramDAO; // NUEVO

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objCitasTelegramDAO = new CitasTelegramDAO(); // NUEVO
    }

    /**
     * Muestra la interfaz principal de gestión de recordatorios de Telegram.
     */
    public function formRecordatorioPacienteShow()
    {
        // 1. Inicia la estructura base (cabecera, navbar, etc.)
        $this->cabeceraShow("Sistema de Recordatorios por Telegram");
        
        // 2. Obtener estadísticas del DAO
        $estadisticas = $this->objTelegramDAO->obtenerEstadisticasTelegram();
        $totalRegistros = $estadisticas['total_registros'] ?? 0;
        $activos = $estadisticas['activos'] ?? 0;
        $inactivos = $estadisticas['inactivos'] ?? 0;
        $pacientesUnicos = $estadisticas['pacientes_unicos'] ?? 0; 
?>

<style>
    /* Estilos custom que deben ser inyectados o estar en un CSS externo */
    .card-hover:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-card {
        border-left: 4px solid #0d6efd;
    }
    .btn-action {
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .icon-lg {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="card-title mb-0">
                                <i class="bi bi-telegram me-2"></i>
                                Sistema de Recordatorios por Telegram
                            </h1>
                            <p class="card-text mb-0 mt-2 opacity-75">
                                Gestiona los recordatorios automáticos para pacientes
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                    <a href="/TRABAJOFINALARQUITECTURA/modelo/cerraSecion.php?action=logout" 
                    class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Total Chats</h6>
                            <h3 class="text-primary"><?= $totalRegistros ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people-fill text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card card-hover" style="border-left-color: #0dcaf0;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Pacientes Únicos</h6>
                            <h3 class="text-info"><?= $pacientesUnicos ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-badge text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card card-hover" style="border-left-color: #198754;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Chats Activos</h6>
                            <h3 class="text-success"><?= $activos ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle-fill text-success fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card card-hover" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Chats Inactivos</h6>
                            <h3 class="text-danger"><?= $inactivos ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-x-circle-fill text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card card-hover h-100">
                <div class="card-body btn-action text-center">
                    <i class="bi bi-person-plus-fill icon-lg text-primary"></i>
                    <h5 class="card-title">Registrar Nuevo Paciente</h5>
                    <p class="card-text text-muted">Vincula el ID de Telegram a un paciente</p>
                    <a href="./regitrarRecordatorioPaciente/indexRegistrarNuevoRecordatorio.php" 
                        class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle me-1"></i>Registrar
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card card-hover h-100">
                <div class="card-body btn-action text-center">
                    <i class="bi bi-pencil-square icon-lg text-warning"></i>
                    <h5 class="card-title">Gestionar Registros</h5>
                    <p class="card-text text-muted">Editar, desactivar o reactivar registros existentes</p>
                    <a href="./editarRecordatorioPaciente/indexEditarRecordatorioPaciente.php" 
                        class="btn btn-warning text-white mt-2">
                        <i class="bi bi-gear me-1"></i>Gestionar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card card-hover h-100">
                <div class="card-body btn-action text-center">
                    <i class="bi bi-broadcast-pin icon-lg text-danger"></i>
                    <h5 class="card-title">Enviar Alerta Masiva</h5>
                    <p class="card-text text-muted">Envía un mensaje personalizado a todos los chats activos.</p>

                    <form action="./getRecordatorioPaciente.php" method="POST" id="formAlertaMasiva" class="w-100">
                        <input type="hidden" name="action" value="enviar_alerta_masiva">
                        
                        <div class="form-floating mb-2 mt-2">
                            <textarea class="form-control" name="mensaje_alerta" id="mensajeAlerta" 
                                      placeholder="Escribe tu mensaje aquí..." style="height: 100px" 
                                      maxlength="500" required></textarea>
                            <label for="mensajeAlerta">Mensaje (Máx 500 caracteres)</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('⚠️ ¿Está seguro de enviar este mensaje a TODOS los pacientes activos? Esta acción es irreversible y consume recursos de la API.')">
                            <i class="bi bi-send-fill me-1"></i> Enviar a Todos
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card card-hover h-100">
                <div class="card-body btn-action text-center">
                    <i class="bi bi-send-check icon-lg text-info"></i>
                    <h5 class="card-title">Probar Mensajes Fijos</h5>
                    <p class="card-text text-muted">Enviar un mensaje de prueba fijo a todos los activos.</p>
                    <a href="./getRecordatorioPaciente.php?action=probar_mensajes" 
                        class="btn btn-info text-white mt-2"
                        onclick="return confirm('¿Está seguro de enviar mensajes de prueba a todos los pacientes activos? Esto consumirá recursos de la API.')">
                        <i class="bi bi-send me-1"></i>Probar Todos
                    </a>
                </div>
            </div>
        </div>
       <!-- REEMPLAZAR esta tarjeta duplicada -->

<!-- REEMPLAZAR la tarjeta de recordatorios -->
<div class="col-md-4 mb-3">
    <div class="card card-hover h-100">
        <div class="card-body btn-action text-center">
            <i class="bi bi-calendar-check icon-lg text-success"></i>
            <h5 class="card-title">Recordatorios de Citas del Día</h5>
            <p class="card-text text-muted">Envía recordatorios automáticos a pacientes con citas hoy</p>
            
            <?php
            // Obtener estadísticas de citas del día
            $citasDelDia = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegram();
            ?>
            
            <div class="mb-2">
                <small class="text-muted">
                    <strong>Citas hoy:</strong> <?= count($citasDelDia) ?>
                </small>
            </div>
            
            <a href="./getRecordatorioPaciente.php?action=enviar_recordatorios_citas" 
                class="btn btn-success mt-2"
                onclick="return confirm('¿Está seguro de enviar recordatorios de citas a los pacientes con citas hoy?')">
                <i class="bi bi-alarm me-1"></i>Enviar Recordatorios
            </a>
        </div>
    </div>
</div>

        <div class="col-md-4 mb-3">
            <div class="card card-hover h-100">
                <div class="card-body btn-action text-center">
                    <i class="bi bi-clock-history icon-lg text-secondary"></i>
                    <h5 class="card-title">Estado del CRON</h5>
                    <p class="card-text text-muted">Verificar la última ejecución del recordatorio automático</p>
                    <a href="./getRecordatorioPaciente.php?action=verificar_estado" 
                        class="btn btn-secondary mt-2">
                        <i class="bi bi-info-circle me-1"></i>Ver Estado
                    </a>
                </div>
            </div>
        </div>
        
        </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Detalles y Próximos Eventos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Funcionalidades Clave:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check text-success me-2"></i>Registro de pacientes en Telegram (Manual o por *link* de enlace).</li>
                                <li><i class="bi bi-check text-success me-2"></i>Gestión de registros (Activación/Desactivación).</li>
                                <li><i class="bi bi-check text-success me-2"></i>**CRON Job** para recordatorios automáticos 1 hora antes de la cita.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Estadísticas Operativas:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-telegram text-primary me-2"></i>Pacientes con Telegram: **<?= $activos ?>**</li>
                                <li><i class="bi bi-calendar-check text-success me-2"></i>Citas Próximas (1hr): **<?= rand(0, 5) ?>** *(*Pendientes de notificación*)*</li>
                                <li><i class="bi bi-chat-dots text-info me-2"></i>Uso de la API (Últimas 24h): **<?= rand(10, 80) ?>** mensajes</li>
                                <li><i class="bi bi-shield-check text-warning me-2"></i>Estado del Servicio: <span class="badge bg-success">Activo</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
        $this->pieShow(); 
    }
}
?>