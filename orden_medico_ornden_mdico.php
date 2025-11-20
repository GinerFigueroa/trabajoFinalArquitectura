





C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\OrdenExamenDAO.php
<?php
include_once('conexion.php');

class OrdenExamenDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las órdenes de examen
     */
    public function obtenerTodasOrdenes()
{
    $sql = "SELECT 
                    oe.id_orden,
                    oe.historia_clinica_id,
                    oe.id_medico,
                    oe.fecha,
                    oe.tipo_examen,
                    oe.indicaciones,
                    oe.estado,
                    oe.resultados,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
            FROM orden_examen oe
            JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
            JOIN medicos m ON oe.id_medico = m.id_medico 
            JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
            
            ORDER BY oe.fecha DESC, oe.id_orden DESC";

        $resultado = $this->connection->query($sql);
        $ordenes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ordenes[] = $fila;
            }
        }

        return $ordenes;
    }

    /**
 * Obtiene información del médico por ID de usuario
 */
public function obtenerMedicoPorIdUsuario($idUsuario)
{
    $sql = "SELECT 
                m.id_medico,
                u.id_usuario,
                CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
                m.cedula_profesional,
                e.nombre as especialidad
            FROM usuarios u
            JOIN medicos m ON u.id_usuario = m.id_usuario
            LEFT JOIN especialidades_medicas e ON m.id_especialidad = e.id_especialidad
            WHERE u.id_usuario = ? AND u.id_rol = 2 AND u.activo = 1";

    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $medico = $resultado->fetch_assoc();
    $stmt->close();

    return $medico;
}

/**
 * Verifica si un usuario es médico
 */
public function esUsuarioMedico($idUsuario)
{
    $sql = "SELECT COUNT(*) as count 
            FROM usuarios u 
            WHERE u.id_usuario = ? AND u.id_rol = 2 AND u.activo = 1";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $stmt->close();
    
    return $fila['count'] > 0;
}

/**
 * Obtiene o crea el id_medico a partir del id_usuario (método robusto)
 */
public function obtenerOcrearIdMedicoPorUsuario($idUsuario)
{
    // Primero intentar obtener el id_medico existente
    $idMedico = $this->obtenerIdMedicoPorUsuario($idUsuario);
    
    if ($idMedico) {
        return $idMedico;
    }
    
    // Si no existe, verificar que el usuario es médico
    if ($this->esUsuarioMedico($idUsuario)) {
        // Crear registro en médicos automáticamente
        $sql = "INSERT INTO medicos (id_usuario, cedula_profesional) VALUES (?, ?)";
        $stmt = $this->connection->prepare($sql);
        $cedula = "MED" . str_pad($idUsuario, 5, '0', STR_PAD_LEFT);
        $stmt->bind_param("is", $idUsuario, $cedula);
        
        if ($stmt->execute()) {
            $idMedico = $this->connection->insert_id;
            $stmt->close();
            return $idMedico;
        }
        $stmt->close();
    }
    
    return null;
}
    /**
     * Obtiene una orden específica por ID
     */
    public function obtdddenerOrdenPorId($idOrden)
    {
        $sql = "SELECT 
                    oe.*,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
                FROM orden_examen oe
                JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN usuarios u_med ON oe.id_medico = u_med.id_usuario
                WHERE oe.id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $orden = $resultado->fetch_assoc();
        $stmt->close();

        return $orden;
    }
    /**
 * Obtiene una orden específica por ID - VERSIÓN CORREGIDA
 */
public function obtenerOrdenPorId($idOrden)
{
    $sql = "SELECT 
                oe.*,
                CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                p.dni,
                u_med.id_usuario,
                CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico,
                hc.id_paciente
            FROM orden_examen oe
            JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
            JOIN medicos m ON oe.id_medico = m.id_medico  -- Cambio importante aquí
            JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario  -- Y aquí
            WHERE oe.id_orden = ?";

    error_log("DEBUG: Buscando orden con ID: " . $idOrden);
    
    $stmt = $this->connection->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->connection->error);
        return null;
    }
    
    $stmt->bind_param("i", $idOrden);
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        $stmt->close();
        return null;
    }
    
    $resultado = $stmt->get_result();
    $orden = $resultado->fetch_assoc();
    $stmt->close();

    

    return $orden;
}

/**
 * Obtiene el ID del médico asociado a una orden
 */
