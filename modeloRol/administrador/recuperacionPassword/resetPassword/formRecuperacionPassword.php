<?php
// recuperacionPassword/resetPassword/formResetPassword.php
// 🧱 TEMPLATE METHOD + 🎛️ STATE

include_once("../../../../shared/pantalla.php");

class formResetPassword extends pantalla {
    
    public function formResetPasswordShow($token) {
        $this->cabeceraShow("Restablecer Contraseña", "bg-success");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 1rem;
            border: none;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
        .btn {
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white text-center py-4">
                        <i class="bi bi-shield-lock display-4 d-block mb-2"></i>
                        <h2 class="h4 mb-0">Nueva Contraseña</h2>
                        <p class="small mb-0 mt-2">Crea una contraseña segura para tu cuenta</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- 🎛️ STATE PATTERN - Indicador de seguridad -->
                        <div id="passwordStrength" class="progress mb-3 d-none" style="height: 5px;">
                            <div id="passwordStrengthBar" class="progress-bar" role="progressbar"></div>
                        </div>

                        <div id="stateMessage" class="alert alert-info d-none">
                            <i class="bi bi-info-circle"></i>
                            <span id="stateMessageText"></span>
                        </div>

                        <form action="./getResetPassword.php" method="POST" id="formReset">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="mb-3">
                                <label for="nueva_clave" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Nueva Contraseña
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="nueva_clave" 
                                       name="nueva_clave" 
                                       required
                                       minlength="8"
                                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                                       title="Mínimo 8 caracteres, una mayúscula, una minúscula y un número">
                                <div class="form-text">
                                    Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmar_clave" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-2"></i>Confirmar Contraseña
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="confirmar_clave" 
                                       name="confirmar_clave" 
                                       required>
                                <div class="form-text" id="passwordMatchText">
                                    Las contraseñas deben coincidir.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        name="btnResetPassword" 
                                        class="btn btn-success btn-lg fw-semibold">
                                    <i class="bi bi-check-lg me-2"></i>Establecer Nueva Contraseña
                                </button>
                                
                                <a href="../../index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                                </a>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-lightbulb me-2"></i>Consejos de Seguridad
                            </h6>
                            <ul class="small mb-0 ps-3">
                                <li>Usa una combinación de letras, números y símbolos</li>
                                <li>Evita información personal fácil de adivinar</li>
                                <li>No reutilices contraseñas de otros servicios</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 🎛️ STATE PATTERN - Validación de contraseña en tiempo real
        document.getElementById('nueva_clave').addEventListener('input', function(e) {
            checkPasswordStrength(e.target.value);
        });

        document.getElementById('confirmar_clave').addEventListener('input', function(e) {
            checkPasswordMatch();
        });

        document.getElementById('formReset').addEventListener('submit', function(e) {
            const password = document.getElementById('nueva_clave').value;
            const confirmPassword = document.getElementById('confirmar_clave').value;
            
            if (!isPasswordStrong(password)) {
                e.preventDefault();
                showStateMessage('❌ La contraseña no cumple con los requisitos de seguridad', 'danger');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showStateMessage('❌ Las contraseñas no coinciden', 'danger');
                return false;
            }
            
            showStateMessage('⏳ Estableciendo nueva contraseña...', 'info');
            return true;
        });

        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            const bar = document.getElementById('passwordStrengthBar');
            
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;
            
            strengthBar.classList.remove('d-none');
            bar.style.width = strength + '%';
            
            if (strength < 50) {
                bar.className = 'progress-bar bg-danger';
            } else if (strength < 75) {
                bar.className = 'progress-bar bg-warning';
            } else {
                bar.className = 'progress-bar bg-success';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('nueva_clave').value;
            const confirmPassword = document.getElementById('confirmar_clave').value;
            const matchText = document.getElementById('passwordMatchText');
            
            if (confirmPassword === '') {
                matchText.innerHTML = 'Las contraseñas deben coincidir.';
                matchText.className = 'form-text';
            } else if (password === confirmPassword) {
                matchText.innerHTML = '✅ Las contraseñas coinciden';
                matchText.className = 'form-text text-success fw-semibold';
            } else {
                matchText.innerHTML = '❌ Las contraseñas no coinciden';
                matchText.className = 'form-text text-danger fw-semibold';
            }
        }

        function isPasswordStrong(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            
            return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers;
        }

        function showStateMessage(message, type) {
            const stateMessage = document.getElementById('stateMessage');
            const stateText = document.getElementById('stateMessageText');
            
            stateMessage.className = `alert alert-${type} d-block`;
            stateText.textContent = message;
        }
    </script>
</body>
</html>

<?php
        $this->pieShow();
    }
}
?>