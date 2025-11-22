<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class formEditarRecordatorioPaciente extends pantalla
{
    private $objTelegramDAO;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
    }

    public function formEditarRecordatorioPacienteShow()
    {
        $this->cabeceraShow("Gestión de Registros de Telegram");
        
        // Obtener todos los registros (activos e inactivos)
        $registros = $this->objTelegramDAO->obtenerTodosChatsTelegramCompleto();
        $estadisticas = $this->objTelegramDAO->obtenerEstadisticasTelegram();
?>

<!-- Solo el contenido específico de esta página, sin <html>, <head>, <body> -->
<style>
    .card-hover:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .badge-status {
        font-size: 0.75em;
    }
    .action-buttons .btn {
        margin: 1px;
        padding: 0.25rem 0.5rem;
    }
    .table-responsive {
        max-height: 600px;
    }
    .search-box {
        max-width: 400px;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    .form-inline {
        display: inline;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header con Estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow card-hover">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h1 class="card-title mb-0">
                                <i class="bi bi-telegram me-2"></i>
                                Gestión de Registros Telegram
                            </h1>
                            <p class="card-text mb-0 mt-2 opacity-75">
                                Administrar, editar y enviar mensajes a pacientes en Telegram
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="mb-0"><?= $estadisticas['total_registros'] ?></h4>
                                    <small>Total</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="mb-0 text-success"><?= $estadisticas['activos'] ?></h4>
                                    <small>Activos</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="mb-0 text-warning"><?= $estadisticas['inactivos'] ?></h4>
                                    <small>Inactivos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Herramientas -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-hover">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <a href="../indexRecordatorioPaciente.php" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                            </a>
                            <a href="../registrarNuevoRecordatorio/indexRegistrarNuevoRecordatorio.php" 
                               class="btn btn-success btn-sm">
                                <i class="bi bi-person-plus me-1"></i>Nuevo Registro
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2 align-items-center">
                            <button class="btn btn-info btn-sm" onclick="recargarTabla()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                            </button>
                            <div class="input-group input-group-sm search-box">
                                <input type="text" class="form-control" id="inputBuscar" 
                                       placeholder="Buscar por nombre, DNI, username...">
                                <button class="btn btn-outline-secondary" type="button" onclick="buscarRegistros()">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="row">
        <div class="col-12">
            <div class="card card-hover shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Registros de Pacientes en Telegram
                    </h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleInactivos" 
                               onchange="toggleRegistrosInactivos()">
                        <label class="form-check-label" for="toggleInactivos">
                            Mostrar inactivos
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="tablaRegistros">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Paciente</th>
                                    <th>DNI</th>
                                    <th>Chat ID</th>
                                    <th>Username</th>
                                    <th>Nombre en Telegram</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th width="280" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($registros)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                No hay registros de Telegram disponibles
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($registros as $registro): ?>
                                        <tr class="<?= $registro['activo'] == 0 ? 'table-warning' : '' ?>">
                                            <td>
                                                <strong>#<?= $registro['id'] ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($registro['nombre_paciente']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">Paciente ID: <?= $registro['id_paciente'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($registro['dni']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary font-monospace">
                                                    <?= htmlspecialchars($registro['chat_id']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($registro['username_telegram']): ?>
                                                    <span class="text-primary">@<?= htmlspecialchars($registro['username_telegram']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($registro['first_name'] || $registro['last_name']): ?>
                                                    <?= htmlspecialchars($registro['first_name'] . ' ' . $registro['last_name']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($registro['activo'] == 1): ?>
                                                    <span class="badge bg-success badge-status">
                                                        <i class="bi bi-check-circle me-1"></i>Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark badge-status">
                                                        <i class="bi bi-x-circle me-1"></i>Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($registro['fecha_registro'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm action-buttons" role="group">
                                                    <!-- Probar Chat (Mensaje de prueba fijo) -->
                                                    <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                                                        <input type="hidden" name="action" value="probar_chat">
                                                        <input type="hidden" name="id_registro" value="<?= $registro['id'] ?>">
                                                        <input type="hidden" name="chat_id" value="<?= $registro['chat_id'] ?>">
                                                        <button type="submit" class="btn btn-outline-info" title="Probar Chat ID">
                                                            <i class="bi bi-play-circle"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Enviar Mensaje Personalizado -->
                                                    <button type="button" class="btn btn-outline-success"
                                                            onclick="enviarMensajePersonal(<?= $registro['id'] ?>, '<?= $registro['chat_id'] ?>', '<?= htmlspecialchars(addslashes($registro['nombre_paciente'])) ?>')"
                                                            title="Enviar Mensaje Personalizado">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                    
                                                    <!-- Editar -->
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="editarRegistro(<?= $registro['id'] ?>)"
                                                            title="Editar Registro">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    
                                                    <!-- Activar/Desactivar -->
                                                    <?php if ($registro['activo'] == 1): ?>
                                                        <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                                                            <input type="hidden" name="action" value="cambiar_estado">
                                                            <input type="hidden" name="id_registro" value="<?= $registro['id'] ?>">
                                                            <input type="hidden" name="nuevo_estado" value="0">
                                                            <button type="submit" class="btn btn-outline-warning" title="Desactivar">
                                                                <i class="bi bi-pause"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                                                            <input type="hidden" name="action" value="cambiar_estado">
                                                            <input type="hidden" name="id_registro" value="<?= $registro['id'] ?>">
                                                            <input type="hidden" name="nuevo_estado" value="1">
                                                            <button type="submit" class="btn btn-outline-success" title="Activar">
                                                                <i class="bi bi-play"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Eliminar -->
                                                    <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline" 
                                                          onsubmit="return confirm('¿Estás seguro que deseas eliminar permanentemente este registro?\\n\\nEsta acción no se puede deshacer.');">
                                                        <input type="hidden" name="action" value="eliminar_registro">
                                                        <input type="hidden" name="id_registro" value="<?= $registro['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Registro de Telegram
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoEditar">
                <!-- Contenido cargado via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para Enviar Mensaje Personalizado -->
<div class="modal fade" id="modalMensajePersonal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-send me-2"></i>
                    Enviar Mensaje Personalizado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="./getEditarRecordatorioPaciente.php" id="formMensajePersonal">
                    <input type="hidden" name="action" value="enviar_mensaje_personal">
                    <input type="hidden" id="mensaje_id_registro" name="id_registro">
                    <input type="hidden" id="mensaje_chat_id" name="chat_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Para:</label>
                        <input type="text" class="form-control" id="mensaje_nombre_paciente" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mensaje_texto" class="form-label">Mensaje *</label>
                        <textarea class="form-control" id="mensaje_texto" name="mensaje" 
                                  rows="6" placeholder="Escribe tu mensaje personalizado aquí..." 
                                  maxlength="1000" required></textarea>
                        <div class="form-text">
                            <span id="contadorCaracteres">0</span>/1000 caracteres
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            El mensaje se enviará directamente al paciente a través de Telegram.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formMensajePersonal" class="btn btn-success">
                    <i class="bi bi-send-fill me-1"></i>Enviar Mensaje
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para enviar mensaje personalizado
function enviarMensajePersonal(idRegistro, chatId, nombrePaciente) {
    // Llenar el modal con los datos
    document.getElementById('mensaje_id_registro').value = idRegistro;
    document.getElementById('mensaje_chat_id').value = chatId;
    document.getElementById('mensaje_nombre_paciente').value = nombrePaciente;
    document.getElementById('mensaje_texto').value = '';
    document.getElementById('contadorCaracteres').textContent = '0';
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalMensajePersonal'));
    modal.show();
}

// Contador de caracteres para el mensaje personalizado
document.getElementById('mensaje_texto').addEventListener('input', function() {
    const contador = document.getElementById('contadorCaracteres');
    contador.textContent = this.value.length;
});

// Función para editar registro
function editarRegistro(idRegistro) {
    document.getElementById('contenidoEditar').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando formulario...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
    modal.show();
    
    fetch('./getEditarRecordatorioPaciente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=cargar_formulario&id_registro=${idRegistro}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('contenidoEditar').innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('contenidoEditar').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Error al cargar el formulario: ${error.message}
            </div>
        `;
    });
}

// Función para buscar registros
function buscarRegistros() {
    const termino = document.getElementById('inputBuscar').value;
    
    if (termino.length === 0) {
        location.reload();
        return;
    }
    
    fetch('./getEditarRecordatorioPaciente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=buscar_registros&termino=${encodeURIComponent(termino)}`
    })
    .then(response => response.json())
    .then(data => {
        actualizarTabla(data.registros);
    })
    .catch(error => {
        console.error('Error en la búsqueda:', error);
    });
}

// Actualizar tabla con resultados de búsqueda
function actualizarTabla(registros) {
    const tbody = document.querySelector('#tablaRegistros tbody');
    
    if (registros.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-search display-4 d-block mb-2"></i>
                        No se encontraron registros
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    registros.forEach(registro => {
        html += `
            <tr class="${registro.activo == 0 ? 'table-warning' : ''}">
                <td><strong>#${registro.id}</strong></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <strong>${registro.nombre_paciente}</strong>
                            <br>
                            <small class="text-muted">Paciente ID: ${registro.id_paciente}</small>
                        </div>
                    </div>
                </td>
                <td><code>${registro.dni}</code></td>
                <td><span class="badge bg-secondary font-monospace">${registro.chat_id}</span></td>
                <td>${registro.username_telegram ? `<span class="text-primary">@${registro.username_telegram}</span>` : '<span class="text-muted">-</span>'}</td>
                <td>${registro.first_name || registro.last_name ? `${registro.first_name} ${registro.last_name}` : '<span class="text-muted">-</span>'}</td>
                <td>
                    ${registro.activo == 1 ? 
                        '<span class="badge bg-success badge-status"><i class="bi bi-check-circle me-1"></i>Activo</span>' : 
                        '<span class="badge bg-warning text-dark badge-status"><i class="bi bi-x-circle me-1"></i>Inactivo</span>'
                    }
                </td>
                <td><small class="text-muted">${new Date(registro.fecha_registro).toLocaleDateString('es-ES')}</small></td>
                <td>
                    <div class="btn-group btn-group-sm action-buttons" role="group">
                        <!-- Probar Chat -->
                        <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                            <input type="hidden" name="action" value="probar_chat">
                            <input type="hidden" name="id_registro" value="${registro.id}">
                            <input type="hidden" name="chat_id" value="${registro.chat_id}">
                            <button type="submit" class="btn btn-outline-info" title="Probar Chat ID">
                                <i class="bi bi-play-circle"></i>
                            </button>
                        </form>
                        
                        <!-- Enviar Mensaje Personalizado -->
                        <button type="button" class="btn btn-outline-success"
                                onclick="enviarMensajePersonal(${registro.id}, '${registro.chat_id}', '${registro.nombre_paciente.replace(/'/g, "\\'")}')"
                                title="Enviar Mensaje Personalizado">
                            <i class="bi bi-send"></i>
                        </button>
                        
                        <!-- Editar -->
                        <button type="button" class="btn btn-outline-primary"
                                onclick="editarRegistro(${registro.id})"
                                title="Editar Registro">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <!-- Activar/Desactivar -->
                        ${registro.activo == 1 ? 
                            `<form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="id_registro" value="${registro.id}">
                                <input type="hidden" name="nuevo_estado" value="0">
                                <button type="submit" class="btn btn-outline-warning" title="Desactivar">
                                    <i class="bi bi-pause"></i>
                                </button>
                            </form>` :
                            `<form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="id_registro" value="${registro.id}">
                                <input type="hidden" name="nuevo_estado" value="1">
                                <button type="submit" class="btn btn-outline-success" title="Activar">
                                    <i class="bi bi-play"></i>
                                </button>
                            </form>`
                        }
                        
                        <!-- Eliminar -->
                        <form method="POST" action="./getEditarRecordatorioPaciente.php" class="form-inline" 
                              onsubmit="return confirm('¿Estás seguro que deseas eliminar permanentemente este registro?\\n\\nEsta acción no se puede deshacer.');">
                            <input type="hidden" name="action" value="eliminar_registro">
                            <input type="hidden" name="id_registro" value="${registro.id}">
                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Función para recargar la tabla
function recargarTabla() {
    location.reload();
}

// Función para alternar visibilidad de registros inactivos
function toggleRegistrosInactivos() {
    const mostrar = document.getElementById('toggleInactivos').checked;
    const filas = document.querySelectorAll('#tablaRegistros tbody tr.table-warning');
    
    filas.forEach(fila => {
        fila.style.display = mostrar ? '' : 'none';
    });
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips si es necesario
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
        $this->pieShow();
    }
}
?>