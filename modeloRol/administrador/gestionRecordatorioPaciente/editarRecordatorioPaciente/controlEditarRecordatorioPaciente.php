<?php
include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class controlEditarRecordatorioPaciente
{
    private $objTelegramDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Probar Chat ID (mensaje de prueba fijo) - CON MENSAJESISTEMASHOW
     */
    public function probarChat()
    {
        $idRegistro = $_POST['id_registro'] ?? null;
        $chatId = $_POST['chat_id'] ?? null;
        
        if (!$idRegistro || !$chatId) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Datos incompletos para probar el chat', 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
            return;
        }

        try {
            // Verificar que el registro existe y está activo
            $registro = $this->objTelegramDAO->obtenerChatPorId($idRegistro);
            if (!$registro) {
                throw new Exception('Registro no encontrado');
            }

            if ($registro['activo'] != 1) {
                throw new Exception('El registro está inactivo');
            }

            $resultado = $this->enviarMensajePrueba($chatId);

            if ($resultado['success']) {
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Mensaje de prueba enviado correctamente', 
                    './indexEditarRecordatorioPaciente.php', 
                    'success'
                );
            } else {
                throw new Exception('Error al enviar mensaje: ' . $resultado['mensaje']);
            }

        } catch (Exception $e) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ ' . $e->getMessage(), 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Enviar mensaje personalizado a un paciente específico - CON MENSAJESISTEMASHOW
     */
    public function enviarMensajePersonal()
    {
        $idRegistro = $_POST['id_registro'] ?? null;
        $chatId = $_POST['chat_id'] ?? null;
        $mensaje = $_POST['mensaje'] ?? '';
        
        if (!$idRegistro || !$chatId) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Datos incompletos para enviar mensaje', 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
            return;
        }

        if (empty($mensaje)) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ El mensaje no puede estar vacío', 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
            return;
        }

        try {
            // Verificar que el registro existe y está activo
            $registro = $this->objTelegramDAO->obtenerChatPorId($idRegistro);
            if (!$registro) {
                throw new Exception('Registro no encontrado');
            }

            if ($registro['activo'] != 1) {
                throw new Exception('El registro está inactivo');
            }

            // Formatear el mensaje personalizado
            $mensajeFormateado = "📢 *Mensaje Personalizado - Clínica*\n\n" .
                                "Hola " . ($registro['first_name'] ? $registro['first_name'] : $registro['nombre_paciente']) . ",\n\n" .
                                $mensaje . "\n\n" .
                                "_Fecha: " . date('d/m/Y H:i') . "_";

            $resultado = $this->enviarMensajeTelegram($chatId, $mensajeFormateado);

            if ($resultado['success']) {
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Mensaje personalizado enviado correctamente', 
                    './indexEditarRecordatorioPaciente.php', 
                    'success'
                );
            } else {
                throw new Exception('Error al enviar mensaje: ' . $resultado['mensaje']);
            }

        } catch (Exception $e) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ ' . $e->getMessage(), 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Cargar formulario de edición
     */
    public function cargarFormularioEdicion()
    {
        $idRegistro = $_POST['id_registro'] ?? null;
        
        if (!$idRegistro) {
            echo '<div class="alert alert-danger">ID de registro no proporcionado</div>';
            return;
        }

        $registro = $this->objTelegramDAO->obtenerChatPorId($idRegistro);
        
        if (!$registro) {
            echo '<div class="alert alert-danger">Registro no encontrado</div>';
            return;
        }

        // Mostrar formulario de edición
        $this->mostrarFormularioEdicion($registro);
    }

    /**
     * Mostrar formulario de edición
     */
    private function mostrarFormularioEdicion($registro)
    {
        ?>
        <form id="formEditarRegistro" method="POST" action="./getEditarRecordatorioPaciente.php">
            <input type="hidden" name="action" value="guardar_edicion">
            <input type="hidden" name="id_registro" value="<?= $registro['id'] ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Paciente</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($registro['nombre_paciente']) ?>" readonly>
                        <div class="form-text">Paciente no se puede modificar</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($registro['dni']) ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="chat_id" class="form-label">Chat ID *</label>
                <input type="number" class="form-control" id="chat_id" name="chat_id" 
                       value="<?= htmlspecialchars($registro['chat_id']) ?>" required>
                <div class="form-text">ID único del chat en Telegram</div>
            </div>

            <div class="mb-3">
                <label for="username_telegram" class="form-label">Username de Telegram</label>
                <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" class="form-control" id="username_telegram" name="username_telegram" 
                           value="<?= htmlspecialchars($registro['username_telegram'] ?? '') ?>" 
                           placeholder="usuario (sin @)">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Nombre en Telegram</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?= htmlspecialchars($registro['first_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Apellido en Telegram</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?= htmlspecialchars($registro['last_name'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Los campos marcados con * son obligatorios.
                </small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                </button>
            </div>
        </form>
        <?php
    }

    /**
     * Guardar edición del formulario - CON MENSAJESISTEMASHOW
     */
    public function guardarEdicion()
    {
        try {
            // Validar campos requeridos
            if (!isset($_POST['id_registro']) || empty($_POST['id_registro'])) {
                throw new Exception('ID de registro no proporcionado');
            }

            if (!isset($_POST['chat_id']) || empty($_POST['chat_id'])) {
                throw new Exception('Chat ID es obligatorio');
            }

            // Sanitizar datos
            $idRegistro = filter_var($_POST['id_registro'], FILTER_VALIDATE_INT);
            $chatId = filter_var($_POST['chat_id'], FILTER_VALIDATE_INT);
            $username = $this->sanitizeInput($_POST['username_telegram'] ?? '');
            $firstName = $this->sanitizeInput($_POST['first_name'] ?? '');
            $lastName = $this->sanitizeInput($_POST['last_name'] ?? '');

            // Validaciones
            if (!$idRegistro || $idRegistro <= 0) {
                throw new Exception('ID de registro no válido');
            }

            if (!$chatId || $chatId <= 0) {
                throw new Exception('Chat ID debe ser un número positivo');
            }

            // Verificar que el registro existe
            $registroExistente = $this->objTelegramDAO->obtenerChatPorId($idRegistro);
            if (!$registroExistente) {
                throw new Exception('El registro no existe');
            }

            // Verificar que el nuevo Chat ID no esté en uso por otro registro
            $registroConMismoId = $this->objTelegramDAO->obtenerChatPorChatId($chatId);
            if ($registroConMismoId && $registroConMismoId['id'] != $idRegistro) {
                throw new Exception('Este Chat ID ya está registrado para otro paciente');
            }

            // Actualizar en la base de datos
            $resultado = $this->objTelegramDAO->actualizarChatTelegram(
                $idRegistro, 
                $chatId, 
                $username ?: null, 
                $firstName ?: null, 
                $lastName ?: null
            );

            if ($resultado['success']) {
                $this->objMensaje->mensajeSistemaShow(
                    $resultado['mensaje'], 
                    './indexEditarRecordatorioPaciente.php', 
                    'success'
                );
            } else {
                throw new Exception($resultado['mensaje']);
            }

        } catch (Exception $e) {
            error_log("Error en guardarEdicion: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow(
                '❌ ' . $e->getMessage(), 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Cambiar estado del registro - CON MENSAJESISTEMASHOW
     */
    public function cambiarEstado()
    {
        $idRegistro = $_POST['id_registro'] ?? null;
        $nuevoEstado = $_POST['nuevo_estado'] ?? null;
        
        if (!$idRegistro || $nuevoEstado === null) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Datos incompletos para cambiar estado', 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
            return;
        }

        try {
            if ($nuevoEstado == 0) {
                // Desactivar
                $resultado = $this->objTelegramDAO->eliminarChatTelegram($idRegistro);
                $mensajeExito = '✅ Registro desactivado correctamente';
                $mensajeError = 'Error al desactivar el registro';
            } else {
                // Reactivar
                $resultado = $this->objTelegramDAO->reactivarChatTelegram($idRegistro);
                $mensajeExito = '✅ Registro reactivado correctamente';
                $mensajeError = 'Error al reactivar el registro';
            }

            if ($resultado) {
                $this->objMensaje->mensajeSistemaShow(
                    $mensajeExito, 
                    './indexEditarRecordatorioPaciente.php', 
                    'success'
                );
            } else {
                throw new Exception($mensajeError);
            }

        } catch (Exception $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow(
                '❌ ' . $e->getMessage(), 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Eliminar registro permanentemente - CON MENSAJESISTEMASHOW
     */
    public function eliminarRegistro()
    {
        $idRegistro = $_POST['id_registro'] ?? null;
        
        if (!$idRegistro) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ ID de registro no proporcionado', 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
            return;
        }

        try {
            // Primero desactivar (eliminación lógica)
            $resultado = $this->objTelegramDAO->eliminarChatTelegram($idRegistro);
            
            if ($resultado) {
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Registro eliminado correctamente', 
                    './indexEditarRecordatorioPaciente.php', 
                    'success'
                );
            } else {
                throw new Exception('Error al eliminar el registro');
            }

        } catch (Exception $e) {
            error_log("Error en eliminarRegistro: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow(
                '❌ ' . $e->getMessage(), 
                './indexEditarRecordatorioPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Buscar registros - MANTENER AJAX (no usa mensajeSistemaShow)
     */
    public function buscarRegistros()
    {
        header('Content-Type: application/json');
        
        $termino = $_POST['termino'] ?? '';
        
        if (empty($termino)) {
            // Si no hay término, devolver todos los registros
            $registros = $this->objTelegramDAO->obtenerTodosChatsTelegramCompleto();
        } else {
            // Buscar con término
            $registros = $this->objTelegramDAO->buscarChatsCompleto($termino);
        }

        echo json_encode([
            'success' => true,
            'registros' => $registros
        ]);
    }

    /**
     * Enviar mensaje de prueba a Telegram
     */
    private function enviarMensajePrueba($chatId)
    {
        $mensaje = "🧪 *Mensaje de Prueba - Sistema de Recordatorios*\n\n" .
                   "Hola, este es un mensaje de prueba del sistema.\n\n" .
                   "✅ *Estado:* Conexión verificada correctamente\n" .
                   "📅 *Fecha:* " . date('d/m/Y H:i') . "\n\n" .
                   "Si recibes este mensaje, tu configuración de Telegram está funcionando correctamente.";

        return $this->enviarMensajeTelegram($chatId, $mensaje);
    }

    /**
     * Función centralizada para enviar mensajes a Telegram
     */
    private function enviarMensajeTelegram($chatId, $mensaje)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $mensaje,
            'parse_mode' => 'Markdown'
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['success' => true, 'mensaje' => 'Mensaje enviado correctamente'];
        } else {
            $responseData = json_decode($response, true);
            $errorMensaje = $responseData['description'] ?? 'Error desconocido de Telegram';
            return ['success' => false, 'mensaje' => $errorMensaje];
        }
    }

    private function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>