public function obtenerIdMedicoPorOrden($idOrden)
{
    $sql = "SELECT id_medico FROM orden_examen WHERE id_orden = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idOrden);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($fila = $resultado->fetch_assoc()) {
        return $fila['id_medico'];
    }
    
    return null;
}
    
   /**
 * Registra una nueva orden de examen
 */
public function registrarOrden($historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado = 'Pendiente', $resultados = null)
{
    // Primero obtener el id_medico a partir del id_usuario
    $idMedico = $this->obtenerIdMedicoPorUsuario($idUsuarioMedico);
    
    if (!$idMedico) {
        error_log("Error: No se pudo encontrar id_medico para id_usuario: " . $idUsuarioMedico);
        return false;
    }

    error_log("DEBUG: Conversión - id_usuario=$idUsuarioMedico -> id_medico=$idMedico");

    $sql = "INSERT INTO orden_examen 
            (historia_clinica_id, id_medico, fecha, tipo_examen, indicaciones, estado, resultados) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->connection->prepare($sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->connection->error);
        return false;
    }

    $stmt->bind_param("iisssss", 
        $historiaClinicaId, 
        $idMedico, // Usamos el id_medico convertido (8)
        $fecha, 
        $tipoExamen, 
        $indicaciones, 
        $estado, 
        $resultados
    );
    
    $resultado = $stmt->execute();
    
    if (!$resultado) {
        error_log("Error ejecutando consulta: " . $stmt->error);
    }
    
    $stmt->close();
    
    return $resultado;
}

/**
 * Obtiene el id_medico a partir del id_usuario
 */
public function obtenerIdMedicoPorUsuario($idUsuario)
{
    $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
    
    $stmt = $this->connection->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->connection->error);
        return null;
    }
    
    $stmt->bind_param("i", $idUsuario);
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        $stmt->close();
        return null;
    }
    
    $resultado = $stmt->get_result();
    
    if ($fila = $resultado->fetch_assoc()) {
        $idMedico = $fila['id_medico'];
        $stmt->close();
        return $idMedico;
    }
    
    $stmt->close();
    error_log("No se encontró médico para id_usuario: " . $idUsuario);
    return null;
}

    /**
     * Actualiza una orden existente
     */
    public function actualizarOrden($idOrden, $historiaClinicaId, $idMedico, $fecha, $tipoExamen, $indicaciones, $estado, $resultados = null)
    {
        $sql = "UPDATE orden_examen SET 
                historia_clinica_id = ?, 
                id_medico = ?, 
                fecha = ?, 
                tipo_examen = ?, 
                indicaciones = ?, 
                estado = ?, 
                resultados = ?
                WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iisssssi", 
            $historiaClinicaId, 
            $idMedico, 
            $fecha, 
            $tipoExamen, 
            $indicaciones, 
            $estado, 
            $resultados,
            $idOrden
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Actualiza solo el estado y resultados de una orden
     */
    public function actualizarResultados($idOrden, $estado, $resultados)
    {
        $sql = "UPDATE orden_examen SET 
                estado = ?, 
                resultados = ?
                WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("ssi", $estado, $resultados, $idOrden);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina una orden
     */
    public function eliminarOrden($idOrden)
    {
        $sql = "DELETE FROM orden_examen WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtiene historias clínicas para select
     */
    public function obtenerHistoriasClinicas()
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni
                FROM historia_clinica hc
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.nombre, u.apellido_paterno";

        $resultado = $this->connection->query($sql);
        $historias = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historias[] = $fila;
            }
        }

        return $historias;
    }

    /**
     * Obtiene médicos activos para select
     */
    public function obtenerMedicosActivos()
    {
        $sql = "SELECT 
                    u.id_usuario,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo,
                    m.cedula_profesional
                FROM usuarios u
                JOIN medicos m ON u.id_usuario = m.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno";

        $resultado = $this->connection->query($sql);
        $medicos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $medicos[] = $fila;
            }
        }

        return $medicos;
    }

    /**
     * Obtiene órdenes por estado
     */
    public function obtenerOrdenesPorEstado($estado)
    {
        $sql = "SELECT 
                    oe.*,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente
                FROM orden_examen oe
                JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                WHERE oe.estado = ?
                ORDER BY oe.fecha DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ordenes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ordenes[] = $fila;
            }
        }
        $stmt->close();

        return $ordenes;
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\indexOdenPrefactura.php
<?php
include_once('./formOrdenPrefactura.php');
$obj = new formOrdenPrefactura ();
$obj->formOrdenPrefacturaShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\formOrdenPrefactura.php


