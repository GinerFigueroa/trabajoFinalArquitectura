<?php
// test_timezone.php
// Colócalo en: TRABAJOFINALARQUITECTURA/modeloRol/administrador/gestionRecordatorioPaciente/

// Incluir la conexión
include_once('conexion.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Timezone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>🔍 Diagnóstico de Timezone y Citas</h2>";

// Configurar timezone para Perú
date_default_timezone_set('America/Lima');

echo "<div class='info'>";
echo "<h3>Configuración PHP</h3>";
echo "PHP Timezone: " . date_default_timezone_get() . "<br>";
echo "PHP Fecha/Hora Actual: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Fecha Actual: " . date('Y-m-d') . "<br>";
echo "</div>";

// Conectar a la base de datos
try {
    $connection = Conexion::getInstancia()->getConnection();
    
    echo "<div class='info'>";
    echo "<h3>Configuración MySQL</h3>";
    
    // Obtener info de MySQL
    $result = $connection->query("SELECT NOW() as db_time, CURDATE() as db_date, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
    $row = $result->fetch_assoc();
    echo "MySQL Fecha/Hora: " . $row['db_time'] . "<br>";
    echo "MySQL Fecha: " . $row['db_date'] . "<br>";
    echo "MySQL Timezone Global: " . $row['global_tz'] . "<br>";
    echo "MySQL Timezone Session: " . $row['session_tz'] . "<br>";
    echo "</div>";
    
    // Verificar citas de hoy
    $fechaHoy = date('Y-m-d');
    echo "<div class='info'>";
    echo "<h3>Citas para Hoy ($fechaHoy)</h3>";
    
    $sql = "SELECT 
                c.id_cita, 
                c.fecha_hora, 
                c.estado,
                c.recordatorio_enviado,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as paciente,
                p.dni,
                pt.chat_id,
                pt.username_telegram
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente AND pt.activo = 1
            WHERE DATE(c.fecha_hora) = '$fechaHoy'
            AND c.estado IN ('Confirmada', 'Pendiente')
            ORDER BY c.fecha_hora ASC";
    
    $result = $connection->query($sql);
    echo "Total citas hoy: " . $result->num_rows . "<br>";
    echo "</div>";
    
    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>ID Cita</th>
                    <th>Fecha/Hora</th>
                    <th>Paciente</th>
                    <th>DNI</th>
                    <th>Estado</th>
                    <th>Recordatorio</th>
                    <th>Chat ID</th>
                    <th>Username</th>
                    <th>¿Cita Futura?</th>
                </tr>";
        
        while($cita = $result->fetch_assoc()) {
            $fechaHoraCita = new DateTime($cita['fecha_hora']);
            $fechaHoraActual = new DateTime();
            $esFutura = $fechaHoraCita > $fechaHoraActual;
            $diferencia = $fechaHoraActual->diff($fechaHoraCita);
            
            $statusRecordatorio = $cita['recordatorio_enviado'] ? 
                '<span style="color: green;">✅ Enviado</span>' : 
                '<span style="color: red;">❌ No enviado</span>';
            
            $statusCita = $esFutura ? 
                '<span style="color: blue;">⏰ Futura</span>' : 
                '<span style="color: orange;">⌛ Pasada</span>';
            
            echo "<tr>
                    <td>{$cita['id_cita']}</td>
                    <td>{$cita['fecha_hora']}</td>
                    <td>{$cita['paciente']}</td>
                    <td>{$cita['dni']}</td>
                    <td>{$cita['estado']}</td>
                    <td>{$statusRecordatorio}</td>
                    <td>{$cita['chat_id']}</td>
                    <td>{$cita['username_telegram']}</td>
                    <td>{$statusCita} (" . ($esFutura ? '+' : '-') . $diferencia->format('%hh %im') . ")</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>No hay citas para hoy.</div>";
    }
    
    // Verificar pacientes con Telegram activo
    echo "<div class='info'>";
    echo "<h3>Pacientes con Telegram Activo</h3>";
    
    $sql_telegram = "SELECT 
                        pt.id_paciente,
                        p.dni,
                        CONCAT(u.nombre, ' ', u.apellido_paterno) as paciente,
                        pt.chat_id,
                        pt.username_telegram,
                        pt.activo
                    FROM paciente_telegram pt
                    JOIN pacientes p ON pt.id_paciente = p.id_paciente
                    JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE pt.activo = 1";
    
    $result_telegram = $connection->query($sql_telegram);
    echo "Total pacientes con Telegram activo: " . $result_telegram->num_rows . "<br>";
    
    if ($result_telegram->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>ID Paciente</th>
                    <th>DNI</th>
                    <th>Paciente</th>
                    <th>Chat ID</th>
                    <th>Username</th>
                    <th>Estado</th>
                </tr>";
        
        while($paciente = $result_telegram->fetch_assoc()) {
            $estado = $paciente['activo'] ? 
                '<span style="color: green;">✅ Activo</span>' : 
                '<span style="color: red;">❌ Inactivo</span>';
            
            echo "<tr>
                    <td>{$paciente['id_paciente']}</td>
                    <td>{$paciente['dni']}</td>
                    <td>{$paciente['paciente']}</td>
                    <td>{$paciente['chat_id']}</td>
                    <td>{$paciente['username_telegram']}</td>
                    <td>{$estado}</td>
                  </tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>Error de conexión: " . $e->getMessage() . "</div>";
}

// Test de Telegram
echo "<div class='info'>";
echo "<h3>Test de Conexión Telegram</h3>";

$botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
$testUrl = "https://api.telegram.org/bot{$botToken}/getMe";

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$response = @file_get_contents($testUrl, false, $context);

if ($response === false) {
    echo "❌ No se pudo conectar con Telegram API<br>";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "<br>";
} else {
    $data = json_decode($response, true);
    if (isset($data['ok']) && $data['ok'] === true) {
        echo "✅ Bot de Telegram conectado correctamente<br>";
        echo "Bot: @" . $data['result']['username'] . " - " . $data['result']['first_name'] . "<br>";
    } else {
        echo "❌ Error en Telegram Bot: " . ($data['description'] ?? 'Error desconocido') . "<br>";
    }
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>Recomendaciones</h3>";
echo "<ul>
        <li>✅ Verifica que el timezone de PHP y MySQL coincidan</li>
        <li>✅ Las citas deben ser futuras para enviar recordatorios</li>
        <li>✅ Los pacientes deben tener chat_id válido en paciente_telegram</li>
        <li>✅ El bot de Telegram debe estar funcionando</li>
        <li>✅ Los pacientes deben haber iniciado el bot con /start</li>
      </ul>";
echo "</div>";

echo "</body></html>";
?>