<?php
// recuperacionPassword/resetPassword/controlResetPassword.php
// 🎯 STRATEGY + COMMAND + STATE

class controlResetPassword {
    private $objUsuarioDAO;
    private $objTokenDAO;
    private $observers;

    public function __construct() {
        $this->objUsuarioDAO = new UsuarioDAO();
        $this->objTokenDAO = new PasswordResetTokenDAO();
        $this->observers = [];
    }

    public function attach(RecoveryObserver $observer) {
        $this->observers[] = $observer;
    }

    private function notify($event, $data) {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }

    // 🧪 COMMAND PATTERN - Resetear contraseña
    public function resetearPassword($token, $nuevaPassword, $ip) {
        try {
            // Validar token
            $controlRecuperacion = new controlRecuperacionPassword();
            $datos = $controlRecuperacion->validarToken($token);
            
            $idUsuario = $datos['token_data']['id_usuario'];
            $idToken = $datos['token_data']['id_token'];
            
            // 🎛️ STATE PATTERN - Validar fortaleza de contraseña
            if (!$this->esPasswordSegura($nuevaPassword)) {
                throw new Exception("❌ La contraseña no cumple los requisitos de seguridad");
            }

            // 🧪 COMMAND PATTERN - Actualizar contraseña
            $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $resultado = $this->objUsuarioDAO->actualizarPassword($idUsuario, $passwordHash);
            
            if ($resultado) {
                // Marcar token como utilizado
                $this->objTokenDAO->marcarTokenComoUtilizado($idToken);
                
                $this->notify('PASSWORD_RESET_SUCCESS', [
                    'id_usuario' => $idUsuario,
                    'ip' => $ip,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                return true;
            }
            
            throw new Exception("❌ Error al actualizar la contraseña");
            
        } catch (Exception $e) {
            $this->notify('PASSWORD_RESET_ERROR', [
                'token' => $token,
                'error' => $e->getMessage(),
                'ip' => $ip
            ]);
            throw $e;
        }
    }

    private function esPasswordSegura($password) {
        $minLength = 8;
        $hasUpperCase = preg_match('/[A-Z]/', $password);
        $hasLowerCase = preg_match('/[a-z]/', $password);
        $hasNumbers = preg_match('/\d/', $password);
        
        return strlen($password) >= $minLength && 
               $hasUpperCase && 
               $hasLowerCase && 
               $hasNumbers;
    }
}
?>