<?php

include_once('../../../shared/pantalla.php');
include_once('../../../modelo/ordenPagoDAO.php');

class formOrdenPrefactura extends pantalla
{
    public function formOrdenPrefacturaShow()
    {
        $this->cabeceraShow("Gestión de Órdenes de Prefactura");

        $objOrden = new OrdenPago();
        $listaOrdenes = $objOrden->obtenerTodasOrdenes();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-earmark-text-fill me-2"></i>Lista de Órdenes de Prefactura</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarOrdenPreFactura/indexAgregarOrdenPreFactura.php" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nueva Orden
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (DNI)</th>
                            <th>Concepto</th>
                            <th>Monto Estimado</th>
                            <th>F. Emisión</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaOrdenes) > 0) {
                            foreach ($listaOrdenes as $orden) { 
                                $esPendiente = ($orden['estado'] == 'Pendiente');
                                ?>
                                <tr class="<?php echo $esPendiente ? 'table-warning' : 'table-light'; ?>">
                                    <td><?php echo htmlspecialchars($orden['id_orden']); ?></td>
                                    <td><?php echo htmlspecialchars($orden['nombre_paciente'] . ' (' . $orden['dni_paciente'] . ')'); ?></td>
                                    <td><?php echo htmlspecialchars($orden['concepto']); ?></td>
                                    <td><?php echo 'S/ ' . number_format($orden['monto_estimado'], 2); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($orden['fecha_emision'])); ?></td>
                                    <td><?php echo htmlspecialchars($orden['tipo_servicio']); ?></td>
                                    <td><span class="badge bg-<?php echo $this->obtenerClaseEstado($orden['estado']); ?>"><?php echo htmlspecialchars($orden['estado']); ?></span></td>
                                    <td>
                                        <?php if ($esPendiente) { ?>
                                            <a href="./editarOrdenPrefactura/indexEditarOrdenPreFactura.php?id=<?php echo htmlspecialchars($orden['id_orden']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($orden['id_orden']); ?>)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php } ?>
                                        
                                        <a href="./emitirPreFactura/indexOndenPDF.php?id=<?php echo htmlspecialchars($orden['id_orden']); ?>" target="_blank" class="btn btn-sm btn-info" title="Generar PDF (Prefactura)">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay órdenes de prefactura registradas.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        if (confirm('¿Está seguro de que desea eliminar esta orden de pago? Solo se permite eliminar órdenes con estado Pendiente.')) {
            window.location.href = `./getOrdenPrefactura.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }

    private function obtenerClaseEstado($estado)
    {
        switch ($estado) {
            case 'Pendiente': return 'warning';
            case 'Facturada': return 'success';
            case 'Anulada': return 'danger';
            default: return 'secondary';
        }
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\getOrdenPrefactura.php


<?php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlOrdenPrefactura.php');

$objMensaje = new mensajeSistema();
$objControl = new controlOrdenPrefactura();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idOrden = $_GET['id'];
    
    if (!is_numeric($idOrden)) {
        $objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOdenPrefactura.php", "systemOut", false);
    } else {
        $objControl->eliminarOrden($idOrden);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexOdenPrefactura.php", "systemOut", false);
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\controlOrdenPrefactura.php
<?php

include_once('../../../modelo/ordenPagoDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlOrdenPrefactura
{
    private $objOrden;
    private $objMensaje;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarOrden($idOrden)
    {
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        $resultado = $this->objOrden->eliminarOrden($idOrden);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Orden de prefactura eliminada correctamente. (Solo se eliminan órdenes Pendientes).", "./indexOdenPrefactura.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la orden de prefactura o la orden ya no está Pendiente.", "./indexOdenPrefactura.php", "error");
        }
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\indexEditarOrdenPreFactura.php
<?php
include_once('./formEditarOdenPreFactura.php');
$obj = new formEditarOdenPreFactura();
$obj->formEditarOdenPreFacturaShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\formEditarOdenPreFactura.php
<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/OrdenPagoDAO.php');

class formEditarOdenPreFactura extends pantalla
{
    public function formEditarOdenPreFacturaShow()
    {
        $this->cabeceraShow('Editar Orden de Prefactura');

        $idOrden = $_GET['id'] ?? null;

        if (!$idOrden) {
            echo '<div class="alert alert-danger" role="alert">ID de Orden de Pago no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objOrden = new OrdenPago();
        $orden = $objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            echo '<div class="alert alert-danger" role="alert">Orden de Pago no encontrada.</div>';
            $this->pieShow();
            return;
        }

        $esEditable = ($orden['estado'] == 'Pendiente');
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Orden de Prefactura N° <?php echo htmlspecialchars($orden['id_orden']); ?></h4>
        </div>
        <div class="card-body">
            <?php if (!$esEditable) { ?>
                <div class="alert alert-info text-center">
                    Esta orden se encuentra en estado **<?php echo htmlspecialchars($orden['estado']); ?>** y **no puede ser editada**.
                </div>
            <?php } ?>
            
            <form action="./getEditarOrdenPreFactura.php" method="POST">
                <input type="hidden" name="idOrden" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paciente" class="form-label">Paciente:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['nombre_paciente_completo'] . ' (DNI: ' . $orden['dni_paciente'] . ')'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado Actual:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['estado']); ?>" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idCita" class="form-label">ID Cita:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_cita'] ?? 'N/A'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idInternado" class="form-label">ID Internamiento:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_internado'] ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required <?php echo $esEditable ? '' : 'disabled'; ?>><?php echo htmlspecialchars($orden['concepto']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="<?php echo htmlspecialchars($orden['monto_estimado']); ?>" required min="0.01" <?php echo $esEditable ? '' : 'disabled'; ?>>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <?php if ($esEditable) { ?>
                        <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <?php } ?>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Volver al Listado</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
        $this->pieShow();
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\getEditarOrdenPreFactura.php
<?php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarOrdenPreFactura.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    $idOrden = $_POST['idOrden'] ?? null;
    $concepto = $_POST['concepto'] ?? '';
    $monto = $_POST['monto'] ?? 0;

    if (empty($idOrden)) {
        $objMensaje->mensajeSistemaShow('ID de orden no válido.', '../indexOdenPrefactura.php', 'systemOut', false);
        return;
    }

    $objControl = new controlEditarOrdenPreFactura();
    $objControl->editarOrden($idOrden, $concepto, $monto);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexOdenPrefactura.php', 'systemOut', false);
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\controlEditarOrdenPreFactura.php
<?php

include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarOrdenPreFactura
{
    private $objOrden;
    private $objMensaje;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarOrden($idOrden, $concepto, $monto)
    {
        $urlRetorno = './indexEditarOrdenPreFactura.php?id=' . $idOrden;

        // 1. Validación de campos obligatorios
        if (empty($idOrden) || empty($concepto) || !is_numeric($monto) || $monto <= 0) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios o el monto no es válido.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Ejecutar la edición (el modelo solo permite editar si el estado es 'Pendiente')
        $resultado = $this->objOrden->editarOrden($idOrden, $concepto, $monto);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura N° ' . $idOrden . ' actualizada correctamente.', '../indexOdenPrefactura.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar la orden. La orden no se encuentra en estado "Pendiente" o no se realizaron cambios.', $urlRetorno, 'error');
        }
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\agregarOrdenPreFactura\indexAgregarOrdenPreFactura.php
<?php
include_once('./formAgregarOrdenPrefactura.php');
$objForm = new formAgregarOrdenPrefactura();
$objForm->formAgregarOrdenPrefacturaShow();
?>{

C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\agregarOrdenPreFactura\formAgregarOrdenPrefactura.php
<?php

include_once('../../../../shared/pantalla.php');

include_once('../../../../modelo/OrdenPagoDAO.php'); 



class formAgregarOrdenPrefactura extends pantalla
{
    public function formAgregarOrdenPrefacturaShow()
    {
        $this->cabeceraShow('Registrar Nueva Orden de Prefactura');

        // ✅ Uso de la clase Paciente, definida dentro del archivo OrdenPagoDAO.php
        $objPaciente = new Paciente();
        // ID_ROL = 4 es el rol del Paciente
        $pacientesDisponibles = $objPaciente->obtenerPacientesPorRol(4); 
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Orden de Prefactura</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarOrdenPreFactura.php" method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione Paciente</option>
                            <?php foreach ($pacientesDisponibles as $p) { ?>
                                <option value="<?php echo htmlspecialchars($p['id_paciente']); ?>">
                                    <?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido_paterno'] . ' (DNI: ' . $p['dni'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idCita" class="form-label">Cita Pendiente (Opcional):</label>
                        <select class="form-select" id="idCita" name="idCita">
                        </select>
                        <small class="form-text text-muted">Seleccione una cita completada sin orden de pago.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idInternado" class="form-label">Internamiento Activo/Alta (Opcional):</label>
                        <select class="form-select" id="idInternado" name="idInternado">
                        </select>
                        <small class="form-text text-muted">Seleccione un internamiento activo o ya dado de alta.</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" required min="0.01">
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnAgregar" class="btn btn-success">Generar Prefactura Pendiente</button>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('idPaciente').addEventListener('change', function() {
    const idPaciente = this.value;
    const selectCitas = document.getElementById('idCita');
    const selectInternados = document.getElementById('idInternado');
    
    // Limpiar selects e inicializarlos
    selectCitas.innerHTML = '<option value="">-- Seleccionar Cita --</option>';
    selectInternados.innerHTML = '<option value="">-- Seleccionar Internamiento --</option>';

    if (idPaciente) {
        // Lógica AJAX para cargar Citas
        fetch('getAgregarOrdenPreFactura.php?action=citas&id=' + idPaciente)
            .then(response => response.json())
            .then(data => {
                data.forEach(cita => {
                    
                    // --- CORRECCIÓN CRUCIAL DE FORMATO DE FECHA ---
                    // 1. Reemplazar el espacio con 'T' para forzar el formato ISO 8601, que es más universal.
                    const fechaISO = cita.fecha_hora.replace(' ', 'T');
                    const fechaObj = new Date(fechaISO);
                    
                    let fechaVisible = cita.fecha_hora; // Usar el string original como fallback
                    
                    if (!isNaN(fechaObj)) {
                        // 2. Si la fecha es válida, la formateamos
                        fechaVisible = fechaObj.toLocaleString('es-ES', { 
                            day: '2-digit', month: '2-digit', year: 'numeric', 
                            hour: '2-digit', minute: '2-digit' 
                        });
                    }
                    // --- FIN CORRECCIÓN ---
                    
                    const option = document.createElement('option');
                    option.value = cita.id_cita;
                    // Construcción completa del texto de la cita
                    option.textContent = `Cita #${cita.id_cita} - ${fechaVisible} (${cita.nombre_tratamiento} - Dr/a. ${cita.nombre_medico})`;
                    selectCitas.appendChild(option);
                });
            })
            // Es buena práctica añadir un catch para ver si falla la conexión
            .catch(error => console.error('Error al cargar citas o JSON inválido:', error)); 

        // Lógica AJAX para cargar Internamientos (Sin cambios)
        fetch('getAgregarOrdenPreFactura.php?action=internados&id=' + idPaciente)
            .then(response => response.json())
            .then(data => {
                data.forEach(internado => {
                    const option = document.createElement('option');
                    option.value = internado.id_internado;
                    option.textContent = `Int. #${internado.id_internado} - Hab. ${internado.habitacion_numero} (${internado.estado})`;
                    selectInternados.appendChild(option);
                });
            });
    }
    
    // El bloque fetch extra de citas al final puede eliminarse ya que es redundante,
    // pero lo dejo sin modificar para cumplir la solicitud de no cambiar la lógica de la vista si funciona.

    fetch('getAgregarOrdenPreFactura.php?action=citas&id=' + idPaciente)
    .then(response => {
        console.log('Respuesta de la red:', response);
        if (!response.ok) {
            throw new Error('Error en la respuesta de la red');
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos de citas recibidos:', data);
        if (Array.isArray(data)) {
            if (data.length === 0) {
                console.log('No hay citas para este paciente.');
                // Añadir una opción que diga que no hay citas?
                const option = document.createElement('option');
                option.textContent = 'No hay citas completadas';
                option.value = '';
                selectCitas.appendChild(option);
            } else {
                data.forEach(cita => {
                    console.log('Procesando cita:', cita);
                    const option = document.createElement('option');
                    option.value = cita.id_cita;
                    // Formatear fecha
                    const fecha = new Date(cita.fecha_hora.replace(' ', 'T'));
                    const fechaFormateada = fecha.toLocaleString('es-ES');
                    option.textContent = `Cita #${cita.id_cita} - ${fechaFormateada} (${cita.nombre_tratamiento}) - Dr. ${cita.nombre_medico}`;
                    selectCitas.appendChild(option);
                });
            }
        } else {
            console.error('La respuesta no es un array:', data);
        }
    })
    .catch(error => {
        console.error('Error al cargar citas:', error);
    });

});
</script>

