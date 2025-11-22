<?php
session_start();
include_once('../../../shared/pantalla.php');

// Verificar que el usuario viene del proceso de recuperación
if (!isset($_SESSION['recuperacion_usuario_id'])) {
    include_once('../../../shared/mensajeSistema.php');
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido", 
        "./indexRecuperarPasword.php", 
        "error"
    );
    exit();
}

class formRestablecerPassword extends pantalla
{
    public function formRestablecerPasswordShow()
    {
        $email = $_SESSION['recuperacion_email'] ?? '';
        
        $this->cabeceraShow("Restablecer Contraseña - Sistema de Clínica");
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-key-fill me-2"></i>
                        Restablecer Contraseña
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-check display-4 text-success"></i>
                        <h5 class="mt-3">Verificación por Telegram</h5>
                        <p class="text-muted">
                            Ingresa el código recibido y tu nueva contraseña
                        </p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Código enviado a: <strong><?php echo htmlspecialchars($email); ?></strong>
                    </div>
                    
                    <form action="./procesarRestablecimiento.php" method="POST" id="formRestablecer">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">
                                <i class="bi bi-chat-square-text me-1"></i>Código de Verificación:
                            </label>
                            <input type="text" class="form-control form-control-lg" 
                                   id="codigo" name="codigo" 
                                   placeholder="123456" maxlength="6" required
                                   pattern="[0-9]{6}" title="Ingresa los 6 dígitos recibidos">
                            <div class="form-text">
                                Ingresa el código de 6 dígitos que recibiste por Telegram
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nueva_password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Nueva Contraseña:
                            </label>
                            <input type="password" class="form-control form-control-lg" 
                                   id="nueva_password" name="nueva_password" 
                                   placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmar_password" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña:
                            </label>
                            <input type="password" class="form-control form-control-lg" 
                                   id="confirmar_password" name="confirmar_password" 
                                   placeholder="Repite tu contraseña" required minlength="6">
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="btnRestablecer" 
                                    class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Restablecer Contraseña
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="./indexRecuperarPasword.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Solicitar nuevo código
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formRestablecer').addEventListener('submit', function(e) {
    const password = document.getElementById('nueva_password').value;
    const confirmar = document.getElementById('confirmar_password').value;
    
    if (password !== confirmar) {
        e.preventDefault();
        alert('❌ Las contraseñas no coinciden. Por favor verifica.');
        return false;
    }
});
</script>

<style>
    .card {
        border: none;
        border-radius: 15px;
    }
    .form-control {
        border-radius: 10px;
    }
    .btn {
        border-radius: 10px;
    }
</style>

<?php
        $this->pieShow();
    }
}

$obj = new formRestablecerPassword();
$obj->formRestablecerPasswordShow();
?>