<?php
// recuperacionPassword/formRecuperacionPassword.php
// 🧱 TEMPLATE METHOD + 🎛️ STATE

include_once("../../../shared/pantalla.php");

class formRecuperacionPassword extends pantalla {
    
    public function formRecuperacionPasswordShow() {
        $this->cabeceraShow("Recuperar Contraseña", "bg-warning");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema Médico</title>
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
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
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
                    <div class="card-header bg-warning text-dark text-center py-4">
                        <i class="bi bi-key-fill display-4 d-block mb-2"></i>
                        <h2 class="h4 mb-0">Recuperar Contraseña</h2>
                        <p class="small mb-0 mt-2">Ingresa tu email para recibir el enlace de recuperación</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- 🎛️ STATE PATTERN - Mensajes de estado -->
                        <div id="stateMessage" class="alert alert-info d-none">
                            <i class="bi bi-info-circle"></i>
                            <span id="stateMessageText"></span>
                        </div>

                        <form action="./getRecuperacionPassword.php" method="POST" id="formRecovery">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="email" 
                                       name="email" 
                                       required
                                       placeholder="tu@email.com"
                                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                       title="Ingresa un email válido">
                                <div class="form-text">
                                    Te enviaremos un enlace seguro para restablecer tu contraseña.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        name="btnSolicitarRecuperacion" 
                                        class="btn btn-warning btn-lg fw-semibold">
                                    <i class="bi bi-send me-2"></i>Enviar Enlace de Recuperación
                                </button>
                                
                                <a href="../index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Volver al Inicio
                                </a>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-shield-check me-2"></i>Seguridad
                            </h6>
                            <ul class="small mb-0 ps-3">
                                <li>El enlace expirará en 1 hora</li>
                                <li>Solo podrás usarlo una vez</li>
                                <li>Revisa tu carpeta de spam si no encuentras el email</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 🎛️ STATE PATTERN - Manejo de estados en cliente
        document.getElementById('formRecovery').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const stateMessage = document.getElementById('stateMessage');
            const stateText = document.getElementById('stateMessageText');

            if (!isValidEmail(email)) {
                e.preventDefault();
                showStateMessage('❌ Por favor, ingresa un email válido', 'danger');
                return false;
            }

            showStateMessage('⏳ Procesando tu solicitud...', 'info');
            return true;
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showStateMessage(message, type) {
            const stateMessage = document.getElementById('stateMessage');
            const stateText = document.getElementById('stateMessageText');
            
            stateMessage.className = `alert alert-${type} d-block`;
            stateText.textContent = message;
            
            setTimeout(() => {
                stateMessage.classList.add('d-none');
            }, 5000);
        }
    </script>
</body>
</html>

<?php
        $this->pieShow();
    }
}
?>