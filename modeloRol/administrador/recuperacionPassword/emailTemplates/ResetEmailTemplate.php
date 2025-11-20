<?php
// recuperacionPassword/emailTemplates/ResetEmailTemplate.php

class ResetEmailTemplate extends EmailTemplate {
    
    protected function prepareTemplate($usuario, $data) {
        // Preparar datos específicos para confirmación
    }
    
    protected function buildSubject() {
        $this->subject = "Contraseña Actualizada - Sistema Médico";
    }
    
    protected function buildBody($usuario, $data) {
        $nombre = $usuario['nombre'];
        $fecha = date('d/m/Y H:i:s');
        $ip = $data['ip'] ?? 'Desconocida';
        
        $this->body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Contraseña Actualizada</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #198754; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .alert { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Contraseña Actualizada</h1>
                </div>
                <div class='content'>
                    <h2>Hola $nombre,</h2>
                    <p>Tu contraseña ha sido actualizada exitosamente.</p>
                    
                    <div class='alert'>
                        <strong>Detalles del cambio:</strong><br>
                        - Fecha: $fecha<br>
                        - IP: $ip
                    </div>
                    
                    <p>Si no realizaste este cambio, por favor contacta inmediatamente al administrador del sistema.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Sistema Médico. Todos los derechos reservados.</p>
                    <p>Este es un mensaje automático de seguridad.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>