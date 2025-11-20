<?php
include_once('../../../shared/pantalla.php');

class formRecuperarPasword extends pantalla
{
    public function formRecuperarPaswordShow()
    {
        $this->cabeceraShow("Recuperación de Contraseña");
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-key-fill me-2"></i>
                        Recuperar Contraseña
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="../img/password-reset.png" alt="Recuperar contraseña" class="img-fluid" style="max-width: 100px;">
                    </div>
                    
                    <p class="text-muted text-center mb-4">
                        Ingrese su correo electrónico registrado. Le enviaremos un enlace seguro para restablecer su contraseña.
                    </p>
                    
                    <form name='recuperarPassword' method='POST' action='./getRecuperarPasword.php'>
                        <div class="mb-3">
                            <label for="txtEmail" class="form-label">Correo Electrónico:</label>
                            <input name='txtEmail' id='txtEmail' type='email' class="form-control" 
                                   placeholder="Ingrese su correo electrónico" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type='submit' name='btnRecuperar' class='btn btn-warning btn-lg'>
                                <i class="bi bi-send-fill me-2"></i>Enviar Enlace de Recuperación
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Volver al Login
                            </a>
                        </div>
                    </form>
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