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
        $this->cabeceraShow("Editar Registros de Telegram");
        
        // Obtener todos los chats registrados
        $chatsTelegram = $this->objTelegramDAO->obtenerTodosChatsTelegram();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Registros Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .badge-status {
            font-size: 0.8em;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-dark shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="card-title mb-0">
                                <i class="bi bi-pencil-square me-2"></i>
                                Gestión de Registros de Telegram
                            </h1>
                            <p class="card-text mb-0 mt-2 opacity-75">
                                Editar, probar y administrar registros existentes
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="../indexRecordatorioPaciente.php" 
                                   class="btn btn-outline-dark btn-sm">
                                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                                </a>
                                <a href="../registrarRecordatorioPaciente/indexRegistrarNuevoRecordatorio.php" 
                                   class="btn btn-dark btn-sm">
                                    <i class="bi bi-person-plus me-1"></i>Nuevo Registro
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="buscarPaciente" 
                                       placeholder="Buscar por nombre, DNI o username...">
                                <button class="btn btn-outline-primary" type="button" onclick="buscarChats()">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtroEstado" onchange="filtrarPorEstado()">
                                <option value="todos">Todos los estados</option>
                                <option value="activo">Solo activos</option>
                                <option value="inactivo">Solo inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filasPorPagina" onchange="actualizarPaginacion()">
                                <option value="10">10 por página</option>
                                <option value="25">25 por página</option>
                                <option value="50">50 por página</option>
                                <option value="100">100 por página</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-muted mb-1">Total Registros</h6>
                            <h4 class="text-primary mb-0"><?= count($chatsTelegram) ?></h4>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted mb-1">Activos</h6>
                            <h4 class="text-success mb-0">
                                <?= array_reduce($chatsTelegram, function($carry, $item) {
                                    return $carry + ($item['activo'] == 1 ? 1 : 0);
                                }, 0) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Registros de Pacientes en Telegram
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($chatsTelegram)): ?>
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No hay pacientes registrados en Telegram.
                            <a href="../registrarRecordatorioPaciente/indexRegistrarNuevoRecordatorio.php" class="alert-link">
                                Registrar el primero
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Paciente</th>
                                        <th>DNI</th>
                                        <th>Chat ID</th>
                                        <th>Username</th>
                                        <th>Nombre en Telegram</th>
                                        <th>Fecha Registro</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaChats">
                                    <?php foreach ($chatsTelegram as $chat): ?>
                                        <tr data-id="<?= $chat['id'] ?>" data-estado="<?= $chat['activo'] ? 'activo' : 'inactivo' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($chat['nombre_paciente']) ?></strong>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($chat['dni']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($chat['chat_id']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($chat['username_telegram']): ?>
                                                    <span class="badge bg-info">@<?= htmlspecialchars($chat['username_telegram']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($chat['first_name'] || $chat['last_name']): ?>
                                                    <?= htmlspecialchars(trim($chat['first_name'] . ' ' . $chat['last_name'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($chat['fecha_registro'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($chat['activo'] == 1): ?>
                                                    <span class="badge bg-success badge-status">
                                                        <i class="bi bi-check-circle me-1"></i>Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger badge-status">
                                                        <i class="bi bi-x-circle me-1"></i>Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="editarChat(<?= $chat['id'] ?>)"
                                                            title="Editar información">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="probarMensaje(<?= $chat['id'] ?>)"
                                                            title="Probar mensaje">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                    <?php if ($chat['activo'] == 1): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="desactivarChat(<?= $chat['id'] ?>)"
                                                                title="Desactivar">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="reactivarChat(<?= $chat['id'] ?>)"
                                                                title="Reactivar">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <nav aria-label="Paginación" class="mt-3">
                            <ul class="pagination justify-content-center" id="paginacion">
                                <!-- La paginación se generará con JavaScript -->
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar chat -->
<div class="modal fade" id="modalEditarChat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Información de Telegram
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarChat">
                    <input type="hidden" id="editIdChat" name="idChat">
                    
                    <div class="mb-3">
                        <label for="editChatId" class="form-label">Chat ID *</label>
                        <input type="number" class="form-control" id="editChatId" name="chatId" required>
                        <div class="form-text">ID único del chat en Telegram</div>
                    </div>

                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username de Telegram</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" id="editUsername" name="username" 
                                   placeholder="usuario (sin @)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editFirstName" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="editFirstName" name="firstName">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editLastName" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="editLastName" name="lastName">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Los campos marcados con * son obligatorios.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicion()">
                    <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let paginaActual = 1;
let filasPorPagina = 10;
let filtroEstado = 'todos';
let terminoBusqueda = '';

function buscarChats() {
    terminoBusqueda = document.getElementById('buscarPaciente').value.toLowerCase();
    paginaActual = 1;
    actualizarTabla();
}

function filtrarPorEstado() {
    filtroEstado = document.getElementById('filtroEstado').value;
    paginaActual = 1;
    actualizarTabla();
}

function actualizarPaginacion() {
    filasPorPagina = parseInt(document.getElementById('filasPorPagina').value);
    paginaActual = 1;
    actualizarTabla();
}

function actualizarTabla() {
    const filas = document.querySelectorAll('#tablaChats tr');
    let filasVisibles = 0;
    
    filas.forEach((fila, index) => {
        const textoFila = fila.textContent.toLowerCase();
        const estadoFila = fila.getAttribute('data-estado');
        
        // Aplicar filtros
        const coincideBusqueda = textoFila.includes(terminoBusqueda);
        const coincideEstado = filtroEstado === 'todos' || estadoFila === filtroEstado;
        const mostrarFila = coincideBusqueda && coincideEstado;
        
        if (mostrarFila) {
            filasVisibles++;
            const mostrarEnPagina = filasVisibles > (paginaActual - 1) * filasPorPagina && 
                                   filasVisibles <= paginaActual * filasPorPagina;
            fila.style.display = mostrarEnPagina ? '' : 'none';
        } else {
            fila.style.display = 'none';
        }
    });
    
    generarPaginacion(filasVisibles);
}

function generarPaginacion(totalFilas) {
    const totalPaginas = Math.ceil(totalFilas / filasPorPagina);
    const paginacion = document.getElementById('paginacion');
    paginacion.innerHTML = '';
    
    if (totalPaginas <= 1) return;
    
    // Botón anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
    liAnterior.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">Anterior</a>`;
    paginacion.appendChild(liAnterior);
    
    // Números de página
    const inicio = Math.max(1, paginaActual - 2);
    const fin = Math.min(totalPaginas, inicio + 4);
    
    for (let i = inicio; i <= fin; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>`;
        paginacion.appendChild(li);
    }
    
    // Botón siguiente
    const liSiguiente = document.createElement('li');
    liSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    liSiguiente.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">Siguiente</a>`;
    paginacion.appendChild(liSiguiente);
}

function cambiarPagina(pagina) {
    paginaActual = pagina;
    actualizarTabla();
}

function editarChat(idChat) {
    fetch('./getEditarRecordatorioPaciente.php?action=obtener_chat&id=' + idChat)
        .then(response => response.json())
        .then(chat => {
            if (chat.success) {
                document.getElementById('editIdChat').value = chat.data.id;
                document.getElementById('editChatId').value = chat.data.chat_id;
                document.getElementById('editUsername').value = chat.data.username_telegram || '';
                document.getElementById('editFirstName').value = chat.data.first_name || '';
                document.getElementById('editLastName').value = chat.data.last_name || '';
                
                const modal = new bootstrap.Modal(document.getElementById('modalEditarChat'));
                modal.show();
            } else {
                Swal.fire('Error', chat.mensaje, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar los datos del chat', 'error');
        });
}

function guardarEdicion() {
    const formData = new FormData(document.getElementById('formEditarChat'));
    
    fetch('./getEditarRecordatorioPaciente.php?action=editar_chat', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(resultado => {
        if (resultado.success) {
            Swal.fire('¡Éxito!', resultado.mensaje, 'success')
                .then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarChat')).hide();
                    location.reload();
                });
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar los cambios', 'error');
    });
}

function probarMensaje(idChat) {
    Swal.fire({
        title: '¿Enviar mensaje de prueba?',
        text: 'Se enviará un mensaje de prueba a este paciente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('./getEditarRecordatorioPaciente.php?action=probar_mensaje&id=' + idChat)
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.success) {
                        Swal.fire('¡Éxito!', resultado.mensaje, 'success');
                    } else {
                        Swal.fire('Error', resultado.mensaje, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al enviar mensaje de prueba', 'error');
                });
        }
    });
}

function desactivarChat(idChat) {
    Swal.fire({
        title: '¿Desactivar registro?',
        text: 'El paciente dejará de recibir recordatorios',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('./getEditarRecordatorioPaciente.php?action=desactivar_chat&id=' + idChat)
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.success) {
                        Swal.fire('¡Éxito!', resultado.mensaje, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', resultado.mensaje, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al desactivar el registro', 'error');
                });
        }
    });
}

function reactivarChat(idChat) {
    Swal.fire({
        title: '¿Reactivar registro?',
        text: 'El paciente volverá a recibir recordatorios',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('./getEditarRecordatorioPaciente.php?action=reactivar_chat&id=' + idChat)
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.success) {
                        Swal.fire('¡Éxito!', resultado.mensaje, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', resultado.mensaje, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al reactivar el registro', 'error');
                });
        }
    });
}

// Event listeners
document.getElementById('buscarPaciente').addEventListener('input', buscarChats);
document.addEventListener('DOMContentLoaded', function() {
    actualizarTabla();
});
</script>
</body>
</html>
<?php
        $this->pieShow();
    }
}
?>