<?php
/**
 * Patrón: MVC (Vista)
 * Patrón: Template Method (Uso del Template)
 * Responsabilidad: Generar el HTML del formulario de login.
 */
include_once __DIR__ . '/../shared/pantalla.php';

class formAutenticarUsuario extends pantalla
{
    /**
     * Patrón: Template Method (Paso de la Plantilla)
     * Implementa el contenido específico de la vista utilizando la estructura fija (cabecera/pie)
     * heredada de la clase 'pantalla'.
     */
    public function formAutenticarUsuarioShow()
    {
        // Paso 1 del Template Method: Muestra la cabecera
        $this->cabeceraShow("Autenticación de Usuario");
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Autenticación de Usuario
                    </h4>
                </div>
                <div class="card-body">
                    <form name='autenticarUsuario' method='POST' action='./getUsuario.php'>
                        <div class="text-center mb-4">
                            <img src="../img/login.png" alt="login" class="img-fluid" style="max-width: 100px;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="txtLogin" class="form-label">Login:</label>
                            <input name='txtLogin' id='txtLogin' type='text' class="form-control" 
                                   placeholder="Ingrese su usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="txtPassword" class="form-label">Password:</label>
                            <input name='txtPassword' id='txtPassword' type='password' 
                                   class="form-control" placeholder="Ingrese su contraseña" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type='submit' name='btnAceptar' class='btn btn-primary btn-lg'>
                                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="../modeloRol/administrador/recuperacionPPasword/indexRecuperarPasword.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>¿Olvidó su contraseña?
                            </a>
                                <a href="../modeloRol/administrador/recuperacionPassword/indexRecuperacionPassword.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>¿Olvidó su contraseña?
                            </a>

                            
                        </div>
                         <div class="text-center mt-3">
                            <a href="./registrarUsuario/indexRegitroUsuario.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i> loguearse 
                            </a>
                            

                           
                        </div>
                    </form>
                    </div>
            </div>
        </div>
    </div>
</div>
<?php
        // Paso 2 del Template Method: Muestra el pie de página
        $this->pieShow();
    }
}
?>