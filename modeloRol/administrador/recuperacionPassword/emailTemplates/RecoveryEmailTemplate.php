<?php
// recuperacionPassword/emailTemplates/RecoveryEmailTemplate.php
// 🧱 TEMPLATE METHOD

abstract class EmailTemplate {
    protected $subject;
    protected $body;
    
    // 🧱 TEMPLATE METHOD - Define el esqueleto del algoritmo
    public final function send($usuario, $data) {
        $this->prepareTemplate($usuario, $data);
        $this->buildSubject();
        $this->buildBody($usuario, $data);
        return $this->sendEmail($usuario['email']);
    }
    
    abstract protected function prepareTemplate($usuario, $data);
    abstract protected function buildSubject();
    abstract protected function buildBody($usuario, $data);
    
    private function sendEmail($to) {
        $headers = "From: sistema@clinica.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $this->subject, $this->body, $headers);
    }
}

class RecoveryEmailTemplate extends EmailTemplate {
    
    protected function prepareTemplate($usuario, $data) {
        // Preparar datos específicos para recuperación
    }
    
    protected function buildSubject() {
        $this->subject = "Recuperación de Contraseña - Sistema Médico";
    }
    
    protected function buildBody($usuario, $data) {
        $nombre = $usuario['nombre'];
        $token = $data['token'];
        $enlace = "http://tudominio.com/recuperacionPassword/resetPassword/indexResetPassword.php?token=" . $token;
        
        $this->body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recuperación de Contraseña</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ffc107; color: #000; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .button { background: #198754; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Recuperación de Contraseña</h1>
                </div>
                <div class='content'>
                    <h2>Hola $nombre,</h2>
                    <p>Has solicitado restablecer tu contraseña en el Sistema Médico.</p>
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$enlace' class='button'>Restablecer Contraseña</a>
                    </p>
                    <p><strong>Este enlace expirará en 1 hora.</strong></p>
                    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Sistema Médico. Todos los derechos reservados.</p>
                    <p>Este es un mensaje automático, por favor no respondas a este email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>