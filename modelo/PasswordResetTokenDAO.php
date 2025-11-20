<?php
// models/PasswordResetTokenDAO.php

class PasswordResetTokenDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function crearToken($tokenData) {
        $sql = "INSERT INTO password_reset_tokens 
                (id_usuario, token, fecha_expiracion, ip_solicitud) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $tokenData['id_usuario'],
            $tokenData['token'],
            $tokenData['fecha_expiracion'],
            $tokenData['ip_solicitud']
        ]);
    }
    
    public function obtenerTokenValido($token) {
        $sql = "SELECT * FROM password_reset_tokens 
                WHERE token = ? 
                AND utilizado = 0 
                AND fecha_expiracion > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function marcarTokenComoUtilizado($idToken) {
        $sql = "UPDATE password_reset_tokens SET utilizado = 1 WHERE id_token = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idToken]);
    }
    
    public function contarIntentosRecientes($email, $horas = 1) {
        $sql = "SELECT COUNT(*) as intentos 
                FROM password_reset_tokens pt
                JOIN usuarios u ON pt.id_usuario = u.id_usuario
                WHERE u.email = ? 
                AND pt.fecha_creacion > DATE_SUB(NOW(), INTERVAL ? HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $horas]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['intentos'] ?? 0;
    }
    
    public function limpiarTokensExpirados() {
        $sql = "DELETE FROM password_reset_tokens WHERE fecha_expiracion < NOW()";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
?>