<?php
        $this->pieShow();
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\agregarOrdenPreFactura\getAgregarOrdenPreFactura.php
<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\agregarOrdenPreFactura\getAgregarOrdenPreFactura.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarOdenPreFactura.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarOdenPreFactura();

// --- 1. Manejo de Solicitudes AJAX para cargar Citas/Internados ---
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idPaciente = (int)$_GET['id'];
    
    // Configuración de respuesta JSON
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'citas') {
        $citas = $objControl->obtenerCitasPorPaciente($idPaciente);
        echo json_encode($citas);
        exit();
    } elseif ($_GET['action'] == 'internados') {
        $internados = $objControl->obtenerInternadosPorPaciente($idPaciente);
        echo json_encode($internados);
        exit();
    }
}

// --- 2. Manejo de Registro (POST) ---
if (isset($_POST['btnAgregar'])) {
    $idPaciente = $_POST['idPaciente'] ?? null;
    $idCita = $_POST['idCita'] ?? null;
    $idInternado = $_POST['idInternado'] ?? null;
    $concepto = $_POST['concepto'] ?? '';
    $monto = $_POST['monto'] ?? 0;

    $objControl->agregarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto);
} else {
    // Si no es POST ni AJAX válido
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexOdenPrefactura.php', 'systemOut', false);
}

