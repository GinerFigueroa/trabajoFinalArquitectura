<?php
session_start();
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/CitasDAO.php');
include_once('../../../modelo/paciente_telegramDAO.php');

class formRecordatorioPaciente extends pantalla
{
    private $objCitasDAO;
    private $objTelegramDAO;

    public function __construct()
    {
        $this->objCitasDAO = new CitasDAO();
        $this->objTelegramDAO = new PacienteTelegramDAO();
    }

    public function formRecordatorioPacienteShow()
    {
        $this->cabeceraShow("Gestión de Recordatorios - Panel Principal");
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-bell-fill me-2"></i>
                        Sistema de Gestión de Recordatorios
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Estadísticas rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-people"></i></h5>
                                    <h4><?= $this->obtenerTotalPacientes() ?></h4>
                                    <p class="mb-0">Total Pacientes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-telegram"></i></h5>
                                    <h4><?= $this->obtenerPacientesConTelegram() ?></h4>
                                    <p class="mb-0">Con Telegram</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-calendar-check"></i></h5>
                                    <h4><?= $this->obtenerCitasHoy() ?></h4>
                                    <p class="mb-0">Citas Hoy</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-clock-history"></i></h5>
                                    <h4><?= $this->obtenerRecordatoriosPendientes() ?></h4>
                                    <p class="mb-0">Recordatorios Pendientes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-send-check me-2"></i>
                                        Envío de Recordatorios
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <p>Envíe recordatorios manuales a pacientes con citas programadas</p>
                                    <a href="../recordatorioPacienteParaSuCitas/indexRecordatorio.php" 
                                       class="btn btn-success btn-lg">
                                        <i class="bi bi-send-fill me-2"></i>
                                        Ir a Envío de Recordatorios
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Gestión de Telegram
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <p>Registre y gestione las cuentas de Telegram de sus pacientes</p>
                                    <div class="d-grid gap-2">
                                        <a href="../registrarRecordatorioPaciente/indexRegistrarNuevoRecordatorio.php" 
                                           class="btn btn-primary">
                                            <i class="bi bi-person-add me-2"></i>
                                            Registrar Nuevo
                                        </a>
                                        <a href="../editarRecordatorioPaciente/indexRegistrarRecordatorioPaciente.php" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-pencil-square me-2"></i>
                                            Gestionar Registros
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información rápida -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h6 class="mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Información del Sistema
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pacientes registrados en Telegram
                                            <span class="badge bg-success rounded-pill">
                                                <?= $this->obtenerPacientesConTelegram() ?>
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pacientes sin Telegram
                                            <span class="badge bg-danger rounded-pill">
                                                <?= $this->obtenerPacientesSinTelegram() ?>
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Citas para hoy
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $this->obtenerCitasHoy() ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary">
                                    <h6 class="mb-0">
                                        <i class="bi bi-lightning-charge me-2"></i>
                                        Acciones Rápidas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-sm" onclick="ejecutarRecordatoriosAutomaticos()">
                                            <i class="bi bi-robot me-2"></i>
                                            Ejecutar Recordatorios Automáticos
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="probarConexionTelegram()">
                                            <i class="bi bi-telegram me-2"></i>
                                            Probar Conexión Telegram
                                        </button>
                                        <button class="btn btn-outline-info btn-sm" onclick="verReportes()">
                                            <i class="bi bi-graph-up me-2"></i>
                                            Ver Reportes de Envíos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ejecutarRecordatoriosAutomaticos() {
    if (confirm('¿Ejecutar recordatorios automáticos para citas en 1 hora?')) {
        window.open('../recordatorioPacienteParaSuCitas/controlRecordatorio.php?auto=telegram', '_blank');
    }
}

function probarConexionTelegram() {
    alert('Función de prueba de conexión Telegram - En desarrollo');
}

function verReportes() {
    alert('Módulo de reportes - En desarrollo');
}
</script>
<?php
        $this->pieShow();
    }

    private function obtenerTotalPacientes()
    {
        $sql = "SELECT COUNT(*) as total FROM pacientes p 
                JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE u.activo = 1";
        $resultado = $this->objCitasDAO->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    private function obtenerPacientesConTelegram()
    {
        $sql = "SELECT COUNT(*) as total FROM paciente_telegram WHERE activo = 1";
        $resultado = $this->objCitasDAO->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    private function obtenerPacientesSinTelegram()
    {
        return count($this->objTelegramDAO->obtenerPacientesSinTelegram());
    }

    private function obtenerCitasHoy()
    {
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM citas WHERE DATE(fecha_hora) = ? AND estado IN ('Pendiente', 'Confirmada')";
        $stmt = $this->objCitasDAO->connection->prepare($sql);
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        return $total;
    }

    private function obtenerRecordatoriosPendientes()
    {
        $sql = "SELECT COUNT(*) as total FROM citas 
                WHERE fecha_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 HOUR)
                AND estado IN ('Pendiente', 'Confirmada')";
        $resultado = $this->objCitasDAO->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }
}
?>