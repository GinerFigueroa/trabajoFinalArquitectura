<?php
// recuperacionPassword/controlRecuperacionPassword.php
// 🎯 STRATEGY + OBSERVER + TEMPLATE METHOD

class controlRecuperacionPassword {
    private $objUsuarioDAO;
    private $objTokenDAO;
    private $recoveryStrategy;
    private $observers;

    public function __construct() {
        $this->objUsuarioDAO = new UsuarioDAO();
        $this->objTokenDAO = new PasswordResetTokenDAO();
        $this->observers = [];
        
        // 🎯 STRATEGY PATTERN - Estrategia por defecto (Email)
        $this->recoveryStrategy = new EmailRecoveryStrategy();
    }

    // 🔍 OBSERVER PATTERN - Notificar eventos
    public function attach(RecoveryObserver $observer) {
        $this->observers[] = $observer;
    }

    private function notify($event, $data) {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }

    // 🎯 STRATEGY PATTERN - Procesar solicitud de recuperación
    public function solicitarRecuperacion($email, $ip, $userAgent) {
        try {
            // Verificar rate limiting
            if (!$this->puedeSolicitarRecuperacion($email)) {
                throw new Exception("❌ Demasiados intentos. Espere 1 hora.");
            }

            // Buscar usuario por email
            $usuario = $this->objUsuarioDAO->obtenerUsuarioPorEmail($email);
            if (!$usuario) {
                // Por seguridad, no revelamos si el email existe
                $this->notify('RECOVERY_REQUEST_FAILED', [
                    'email' => $email,
                    'reason' => 'user_not_found',
                    'ip' => $ip,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return true;
            }

            // Generar y guardar token
            $token = $this->generarTokenSeguro();
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $tokenData = [
                'id_usuario' => $usuario['id_usuario'],
                'token' => $token,
                'fecha_expiracion' => $fechaExpiracion,
                'ip_solicitud' => $ip
            ];

            if ($this->objTokenDAO->crearToken($tokenData)) {
                // 🎯 STRATEGY PATTERN - Ejecutar estrategia
                $resultado = $this->recoveryStrategy->execute($usuario, $token);
                
                if ($resultado) {
                    $this->notify('RECOVERY_REQUEST_SUCCESS', [
                        'id_usuario' => $usuario['id_usuario'],
                        'email' => $email,
                        'ip' => $ip,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    return true;
                }
            }

            throw new Exception("❌ Error al procesar la solicitud");

        } catch (Exception $e) {
            $this->notify('RECOVERY_REQUEST_ERROR', [
                'email' => $email,
                'error' => $e->getMessage(),
                'ip' => $ip,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            throw $e;
        }
    }

    // 🎯 STRATEGY PATTERN - Cambiar estrategia
    public function setRecoveryStrategy(RecoveryStrategy $strategy) {
        $this->recoveryStrategy = $strategy;
    }

    private function generarTokenSeguro() {
        return bin2hex(random_bytes(32));
    }

    private function puedeSolicitarRecuperacion($email) {
        // Verificar intentos recientes (máximo 3 por hora)
        $intentos = $this->objTokenDAO->contarIntentosRecientes($email, 1);
        return $intentos < 3;
    }

    // 🔍 Validar token para reset
    public function validarToken($token) {
        try {
            $tokenData = $this->objTokenDAO->obtenerTokenValido($token);
            
            if (!$tokenData) {
                throw new Exception("❌ Token inválido o expirado");
            }

            if ($tokenData['utilizado']) {
                throw new Exception("❌ Este enlace ya ha sido utilizado");
            }

            if (strtotime($tokenData['fecha_expiracion']) < time()) {
                throw new Exception("❌ El enlace ha expirado");
            }

            // Obtener datos del usuario
            $usuario = $this->objUsuarioDAO->obtenerUsuarioPorId($tokenData['id_usuario']);
            
            if (!$usuario) {
                throw new Exception("❌ Usuario no encontrado");
            }

            return [
                'token_data' => $tokenData,
                'usuario' => $usuario
            ];

        } catch (Exception $e) {
            $this->notify('TOKEN_VALIDATION_FAILED', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
?>