C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\conexion.php

<?php

class Conexion
{
    // Almacena la única instancia de la clase
    private static $instancia = null;
    // Almacena la conexión mysqli
    private $connection;
    
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbName = 'opipitaltrabajo';

    // 1. Constructor privado: Inicializa la conexión solo una vez
    private function __construct()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->dbName);
        if ($this->connection->connect_error) {
            die("Conexión fallida: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8");
    }

    // 2. Método estático: Punto de acceso único a la instancia
    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia;
    }

    // 3. Método para obtener el objeto mysqli
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Cierra la conexión y libera la instancia Singleton.
     * Solo debe llamarse una vez al final de la ejecución del script.
     */
    public function cerrarConexion()
    {
        if ($this->connection) {
            $this->connection->close();
            self::$instancia = null; // Reinicia la instancia por si se necesita otra
        }
    }

    // Métodos para prevenir la clonación y deserialización
    private function __clone() {}
    public function __wakeup() {}
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\InternadoSeguimientoDAO.php
<?php
include_once('conexion.php'); 
include_once('EntidadAuxiliarDAO.php');

class InternadoSeguimientoDAO
{
    private $connection;
    private $objAuxiliar;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->objAuxiliar = new EntidadAuxiliarDAO();
    }

    /**
     * Obtiene todos los seguimientos, con los nombres completos de Paciente, Médico y Enfermera.
     * @return array
     */
    public function obtenerTodosSeguimientos()
    {
        $sql = "SELECT id_seguimiento, id_internado, fecha, id_medico, id_enfermera, evolucion, tratamiento
                FROM internados_seguimiento
                ORDER BY fecha DESC";
        
        $resultado = $this->connection->query($sql);
        $seguimientos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Obtener nombres auxiliares
                $fila['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorInternado($fila['id_internado']);
                $fila['nombre_medico'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($fila['id_medico']);
                $fila['nombre_enfermera'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($fila['id_enfermera']);
                $seguimientos[] = $fila;
            }
        }
        return $seguimientos;
    }

    /**
     * Obtiene un seguimiento específico por ID.
     * @param int $idSeguimiento
     * @return array|null
     */
    public function obtenerSeguimientoPorId($idSeguimiento)
    {
        $sql = "SELECT * FROM internados_seguimiento WHERE id_seguimiento = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idSeguimiento);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $seguimiento = $resultado->fetch_assoc();
        $stmt->close();

        if ($seguimiento) {
             // Opcional: enriquecer con nombres para la vista de edición
             $seguimiento['nombre_medico'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($seguimiento['id_medico']);
             $seguimiento['nombre_enfermera'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($seguimiento['id_enfermera']);
             $seguimiento['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorInternado($seguimiento['id_internado']);
        }
        return $seguimiento;
    }

    /**
     * Registra un nuevo seguimiento.
     * @return bool
     */
    public function registrarSeguimiento($idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento)
    {
        $sql = "INSERT INTO internados_seguimiento (id_internado, id_medico, id_enfermera, evolucion, tratamiento)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        // Tipos: i = integer, s = string. Asumimos que evolucion y tratamiento son strings largos.
        $stmt->bind_param("iiiss", $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita un seguimiento existente.
     * @return bool
     */
    public function editarSeguimiento($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento)
    {
        $sql = "UPDATE internados_seguimiento 
                SET id_internado = ?, id_medico = ?, id_enfermera = ?, evolucion = ?, tratamiento = ?
                WHERE id_seguimiento = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iiissi", $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento, $idSeguimiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina un seguimiento de la base de datos.
     * @return bool
     */
    public function eliminarSeguimiento($idSeguimiento)
    {
        $stmt = $this->connection->prepare("DELETE FROM internados_seguimiento WHERE id_seguimiento = ?");
        $stmt->bind_param("i", $idSeguimiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}

C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\indexEvolucionClinicaPacienteHospitalizado.php
<?php

include_once('./formEvolucionPacienteHospitalizado.php');
$obj = new formEvolucionPacienteHospitalizado();
$obj->formEvolucionPacienteHospitalizadoShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\formEvolucionPacienteHospitalizado.php
<?php
include_once("../../../shared/pantalla.php");
include_once("../../../modelo/InternadoSeguimientoDAO.php"); 

class formEvolucionPacienteHospitalizado extends pantalla
{
    public function formEvolucionPacienteHospitalizadoShow()
    {
        $this->cabeceraShow("Gestión de Evolución Clínica");

        $objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $listaSeguimientos = $objSeguimientoDAO->obtenerTodosSeguimientos();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-heart-pulse-fill me-2"></i>Seguimiento Clínico de Pacientes Hospitalizados</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./agregarEvolucionPacienteHospitalizado/indexaAgregarEvolucionPaciente.php" class="btn btn-success">
                        <i class="bi bi-journal-plus me-2"></i>Registrar Nueva Evolución
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Seguimiento</th>
                            <th>Paciente</th>
                            <th>Fecha</th>
                            <th>Médico</th>
                            <th>Enfermera</th>
                            <th>Evolución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaSeguimientos) > 0) {
                            foreach ($listaSeguimientos as $seguimiento) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?></td>
                                    <td><?php echo htmlspecialchars($seguimiento['nombre_paciente'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime(htmlspecialchars($seguimiento['fecha']))); ?></td>
                                    <td><?php echo htmlspecialchars($seguimiento['nombre_medico'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($seguimiento['nombre_enfermera'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($seguimiento['evolucion'], 0, 50) . (strlen($seguimiento['evolucion']) > 50 ? '...' : '')); ?></td>
                                    <td>
                                        <a href="./editarEvolucionPacienteHospitalizado/indexEditarPacienteHospitazado.php?id=<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay evoluciones clínicas registradas.</td>
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
        if (confirm('¿Está seguro de que desea eliminar este registro de evolución? Esta acción es irreversible.')) {
            window.location.href = `./getEvolucionPacienteHopitalizado.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\getEvolucionPacienteHopitalizado.php
<?php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPacienteHospitalizado.php');

$objControl = new controlEvolucionPacienteHospitalizado();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idSeguimiento = $_GET['id'];
    
    if (!is_numeric($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de seguimiento no válido.", "./indexEvolucionClinicaPacienteHospitalizado.php", "error"); 
    } else {
        $objControl->eliminarSeguimiento($idSeguimiento);
    }
} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: ./indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\controlEvolucionPacienteHospitalizado.php
<?php
include_once('../../../modelo/InternadoSeguimientoDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlEvolucionPacienteHospitalizado
{
    private $objSeguimientoDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarSeguimiento($idSeguimiento)
    {
        $resultado = $this->objSeguimientoDAO->eliminarSeguimiento($idSeguimiento);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Registro de evolución eliminado correctamente.', './indexEvolucionClinicaPacienteHospitalizado.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al eliminar el registro de evolución.', './indexEvolucionClinicaPacienteHospitalizado.php', 'error');
        }
    }
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\editarEvolucionPacienteHospitalizado\indexEditarPacienteHospitazado.php
<?php

include_once('./formEditarPacienteHospitalizado.php');
$obj = new formEditarPacienteHospitalizado();
$obj->formEditarPacienteHospitalizadoShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\editarEvolucionPacienteHospitalizado\formEditarPacienteHospitalizado.php
<?php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/InternadoSeguimientoDAO.php');


class formEditarPacienteHospitalizado extends pantalla
{
    public function formEditarPacienteHospitalizadoShow()
    {
        $this->cabeceraShow('Editar Evolución Clínica');

        $idSeguimiento = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$idSeguimiento) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("ID de registro de seguimiento no proporcionado.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }

        $objSeguimiento = new InternadoSeguimientoDAO();
        $objAuxiliar = new EntidadAuxiliarDAO();

        $seguimiento = $objSeguimiento->obtenerSeguimientoPorId($idSeguimiento);
        $internados = $objAuxiliar->obtenerInternadosActivosConNombrePaciente();
        $medicos = $objAuxiliar->obtenerMedicosActivos();
        $enfermeros = $objAuxiliar->obtenerEnfermerosActivos();

        if (!$seguimiento) {
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow("Registro de seguimiento no encontrado.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-fill me-2"></i>Editar Evolución Clínica (ID: <?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>)</h4>
        </div>
        <div class="card-body">
            <form action="./getEditaraPacienteHospitalizado.php" method="POST">
                 
                
                <input type="hidden" name="idSeguimiento" value="<?php echo htmlspecialchars($seguimiento['id_seguimiento']); ?>">
                
                <div class="mb-3">
                    <label for="idInternado" class="form-label">Paciente Hospitalizado:</label>
                    <select class="form-select" id="idInternado" name="idInternado" required>
                        <option value="">-- Seleccione un Paciente (Internado Activo) --</option>
                        <?php foreach ($internados as $internado) { 
                            $selected = ($internado['id_internado'] == $seguimiento['id_internado']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($internado['id_internado']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars("ID: {$internado['id_internado']} - {$internado['nombre_completo']} - Ingreso: " . date('d/m/Y', strtotime($internado['fecha_ingreso']))); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante:</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach ($medicos as $medico) { 
                                $selected = ($medico['id_usuario'] == $seguimiento['id_medico']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($medico['id_usuario']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars("{$medico['nombre']} {$medico['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
      
                    <div class="col-md-6 mb-3">
                        <label for="idEnfermera" class="form-label">Enfermera (Opcional):</label>
                        <select class="form-select" id="idEnfermera" name="idEnfermera">
                            <option value="">-- Seleccione una Enfermera --</option>
                            <?php foreach ($enfermeros as $enfermero) { 
                                $selected = ($enfermero['id_usuario'] == $seguimiento['id_enfermera']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($enfermero['id_usuario']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars("{$enfermero['nombre']} {$enfermero['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evolucion" class="form-label">Evolución Clínica:</label>
                    <textarea class="form-control" id="evolucion" name="evolucion" rows="5" required><?php echo htmlspecialchars($seguimiento['evolucion']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Tratamiento/Indicaciones:</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5"><?php echo htmlspecialchars($seguimiento['tratamiento']); ?></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning text-white"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Evolución</button>
                    <a href="../indexEvolucionClinicaPacienteHospitalizado.php" class="btn btn-secondary">Cancelar</a>
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
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\editarEvolucionPacienteHospitalizado\getEditaraPacienteHospitalizado.php
<?php

session_start();

include_once('./controlEditarPacienteHospitalizado.php');
$objControl = new controlEditarPacienteHospitalizado();
include_once('../../../../shared/mensajeSistema.php');
$objMensaje = new mensajeSistema();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar ID de seguimiento
    $idSeguimiento = isset($_POST['idSeguimiento']) ? (int)$_POST['idSeguimiento'] : null;

    if (empty($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de registro de seguimiento faltante o no válido.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        exit();
    }
    
    // Recoger y limpiar datos del formulario
    $idInternado = isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null;
    $idMedico = isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null;
    $idEnfermera = isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null;
    $evolucion = isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '';
    $tratamiento = isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '';

    // Llamar al controlador para editar
    $objControl->editarEvolucion($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
    
} else {
    // Si no es POST, redirigir al formulario principal de gestión
    header("Location: ../indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\editarEvolucionPacienteHospitalizado\controlEditarPacienteHospitalizado.php
<?php

include_once('../../../../modelo/InternadoSeguimientoDAO.php');

include_once('../../../../shared/mensajeSistema.php');

class controlEditarPacienteHospitalizado
{
    private $objSeguimientoDAO;
    private $objAuxiliarDAO; // Nuevo objeto auxiliar
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); // Instanciamos el auxiliar
        $this->objMensaje = new mensajeSistema();
    }

public function editarEvolucion($idSeguimiento, $idInternado, $idUsuarioMedico, $idUsuarioEnfermera, $evolucion, $tratamiento)
{
    $rutaRetorno = "./indexEditarPacienteHospitazado.php?id={$idSeguimiento}";
    
    // 1. Validaciones básicas
    if (empty($idSeguimiento) || empty($idInternado) || empty($idUsuarioMedico) || empty($evolucion)) {
        $this->objMensaje->mensajeSistemaShow("Los campos obligatorios están incompletos.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 2. Validar que el seguimiento existe
    if (!$this->objSeguimientoDAO->obtenerSeguimientoPorId($idSeguimiento)) {
        $this->objMensaje->mensajeSistemaShow("El registro de evolución no existe.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        return;
    }

    // 3. Validar que el usuario médico existe y es médico
    if (!$this->objAuxiliarDAO->validarUsuarioEsMedico($idUsuarioMedico)) {
        $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como médico no es un médico válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 4. Convertir id_usuario_medico a id_medico
    $idMedico = $this->objAuxiliarDAO->obtenerIdMedicoPorIdUsuario($idUsuarioMedico);
    if (!$idMedico) {
        $this->objMensaje->mensajeSistemaShow("Error al obtener el ID médico del usuario seleccionado.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 5. Validar enfermera (si se seleccionó)
    $idEnfermera = null;
    if (!empty($idUsuarioEnfermera)) {
        if (!$this->objAuxiliarDAO->validarUsuarioEsEnfermera($idUsuarioEnfermera)) {
            $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como enfermera no es una enfermera válida.", $rutaRetorno, 'systemOut', false);
            return;
        }
        $idEnfermera = $idUsuarioEnfermera;
    }

    // 6. Validar internado
    if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($idInternado)) {
        $this->objMensaje->mensajeSistemaShow("El ID de Internado no es válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 7. Actualizar seguimiento
    $resultado = $this->objSeguimientoDAO->editarSeguimiento(
        $idSeguimiento,
        $idInternado, 
        $idMedico,        // id_medico (de la tabla medicos)
        $idEnfermera,     // id_usuario (directo de la tabla usuarios)
        $evolucion, 
        $tratamiento
    );

    if ($resultado) {
        $this->objMensaje->mensajeSistemaShow('Evolución clínica actualizada correctamente.', '../indexEvolucionClinicaPacienteHospitalizado.php', 'success');
    } else {
        $this->objMensaje->mensajeSistemaShow('Error al actualizar la evolución.', $rutaRetorno, 'error');
    }
}
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\agregarEvolucionPacienteHospitalizado\indexaAgregarEvolucionPaciente.php
<?php

include_once('./formAgregarEvolucionPaciente.php'); 
$obj = new formAgregarEvolucionPaciente();
$obj->formAgregarEvolucionPacienteShow();
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\agregarEvolucionPacienteHospitalizado\formAgregarEvolucionPaciente.php
<?php

include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/InternadoSeguimientoDAO.php');



class formAgregarEvolucionPaciente extends pantalla
{
    public function formAgregarEvolucionPacienteShow()
    {
        $this->cabeceraShow('Registrar Evolución Clínica');

        $objAuxiliar = new EntidadAuxiliarDAO();
        $internados = $objAuxiliar->obtenerInternadosActivosConNombrePaciente();
        $medicos = $objAuxiliar->obtenerMedicosActivos();
        $enfermeros = $objAuxiliar->obtenerEnfermerosActivos();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-journal-plus me-2"></i>Nuevo Registro de Evolución</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarEvolucionPaciente.php" method="POST">
                
                <div class="mb-3">
                    <label for="idInternado" class="form-label">Paciente Hospitalizado:</label>
                    <select class="form-select" id="idInternado" name="idInternado" required>
                        <option value="">-- Seleccione un Paciente (Internado Activo) --</option>
                        <?php foreach ($internados as $internado) { ?>
                            <option value="<?php echo htmlspecialchars($internado['id_internado']); ?>">
                                <?php echo htmlspecialchars("ID: {$internado['id_internado']} - {$internado['nombre_completo']} - Ingreso: " . date('d/m/Y', strtotime($internado['fecha_ingreso']))); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idMedico" class="form-label">Médico Tratante:</label>
                        <select class="form-select" id="idMedico" name="idMedico" required>
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach ($medicos as $medico) { ?>
                                <option value="<?php echo htmlspecialchars($medico['id_usuario']); ?>">
                                    <?php echo htmlspecialchars("{$medico['nombre']} {$medico['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idEnfermera" class="form-label">Enfermera (Opcional):</label>
                        <select class="form-select" id="idEnfermera" name="idEnfermera">
                            <option value="">-- Seleccione una Enfermera --</option>
                            <?php foreach ($enfermeros as $enfermero) { ?>
                                <option value="<?php echo htmlspecialchars($enfermero['id_usuario']); ?>">
                                    <?php echo htmlspecialchars("{$enfermero['nombre']} {$enfermero['apellido_paterno']}"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evolucion" class="form-label">Evolución Clínica:</label>
                    <textarea class="form-control" id="evolucion" name="evolucion" rows="5" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tratamiento" class="form-label">Tratamiento/Indicaciones:</label>
                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="5"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Guardar Evolución</button>
                    <a href="../indexEvolucionClinicaPacienteHospitalizado.php" class="btn btn-secondary">Cancelar</a>
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
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\agregarEvolucionPacienteHospitalizado\getAgregarEvolucionPaciente.php
<?php

session_start();

include_once('./controlAgregarEvolucionPaciente.php');
$objControl = new controlAgregarEvolucionPaciente();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar datos
    $idInternado = isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null;
    $idMedico = isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null;
    // idEnfermera puede ser NULL si no se selecciona
    $idEnfermera = isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null;
    $evolucion = isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '';
    $tratamiento = isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '';

    // Llamar al controlador
    $objControl->registrarEvolucion($idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ./indexaAgregarEvolucionPaciente.php");
    exit();
}
?>
C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\enfermera\gestionEvolucionClinica\agregarEvolucionPacienteHospitalizado\controlAgregarEvolucionPaciente.php

<?php
include_once("../../../../modelo/InternadoSeguimientoDAO.php"); 
include_once('../../../../shared/mensajeSistema.php');

class controlAgregarEvolucionPaciente
{
    private $objSeguimientoDAO;
    private $objAuxiliarDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); // Asegurar que se instancia correctamente
        $this->objMensaje = new mensajeSistema();
    }

  public function registrarEvolucion($idInternado, $idUsuarioMedico, $idUsuarioEnfermera, $evolucion, $tratamiento)
{
    $rutaRetorno = './indexaAgregarEvolucionPaciente.php';
    $evolucion = trim($evolucion);
    $tratamiento = trim($tratamiento);
    
    // 1. Validaciones básicas
    if (empty($idInternado) || empty($idUsuarioMedico) || empty($evolucion)) {
        $this->objMensaje->mensajeSistemaShow("Los campos Paciente Hospitalizado, Médico Tratante y Evolución Clínica son obligatorios.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 2. Validar que el usuario médico existe y es médico
    if (!$this->objAuxiliarDAO->validarUsuarioEsMedico($idUsuarioMedico)) {
        $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como médico no es un médico válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 3. Convertir id_usuario_medico a id_medico
    $idMedico = $this->objAuxiliarDAO->obtenerIdMedicoPorIdUsuario($idUsuarioMedico);
    if (!$idMedico) {
        $this->objMensaje->mensajeSistemaShow("Error al obtener el ID médico del usuario seleccionado.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 4. Validar enfermera (si se seleccionó)
    $idEnfermera = null;
    if (!empty($idUsuarioEnfermera)) {
        if (!$this->objAuxiliarDAO->validarUsuarioEsEnfermera($idUsuarioEnfermera)) {
            $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como enfermera no es una enfermera válida.", $rutaRetorno, 'systemOut', false);
            return;
        }
        $idEnfermera = $idUsuarioEnfermera; // Se usa id_usuario directamente
    }

    // 5. Validar internado
    if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($idInternado)) {
        $this->objMensaje->mensajeSistemaShow("El ID de Internado seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 6. Registrar seguimiento
    $resultado = $this->objSeguimientoDAO->registrarSeguimiento(
        $idInternado, 
        $idMedico,        // id_medico (de la tabla medicos)
        $idEnfermera,     // id_usuario (directo de la tabla usuarios)
        $evolucion, 
        $tratamiento
    );

    if ($resultado) {
        $this->objMensaje->mensajeSistemaShow('Evolución clínica registrada correctamente.', '../indexEvolucionClinicaPacienteHospitalizado.php', 'success');
    } else {
        $this->objMensaje->mensajeSistemaShow('Error al registrar la evolución. Fallo en la inserción en la base de datos.', $rutaRetorno, 'error');
    }
}

}
?>
