<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class formRegistrarNuevoRecordatorio extends pantalla
{
    private $objTelegramDAO;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
    }

    public function formRegistrarNuevoRecordatorioShow()
    {
        $this->cabeceraShow("Registrar Nuevo Paciente en Telegram");
        
        // Obtener pacientes disponibles (sin registro en Telegram)
        $pacientesDisponibles = $this->obtenerPacientesSinTelegram();
?>
<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="card-title mb-0">
                                <i class="bi bi-person-plus me-2"></i>
                                Registrar Nuevo Paciente en Telegram
                            </h1>
                            <p class="card-text mb-0 mt-2 opacity-75">
                                Agregar un nuevo paciente al sistema de recordatorios por Telegram
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="../indexRecordatorioPaciente.php" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                                </a>
                                <a href="../editarRecordatorioPaciente/indexEditarRecordatorioPaciente.php" 
                                   class="btn btn-light btn-sm text-dark">
                                    <i class="bi bi-list-ul me-1"></i>Ver Registros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Registro -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-hover">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-telegram me-2"></i>
                        Información del Paciente y Telegram
                    </h5>
                </div>
                <div class="card-body">
                   <form id="formRegistrarPaciente" action="./getRegistrarNuevoRecordatorio.php" method="POST">
                        
                        <!-- Paso 1: Selección de Paciente -->
                        <div id="paso1">
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">
                                    <i class="bi bi-person-check me-2"></i>
                                    Seleccionar Paciente
                                </h6>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        Selecciona un paciente que aún no tenga registro en Telegram
                                    </small>
                                </div>
                                
                                <?php if (empty($pacientesDisponibles)): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No hay pacientes disponibles para registrar. 
                                        <a href="../../../pacienteModule/getPaciente.php?action=nuevo" class="alert-link">
                                            Registrar nuevo paciente primero
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Seleccionar</th>
                                                    <th>Paciente</th>
                                                    <th>DNI</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pacientesDisponibles as $paciente): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="radio" name="idPaciente" 
                                                                   value="<?= $paciente['id_paciente'] ?>" 
                                                                   class="form-check-input" required>
                                                        </td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($paciente['nombre_completo']) ?></strong>
                                                        </td>
                                                        <td>
                                                            <code><?= htmlspecialchars($paciente['dni']) ?></code>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">Disponible</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Paso 2: Información de Telegram -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">
                                <i class="bi bi-telegram me-2"></i>
                                Información de Telegram
                            </h6>
                            
                            <div class="alert alert-warning">
                                <small>
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Asegúrate de que el paciente ya ha iniciado conversación con el bot de Telegram
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="chatId" class="form-label required-field">Chat ID</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="chatId" name="chatId" 
                                                   placeholder="8492891837" required>
                                            <button type="button" class="btn btn-test-telegram" onclick="probarChatId()">
                                                <i class="bi bi-play-circle me-1"></i>Probar
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            ID único del chat en Telegram. Se obtiene cuando el paciente inicia conversación con el bot.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username de Telegram</label>
                                        <div class="input-group">
                                            <span class="input-group-text">@</span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="ginerBush (sin @)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="firstName" class="form-label">Nombre en Telegram</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" 
                                               placeholder="Giner">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lastName" class="form-label">Apellido en Telegram</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" 
                                               placeholder="Figueroa">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmación -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmarRegistro" name="confirmarRegistro" required>
                                <label class="form-check-label" for="confirmarRegistro">
                                    Confirmo que la información es correcta y el paciente ha iniciado conversación con el bot
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" name="btnRegistrarPaciente">
                                <i class="bi bi-check-lg me-1"></i>Registrar Paciente en Telegram
                            </button>
                            <a href="../indexRecordatorioPaciente.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>
                        ¿Cómo obtener el Chat ID?
                    </h6>
                </div>
                <div class="card-body">
                    <ol>
                        <li>El paciente debe buscar el bot de Telegram: <strong>@prueba_paciente_bot</strong></li>
                        <li>Iniciar conversación con el bot enviando el comando <code>/start</code></li>
                        <li>El Chat ID se obtiene automáticamente cuando el paciente envía un mensaje</li>
                        <li>Puedes usar herramientas como <code>@userinfobot</code> para obtener el Chat ID</li>
                    </ol>
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-lightbulb me-1"></i>
                            <strong>Tip:</strong> El paciente debe mantener la conversación con el bot activa para recibir recordatorios.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function probarChatId() {
    const chatId = document.getElementById('chatId').value;
    
    if (!chatId) {
        alert('Por favor, ingresa un Chat ID primero');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Probando...';
    btn.disabled = true;
    
    // Crear formulario temporal para probar el Chat ID
    const formData = new FormData();
    formData.append('chatIdTest', chatId);
    formData.append('action', 'probar_chat_id');
    
    fetch('./getRegistrarNuevoRecordatorio.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.mensaje);
        } else {
            alert('❌ ' + data.mensaje);
        }
    })
    .catch(error => {
        alert('❌ Error de conexión: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php
        $this->pieShow();
    }

    /**
     * Obtener pacientes que no tienen registro en Telegram - CORREGIDO
     */
    private function obtenerPacientesSinTelegram()
    {
        try {
            // Usar el método existente del DAO en lugar de crear nueva conexión
            return $this->objTelegramDAO->obtenerPacientesSinTelegram();
            
        } catch (Exception $e) {
            error_log("Error en obtenerPacientesSinTelegram: " . $e->getMessage());
            return [];
        }
    }
}
?>