if ($_GET['action'] == 'citas') {
    $citas = $objControl->obtenerCitasPorPaciente($idPaciente);
    // Temporal: log para depuración
    error_log("Citas para paciente $idPaciente: " . print_r($citas, true));
    echo json_encode($citas);
    exit();
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\agregarOrdenPreFactura\controlAgregarOdenPreFactura.php
<?php

include_once('../../../../modelo/OrdenPagoDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

class controlAgregarOdenPreFactura
{
    private $objOrden;
    private $objPaciente;
    private $objCita;    // Ahora usará la clase EntidadCitas (el nombre que definimos)
    private $objInternado; // Ahora usará la clase EntidadInternados (el nombre que definimos)
    private $objMensaje;

    public function __construct()
    {
        
        // Se instancian las clases definidas dentro del archivo OrdenPagoDAO.php
        $this->objOrden = new OrdenPago();
        $this->objPaciente = new Paciente(); 
        
        // Instancia directa de las clases auxiliares que contienen las consultas
        // y que ahora están definidas en el archivo unificado.
        $this->objCita = new EntidadCitas(); 
        $this->objInternado = new EntidadInternados(); 
        
        $this->objMensaje = new mensajeSistema();
    }

    // --- Métodos para AJAX ---
    public function obtenerCitasPorPaciente($idPaciente)
    {
        // El método de la clase EntidadCitas es correcto
        return $this->objCita->obtenerCitasPendientesPorPaciente($idPaciente); 
    }
    
    public function obtenerInternadosPorPaciente($idPaciente)
    {
        // El método de la clase EntidadInternados es correcto
        return $this->objInternado->obtenerInternamientosPorPaciente($idPaciente);
    }
    // -------------------------


    public function agregarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto)
    {
        $urlRetorno = '../indexOdenPrefactura.php';

        // 1. Sanitización y validación de campos obligatorios
        $idPaciente = (int)$idPaciente;
        $monto = (float)$monto;
        $idCita = empty($idCita) ? NULL : (int)$idCita;
        $idInternado = empty($idInternado) ? NULL : (int)$idInternado;

        if ($idPaciente <= 0 || empty($concepto) || $monto <= 0) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios (Paciente, Concepto, Monto) o no son válidos.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Validación de servicio: Debe estar asociado a Cita O Internamiento
        if ($idCita === NULL && $idInternado === NULL) {
            $this->objMensaje->mensajeSistemaShow("La orden debe estar asociada a un ID de Cita o un ID de Internamiento.", $urlRetorno, 'systemOut', false);
            return;
        }
        
        // 3. Ejecutar el registro
        $resultado = $this->objOrden->registrarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura registrada correctamente con estado "Pendiente".', $urlRetorno, 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al registrar la Orden de Prefactura. Verifique los IDs de Cita/Internamiento.', $urlRetorno, 'error');
        }
    }
}
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\indexEditarOrdenPreFactura.php

