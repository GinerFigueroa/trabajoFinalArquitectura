<?php
include_once('../../../shared/pantalla.php');

class formRecuperarPasword extends pantalla
{
    public function formRecuperarPaswordShow()
    {
        $this->cabeceraShow("Recuperar Contraseña - Sistema de Clínica");
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        Recuperar Contraseña
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-telegram display-4 text-primary"></i>
                        <h5 class="mt-3">Recuperación por Telegram</h5>
                        <p class="text-muted">
                            Ingresa tu correo electrónico para recibir un código de verificación por Telegram
                        </p>
                    </div>
                    
                    <form action="./getRecuperarPasword.php" method="POST" id="formRecuperacion">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico:
                            </label>
                            <input type="email" class="form-control form-control-lg" 
                                   id="email" name="email" 
                                   placeholder="tu@email.com" required>
                            <div class="form-text">
                                Debe ser el mismo email registrado en tu cuenta de paciente
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="btnSolicitarCodigo" 
                                    class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Enviar Código por Telegram
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="../../../index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Volver al Inicio de Sesión
                        </a>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="alert alert-info mt-4" role="alert">
                        <h6><i class="bi bi-info-circle me-2"></i>¿Cómo funciona?</h6>
                        <ul class="mb-0 small">
                            <li>Ingresa tu email registrado en el sistema</li>
                            <li>Recibirás un código de 6 dígitos por Telegram</li>
                            <li>Usa ese código para restablecer tu contraseña</li>
                            <li>El código expira en 15 minutos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
?>