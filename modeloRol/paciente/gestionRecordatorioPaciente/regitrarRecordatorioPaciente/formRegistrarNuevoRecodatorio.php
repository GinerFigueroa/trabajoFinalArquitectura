<?php
session_start();
include_once('../../../shared/pantalla.php');
include_once('../../../modelo/CitasDAO.php');
include_once('../../../modelo/paciente_telegramDAO.php');

class formRegistrarNuevoRecordatorio extends pantalla
{
    private $objCitasDAO;
    private $objTelegramDAO;

    public function __construct()
    {
        $this->objCitasDAO = new CitasDAO();
        $this->objTelegramDAO = new PacienteTelegramDAO();
    }

    public function formRegistrarNuevoRecordatorioShow()
    {
        $this->cabeceraShow("Registrar Paciente en Telegram");
        
        // Obtener pacientes sin Telegram
        $pacientesSinTelegram = $this->objTelegramDAO->obtenerPacientesSinTelegram();
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        Registrar Paciente en Telegram
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($pacientesSinTelegram)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-check-circle me-2"></i>
                            ¡Todos los pacientes activos ya tienen Telegram registrado!
                        </div>
                        <div class="text-center">
                            <a href="../editarRecordatorioPaciente/indexRegistrarRecordatorioPaciente.php" 
                               class="btn btn-primary me-2">
                                <i class="bi bi-pencil-square me-2"></i>
                                Gestionar Registros Existentes
                            </a>
                            <a href="../gestionRecordatorioPaciente/indexRecordatorioPaciente.php" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Volver al Panel
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            Seleccione un paciente y complete la información de su cuenta de Telegram.
                        </div>

                        <form name="formRegistrarTelegram" method="POST" action="./getRegistrarNuevoRecordatorio.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0">
                                                <i class="bi bi-person me-2"></i>
                                                Seleccionar Paciente
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="idPaciente" class="form-label fw-bold">Paciente:</label>
                                                <select class="form-control" id="idPaciente" name="idPaciente" required>
                                                    <option value="">-- Seleccione un paciente --</option>
                                                    <?php foreach ($pacientesSinTelegram as $paciente): ?>
                                                        <option value="<?= $paciente['id_paciente'] ?>" 
                                                                data-dni="<?= $paciente['dni'] ?>"
                                                                data-email="<?= $paciente['email'] ?>"
                                                                data-telefono="<?= $paciente['telefono'] ?>">
                                                            <?= $paciente['nombre_paciente'] ?> - DNI: <?= $paciente['dni'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div id="infoPaciente" class="alert alert-secondary d-none">
                                                <h6>Información del Paciente:</h6>
                                                <p class="mb-1"><strong>DNI:</strong> <span id="infoDni">-</span></p>
                                                <p class="mb-1"><strong>Email:</strong> <span id="infoEmail">-</span></p>
                                                <p class="mb-0"><strong>Teléfono:</strong> <span id="infoTelefono">-</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="bi bi-telegram me-2"></i>
                                                Información de Telegram
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="chatId" class="form-label fw-bold">Chat ID:</label>
                                                <input type="number" class="form-control" id="chatId" name="chatId" 
                                                       placeholder="Ej: 123456789" required>
                                                <div class="form-text">
                                                    El Chat ID es un número único que identifica la conversación con el bot.
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="usernameTelegram" class="form-label">Username de Telegram:</label>
                                                <input type="text" class="form-control" id="usernameTelegram" name="usernameTelegram" 
                                                       placeholder="Ej: @usuario (opcional)">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="firstName" class="form-label">Nombre:</label>
                                                        <input type="text" class="form-control" id="firstName" name="firstName" 
                                                               placeholder="Nombre en Telegram">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="lastName" class="form-label">Apellido:</label>
                                                        <input type="text" class="form-control" id="lastName" name="lastName" 
                                                               placeholder="Apellido en Telegram">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-warning">
                                            <h5 class="mb-0">
                                                <i class="bi bi-question-circle me-2"></i>
                                                ¿Cómo obtener el Chat ID?
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <ol>
                                                <li>El paciente debe iniciar una conversación con tu bot de Telegram</li>
                                                <li>Visita: <code>https://api.telegram.org/bot&lt;TU_TOKEN&gt;/getUpdates</code></li>
                                                <li>Busca el número en <code>"chat":{"id":123456789}</code></li>
                                                <li>También puedes usar bots como @userinfobot para obtener el Chat ID</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="../gestionRecordatorioPaciente/indexRecordatorioPaciente.php" 
                                           class="btn btn-outline-secondary me-md-2">
                                            <i class="bi bi-arrow-left me-2"></i>
                                            Cancelar
                                        </a>
                                        <button type="submit" name="btnRegistrarTelegram" 
                                                class="btn btn-success btn-lg">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Registrar en Telegram
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPaciente = document.getElementById('idPaciente');
    const infoPaciente = document.getElementById('infoPaciente');
    const infoDni = document.getElementById('infoDni');
    const infoEmail = document.getElementById('infoEmail');
    const infoTelefono = document.getElementById('infoTelefono');

    selectPaciente.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            infoDni.textContent = selectedOption.dataset.dni;
            infoEmail.textContent = selectedOption.dataset.email;
            infoTelefono.textContent = selectedOption.dataset.telefono;
            infoPaciente.classList.remove('d-none');
        } else {
            infoPaciente.classList.add('d-none');
        }
    });
});
</script>
<?php
        $this->pieShow();
    }
}
?>