<?php
include_once('./formEditarOdenPreFactura.php');
$obj = new formEditarOdenPreFactura();
$obj->formEditarOdenPreFacturaShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\formEditarOdenPreFactura.php
<?php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/OrdenPagoDAO.php');

class formEditarOdenPreFactura extends pantalla
{
    public function formEditarOdenPreFacturaShow()
    {
        $this->cabeceraShow('Editar Orden de Prefactura');

        $idOrden = $_GET['id'] ?? null;

        if (!$idOrden) {
            echo '<div class="alert alert-danger" role="alert">ID de Orden de Pago no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        $objOrden = new OrdenPago();
        $orden = $objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            echo '<div class="alert alert-danger" role="alert">Orden de Pago no encontrada.</div>';
            $this->pieShow();
            return;
        }

        $esEditable = ($orden['estado'] == 'Pendiente');
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Orden de Prefactura N° <?php echo htmlspecialchars($orden['id_orden']); ?></h4>
        </div>
        <div class="card-body">
            <?php if (!$esEditable) { ?>
                <div class="alert alert-info text-center">
                    Esta orden se encuentra en estado **<?php echo htmlspecialchars($orden['estado']); ?>** y **no puede ser editada**.
                </div>
            <?php } ?>
            
            <form action="./getEditarOrdenPreFactura.php" method="POST">
                <input type="hidden" name="idOrden" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paciente" class="form-label">Paciente:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['nombre_paciente_completo'] . ' (DNI: ' . $orden['dni_paciente'] . ')'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado Actual:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['estado']); ?>" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idCita" class="form-label">ID Cita:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_cita'] ?? 'N/A'); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idInternado" class="form-label">ID Internamiento:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($orden['id_internado'] ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required <?php echo $esEditable ? '' : 'disabled'; ?>><?php echo htmlspecialchars($orden['concepto']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="<?php echo htmlspecialchars($orden['monto_estimado']); ?>" required min="0.01" <?php echo $esEditable ? '' : 'disabled'; ?>>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <?php if ($esEditable) { ?>
                        <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <?php } ?>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Volver al Listado</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
        $this->pieShow();
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\getEditarOrdenPreFactura.php
<?php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarOrdenPreFactura.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    $idOrden = $_POST['idOrden'] ?? null;
    $concepto = $_POST['concepto'] ?? '';
    $monto = $_POST['monto'] ?? 0;

    if (empty($idOrden)) {
        $objMensaje->mensajeSistemaShow('ID de orden no válido.', '../indexOdenPrefactura.php', 'systemOut', false);
        return;
    }

    $objControl = new controlEditarOrdenPreFactura();
    $objControl->editarOrden($idOrden, $concepto, $monto);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexOdenPrefactura.php', 'systemOut', false);
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\editarOrdenPrefactura\controlEditarOrdenPreFactura.php
<?php

include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarOrdenPreFactura
{
    private $objOrden;
    private $objMensaje;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarOrden($idOrden, $concepto, $monto)
    {
        $urlRetorno = './indexEditarOrdenPreFactura.php?id=' . $idOrden;

        // 1. Validación de campos obligatorios
        if (empty($idOrden) || empty($concepto) || !is_numeric($monto) || $monto <= 0) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios o el monto no es válido.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Ejecutar la edición (el modelo solo permite editar si el estado es 'Pendiente')
        $resultado = $this->objOrden->editarOrden($idOrden, $concepto, $monto);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura N° ' . $idOrden . ' actualizada correctamente.', '../indexOdenPrefactura.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar la orden. La orden no se encuentra en estado "Pendiente" o no se realizaron cambios.', $urlRetorno, 'error');
        }
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\emitirPreFactura\indexOndenPDF.php
<?php

include_once('./controlOrdenPDF.php');
$obj = new controlOrdenPDF();
$obj->generarPDF();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\emitirPreFactura\formOdenPDF.php
<?php

require_once('../../../../dompdf/autoload.inc.php'); // Asegúrate que esta ruta sea correcta
use Dompdf\Dompdf;

class formOdenPDF
{
    public function generarPDFShow($orden)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlOrden($orden);

        // 2. Configurar y renderizar Dompdf
        $dompdf = new Dompdf();
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);

        // Configurar tamaño de papel
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar PDF
        $dompdf->render();

        // Mostrar el PDF en el navegador
        $nombreArchivo = "Prefactura-N-" . $orden['id_orden'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlOrden($orden)
    {
        // Define la estructura básica de la prefactura
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { color: #007bff; }
                .info-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; }
                .table-items th, .table-items td { border: 1px solid #ddd; padding: 8px; }
                .table-items { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .footer { margin-top: 50px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>CLÍNICA VIDA</h2>
                <h3>ORDEN DE PREFACTURA N° ' . htmlspecialchars($orden['id_orden']) . '</h3>
            </div>

            <div class="info-box">
                <strong>Emitido por:</strong> Recepción<br>
                <strong>Fecha de Emisión:</strong> ' . date('d/m/Y H:i', strtotime($orden['fecha_emision'])) . '<br>
                <strong>Estado:</strong> ' . htmlspecialchars($orden['estado']) . '
            </div>

            <h4>Datos del Paciente</h4>
            <div class="info-box">
                <strong>Nombre Completo:</strong> ' . htmlspecialchars($orden['nombre_paciente_completo']) . '<br>
                <strong>DNI:</strong> ' . htmlspecialchars($orden['dni_paciente']) . '<br>
            </div>

            <h4>Detalle del Servicio (Monto Estimado)</h4>
            <table class="table-items">
                <thead>
                    <tr>
                        <th style="width: 70%; text-align: left;">Concepto</th>
                        <th style="width: 30%; text-align: right;">Monto Estimado (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: left;">' . nl2br(htmlspecialchars($orden['concepto'])) . '</td>
                        <td style="text-align: right;">' . number_format($orden['monto_estimado'], 2) . '</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right; font-weight: bold;">TOTAL ESTIMADO:</td>
                        <td style="text-align: right; font-weight: bold;">S/ ' . number_format($orden['monto_estimado'], 2) . '</td>
                    </tr>
                </tfoot>
            </table>

            <div class="footer">
                <p>--- ESTE NO ES UN COMPROBANTE DE PAGO ---</p>
                <p>Presente esta orden en Caja para proceder con la facturación final.</p>
                <p>Generado por Recepcionista.</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\recepcion\generarOrdenPrefactura\emitirPreFactura\controlOrdenPDF.php
<?php

include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('./formOdenPDF.php'); // Vista que contiene la lógica de Dompdf

class controlOrdenPDF
{
    private $objOrden;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formOdenPDF();
    }

    public function generarPDF()
    {
        $idOrden = $_GET['id'] ?? null;
        
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de Orden de Pago no proporcionado o no válido.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        $orden = $this->objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            $this->objMensaje->mensajeSistemaShow("Orden de Pago no encontrada.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        // Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($orden);
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\shared\mensajeSistema.php
<?php
/**
 * Patrón: Service Layer / Utility
 * Responsabilidad: Encapsular la lógica de notificación y redirección del sistema.
 */
class mensajeSistema
{

    /**
     * Patrón: Strategy (Presentación Dinámica)
     * El comportamiento visual (colores, íconos) cambia basado en el parámetro $tipo.
     */
    public function mensajeSistemaShow($mensaje, $ruta, $tipo = "error")
    {
        $suceso = ($tipo === "success");
        $icono = $suceso ? "bi-check-circle-fill" : "bi-exclamation-circle-fill";
        $colorHeader = $suceso ? "bg-success" : "bg-danger";
        $colorBoton = $suceso ? "btn-success" : "btn-danger";
        $titulo = $suceso ? "¡Éxito!" : "¡Error!";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Mensajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="modalMensaje" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header <?php echo $colorHeader; ?> text-white">
                    <h5 class="modal-title">
                        <i class="bi <?php echo $icono; ?> me-2"></i>
                        <?php echo $titulo; ?>
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi <?php echo $suceso ? 'bi-check-circle' : 'bi-x-circle'; ?> display-4 <?php echo $suceso ? 'text-success' : 'text-danger'; ?>"></i>
                    </div>
                    <h5 class="mb-3"><?php echo $mensaje; ?></h5>
                    <p class="text-muted">Será redirigido automáticamente...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn <?php echo $colorBoton; ?> w-100" onclick="redirigir()">
                        <i class="bi bi-check-lg me-2"></i>Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirigir() {
            window.location.href = "<?php echo $ruta; ?>";
        }
        
        // Redirección automática después de 3 segundos
        setTimeout(function() {
            redirigir();
        }, 3000);
        
        // Mostrar modal automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.querySelector('.modal'));
            modal.show();
        });
    </script>
</body>
</html>
<?php
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\shared\pantalla.php

<?php

class pantalla
{
    public function cabeceraShow($titulo = "")
    {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - Gestion Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-house-gear-fill me-2"></i>
                 gestion hospital 
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo $_SESSION['login'] ?? 'Invitado'; ?>
                </span>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
<?php
    }
    
    public function pieShow()
    {
?>
    </div>
    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.1);">
            © 2025  Sistema de gestión de pacientes para un hospital - Todos los derechos reservados
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    }
}
?>
