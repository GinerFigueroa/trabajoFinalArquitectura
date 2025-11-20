<?php
session_start();
include_once('../../../shared/pantalla.php');
include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecuperarPasword.php');

class formRestablecerPassword extends pantalla
{
    public function formRestablecerPasswordShow($idUsuario, $datosValidos)
    {
        $this->cabeceraShow("Restablecer Contraseña");
        
        if (!$datosValidos) {
            $this->mostrarError("El enlace de recuperación no es válido o ha expirado.");
            return;
        }
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-lock-fill me-2"></i>
                        Restablecer Contraseña
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center mb-4">
                        Ingrese su nueva contraseña. Asegúrese de que sea segura y fácil de recordar.
                    </p>
                    
                    <form name='restablecerPassword' method='POST' action='./procesarRestablecimiento.php'>
                        <input type="hidden" name="idUsuario" value="<?php echo htmlspecialchars($idUsuario); ?>">
                        <input type="hidden" name="data" value="<?php echo htmlspecialchars($_GET['data'] ?? ''); ?>">
                        
                        <div class="mb-3">
                            <label for="nuevaPassword" class="form-label">Nueva Contraseña:</label>
                            <input name='nuevaPassword' id='nuevaPassword' type='password' class="form-control" 
                                   placeholder="Ingrese nueva contraseña" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmarPassword" class="form-label">Confirmar Contraseña:</label>
                            <input name='confirmarPassword' id='confirmarPassword' type='password' class="form-control" 
                                   placeholder="Confirme la contraseña" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type='submit' name='btnRestablecer' class='btn btn-success btn-lg'>
                                <i class="bi bi-check-circle-fill me-2"></i>Restablecer Contraseña
                            </button>
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
    
    private function mostrarError($mensaje)
    {
        echo '<div class="container mt-5">
                <div class="alert alert-danger text-center">
                    <h4><i class="bi bi-exclamation-triangle-fill"></i> Error</h4>
                    <p>' . $mensaje . '</p>
                    <a href="../index.php" class="btn btn-primary">Volver al Login</a>
                </div>
              </div>';
    }
}

// Procesar la solicitud
$objMensaje = new mensajeSistema();

if (!isset($_GET['data'])) {
    $objMensaje->mensajeSistemaShow("Enlace de recuperación no válido", "../index.php", "error");
    exit();
}

$parametrosCodificados = $_GET['data'];
$datosValidos = controlRecuperarPasword::validarEnlaceRecuperacion($parametrosCodificados);
$idUsuario = controlRecuperarPasword::obtenerIdUsuarioDesdeEnlace($parametrosCodificados);

$objForm = new formRestablecerPassword();
$objForm->formRestablecerPasswordShow($idUsuario, $datosValidos);
?>