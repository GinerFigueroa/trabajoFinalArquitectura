
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




<?php
    include_once('./formAutenticarUsuario.php');
    
    $obj = new formAutenticarUsuario();
    $obj -> formAutenticarUsuarioShow();
?>




<?php
/**
 * Patrón: MVC (Vista)
 * Patrón: Template Method (Uso del Template)
 * Responsabilidad: Generar el HTML del formulario de login.
 */
include_once __DIR__ . '/../shared/pantalla.php';

class formAutenticarUsuario extends pantalla
{
    /**
     * Patrón: Template Method (Paso de la Plantilla)
     * Implementa el contenido específico de la vista utilizando la estructura fija (cabecera/pie)
     * heredada de la clase 'pantalla'.
     */
    public function formAutenticarUsuarioShow()
    {
        // Paso 1 del Template Method: Muestra la cabecera
        $this->cabeceraShow("Autenticación de Usuario");
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Autenticación de Usuario
                    </h4>
                </div>
                <div class="card-body">
                    <form name='autenticarUsuario' method='POST' action='./getUsuario.php'>
                        <div class="text-center mb-4">
                            <img src="../img/login.png" alt="login" class="img-fluid" style="max-width: 100px;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="txtLogin" class="form-label">Login:</label>
                            <input name='txtLogin' id='txtLogin' type='text' class="form-control" 
                                   placeholder="Ingrese su usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="txtPassword" class="form-label">Password:</label>
                            <input name='txtPassword' id='txtPassword' type='password' 
                                   class="form-control" placeholder="Ingrese su contraseña" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type='submit' name='btnAceptar' class='btn btn-primary btn-lg'>
                                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="recuperar-contrasena.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>¿Olvidó su contraseña?
                            </a>
                        </div>
                         <div class="text-center mt-3">
                            <a href="./registrarUsuario/indexRegitroUsuario.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i> loguearse 
                            </a>
                        </div>
                    </form>
                    </div>
            </div>
        </div>
    </div>
</div>
<?php
        // Paso 2 del Template Method: Muestra el pie de página
        $this->pieShow();
    }
}
?>
<?php
/**
 * Patrón: Front Controller (Parcial)
 * Responsabilidad: Actuar como punto de entrada POST, realizar validación de entrada y delegar al Controlador.
 */
session_start();

/**
 * Patrón: Helper/Utilidad (Funciones de Validación)
 */
function validarBoton($boton)
{
    return isset($boton);    
}

function validarTexto($txtLogin, $txtPassword)
{
    $loginLength = strlen(trim($txtLogin));
    $passwordLength = strlen(trim($txtPassword));
    return ($loginLength > 3 && $passwordLength > 3);
}

// 1. Lógica del Front Controller
if (isset($_POST['btnAceptar']) && validarBoton($_POST['btnAceptar'])) {
    // 2. Validación de la Entrada (Delegación a funciones Helper)
    if (validarTexto($_POST['txtLogin'], $_POST['txtPassword'])) {
        $login = strtolower(trim(htmlspecialchars($_POST['txtLogin'])));
        $password = trim(htmlspecialchars($_POST['txtPassword']));

        // 3. Delegación al Controlador
        include_once('controlAutenticarUsuario.php');
        $obcontrol = new controlAutenticarUsuario();
        $obcontrol->verificarUsuario($login, $password); // Ejecución del Command
    } else {
        // Delegación a la utilidad de Mensajes
        include_once('../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow(
            "Los datos ingresados no son válidos<br>El login y password deben tener más de 3 caracteres",
            "../index.php",
            "error"
        ); 
    }
} else {
    // Delegación a la utilidad de Mensajes
    include_once('../shared/mensajeSistema.php'); 
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido<br>Se ha detectado un intento de ingreso no autorizado",
        "../index.php",
        "error"
    );
}
?>
<?php
/**
 * Patrón: MVC (Controlador) / Service Layer (Parcial)
 * Responsabilidad: Controlar el flujo de la autenticación.
 */
class controlAutenticarUsuario
{
    /**
     * Patrón: Delegación / Command (Verificar Autenticación)
     * Centraliza la secuencia de validación.
     */
    public function verificarUsuario($login, $password)
    {
        // Patrón DAO: Se instancia el DAO (Usuario) para acceder a los datos.
        include_once('../modelo/securitUsuario.php');
        
        $objUsuario = new UsuarioDAO(); 
        
        // Patrón Strategy (Implícito): Cada validación es una "estrategia" de comprobación.
        // Las llamadas a los métodos del DAO (validarLogin, validarPassword, validarEstado) 
        // son los pasos en la Estrategia de Autenticación.
        
        // 1. Validar login
        $respuesta = $objUsuario->validarLogin($login);
        if(!$respuesta) {
            $this->mostrarError("El login '$login' no está registrado en el sistema");
            return;
        }
        
        // 2. Validar password
        $respuesta = $objUsuario->validarPassword($login, $password);
        if(!$respuesta) {
            $this->mostrarError("El usuario '$login' tiene registrado un password diferente del ingresado");
            return;
        }
        
        // 3. Validar estado (Patrón State Simplificado: 1/0)
        $respuesta = $objUsuario->validarEstado($login);
        if(!$respuesta) {
            $this->mostrarError("El usuario '$login' no está habilitado en el sistema<br>Contacte con el administrador");
            return;
        }
        
        // 4. Autenticación exitosa
        $this->iniciarSesion($login);
    }
    
    /**
     * Patrón: Delegación (Manejo de Mensajes)
     */
    private function mostrarError($mensaje)
    {
        include_once('../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow($mensaje, "../index.php", "systemOut", false);
    }
    
    /**
     * Patrón: Delegación y Coordinación
     * **MODIFICADO:** Ahora almacena id_usuario y rol_id en la sesión.
     */
    private function iniciarSesion($login)
    {
        // DAO para privilegios (contiene obtenerInformacionCompletaUsuario)
        include_once('../modelo/usuarioPrivilegioDAO.php'); 
        // Vista de bienvenida
        include_once('screenBienvenida.php');
        
        $objUsuarioPrivilegio = new UsuarioPrivilegioDAO();
        $objBienvenida = new screenBienvenida();
        
        // 1. OBTENER INFORMACIÓN COMPLETA DEL USUARIO (id_usuario, id_rol, etc.)
        $usuarioInfo = $objUsuarioPrivilegio->obtenerInformacionCompletaUsuario($login);
        
        // 2. Obtiene solo la lista de privilegios/roles
        $listaPrivilegios = $objUsuarioPrivilegio->obtenerPrivilegiosUsuario($login);
        
        // 3. Establecer Variables de Sesión
        $_SESSION['login'] = $login;
        $_SESSION['privilegios'] = $listaPrivilegios;
        
        // 💥 Solución para el error de 'Acceso Denegado' 💥
        if ($usuarioInfo) {
            // Asumiendo que las claves en $usuarioInfo son 'id_usuario' y 'id_rol' (o 'id_rol' si así lo configuró el DAO)
            $_SESSION['id_usuario'] = $usuarioInfo['id_usuario'] ?? null; // Clave requerida en formConsultarCitas
            $_SESSION['rol_id'] = $usuarioInfo['id_rol'] ?? null;       // Clave requerida en formConsultarCitas (asumiendo 'id_rol' en BD)
        }
        
        // 4. Muestra la vista (Delegación a la Vista)
        $objBienvenida->screenBienvenidaShow($listaPrivilegios);
    }
}
?>
<?php
/**
 * Patrón: MVC (Vista)
 * Responsabilidad: Generar la interfaz de usuario posterior al login.
 */
include_once("../shared/pantalla.php");

include_once('../modelo/usuarioPrivilegioDAO.php'); 

class screenBienvenida extends pantalla
{
    /**
     * Patrón: Template Method (Uso del Template)
     * Utiliza los métodos base (cabeceraShow/pieShow) de la clase pantalla.
     */
    public function screenBienvenidaShow()
    {
        $login = $_SESSION['login'];

        // Patrón DAO: Acceso a datos para obtener el rol.
        $usuarioRol = new usuarioPrivilegioDAO();
        $roles = $usuarioRol->obtenerPrivilegiosUsuario($login);

        $rol = $roles[0]['rol'] ?? 'Desconocido';

        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Sistema de Gestión de Clínica</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css'>
            <link rel='stylesheet' type='text/css' href='../css/bienvenida.css'>
            <style>
                .card {
                    transition: transform 0.3s, box-shadow 0.3s;
                    height: 100%;
                    border-radius: 15px;
                    border: none;
                    overflow: hidden;
                }
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
                }
                .card-header-custom {
                    padding: 1rem;
                    font-weight: bold;
                    color: white;
                }
                .role-icon {
                    font-size: 3rem;
                    margin-bottom: 15px;
                }
                .welcome-header {
                    background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                    color: white;
                    padding: 2rem 0;
                    margin-bottom: 2rem;
                    border-radius: 0 0 1rem 1rem;
                }
                .section-title {
                    margin-top: 2rem;
                    margin-bottom: 1rem;
                    padding: 0.5rem 1rem;
                    border-left: 4px solid #0d6efd;
                    background: #f8f9fa;
                }
                
                /* Colores para diferentes secciones */
                .card-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
                .card-planificacion { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
                .card-registro { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
                .card-unico { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
                .card-hospitalizacion { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
                .card-recepcion { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
                .card-paciente { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
                .card-cajero { background: linear-gradient(135deg, #ff9a56 0%, #feca57 100%); }
                .card-enfermera { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
                
                .card-body-white {
                    background: white;
                    color: #333;
                }
                .icon-container {
                    color: white;
                    padding: 1.5rem;
                }
            </style>
        </head>
        <body>";

        $this->cabeceraShow("Sistema de Gestión de Clínica");

        echo "<div class='welcome-header text-center'>
                <div class='container'>
                    <h1 class='display-4'><i class='bi bi-heart-pulse-fill'></i> Bienvenido, $login</h1>
                    <p class='lead'>Rol: $rol</p>
                    <p>Seleccione una opción del menú para comenzar</p>
                </div>
              </div>";

        echo "<div class='container my-5'>";

        // Según el rol, mostrar diferentes opciones
        if ($rol == 'Administrador') {
            echo "<h3 class='section-title'><i class='bi bi-gear-fill'></i> Panel de Administración</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-admin icon-container text-center'>
                            <i class='bi bi-people-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión de Usuarios</h5>
                            <p class='card-text'>Administre usuarios y roles del sistema</p>
                            <a href='../modeloRol/administrador/gestionUsuario/indexGestionUsuario.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-admin icon-container text-center'>
                            <i class='bi bi-file-earmark-bar-graph role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Informes y Estadísticas</h5>
                            <p class='card-text'>Genere reportes del sistema</p>
                            <a href='../moduloReportes/indexReportes.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-admin icon-container text-center'>
                            <i class='bi bi-sliders role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Configuración del Sistema</h5>
                            <p class='card-text'>Ajustar parámetros del sistema</p>
                            <a href='../moduloConfiguracion/indexConfiguracion.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
        
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-admin icon-container text-center'>
                            <i class='bi bi-clock-history role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Registros del Sistema</h5>
                            <p class='card-text'>Consultar logs de actividad</p>
                            <a href='../moduloLogs/indexLogs.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

        } elseif ($rol == 'Médico') {
            // SECCIÓN: PLANIFICACIÓN DE TRATAMIENTOS
            echo "<h3 class='section-title'><i class='bi bi-clipboard2-pulse-fill'></i> Planificación y Seguimiento de Tratamientos</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-planificacion icon-container text-center'>
                            <i class='bi bi-clipboard-check-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión de Seguimiento Tratamiento</h5>
                            <p class='card-text'>Listar, editar y agregar seguimientos</p>
                            <a href='../modeloRol/medico/planificacionSeguimientoDeTratamientos/gestionSeguimientoTratamientoVer/indexSeguimientoTratamiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
 
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-planificacion icon-container text-center'>
                            <i class='bi bi-journal-medical role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión de Plan de Tratamiento</h5>
                            <p class='card-text'>Plan de tratamientos: listar, editar, registrar</p>
                            <a href='../modeloRol/medico/planificacionSeguimientoDeTratamientos/gestionPlanTratamiento/indexPlanTratamiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-planificacion icon-container text-center'>
                            <i class='bi bi-cash-coin role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión de Presupuesto</h5>
                            <p class='card-text'>Informar costo de tratamiento médico</p>
                            <a href='../modeloRol/medico/planificacionSeguimientoDeTratamientos/gestionPresupuesto/indexPresupuesto.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-planificacion icon-container text-center'>
                            <i class='bi bi-clipboard2-pulse role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Seguimiento a Pacientes</h5>
                            <p class='card-text'>Crear y gestionar seguimientos</p>
                            <a href='../modeloRol/medico/planificacionSeguimientoDeTratamientos/gestionSeguimientoTratamiento/indexSeguimientoTratamiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-planificacion icon-container text-center'>
                            <i class='bi bi-clipboard-data role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Plan de Tratamiento</h5>
                            <p class='card-text'>Administración de planes</p>
                            <a href='../modeloRol/medico/planificacionSeguimientoDeTratamientos/gestionPlanTratamiento/indexPlanTratamiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";
            
            // SECCIÓN: REGISTROS POR CADA VISITA
            echo "<h3 class='section-title'><i class='bi bi-clipboard2-check-fill'></i> Registros por Cada Visita</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-prescription2 role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Recetas Medicas</h5>
                            <p class='card-text'>Dar recetas, editar, eliminar, listar pacientes</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/darRecetaMedicaAlpaciente/gestionOrdenRecetaMedica/indexRecetaMedica.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

                      echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-capsule role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Detalle Receta Medico</h5>
                            <p class='card-text'>Crear detalle medico, editar , registrar</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/darRecetaMedicaAlpaciente/gestionDetalleCitaPaciente/indexDetalleCita.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-file-medical-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Orden para Exámenes</h5>
                            <p class='card-text'>Ordenar exámenes médicos post-diagnóstico</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/gestionDiagnosticoPaciente/indexPacienteDianostico.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-thermometer-half role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Orden Examen Clínico</h5>
                            <p class='card-text'>Generar orden para examen médico</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/gestionOrdenExamenClinicoPaciente/indexOrdenExamenClinico.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

        
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-file-earmark-text-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Historia Clínica</h5>
                            <p class='card-text'>Ver, registrar y editar historias</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/gestionHistoriaClinica/indexHistoriaClinica.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-calendar3 role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mis Citas Programadas</h5>
                            <p class='card-text'>Ver citas agendadas para atender</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/gestionHistoriaClinica/indexHistoriaClinica.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";
            
        
            
            // SECCIÓN: SEGUIMIENTO HOSPITALIZACIÓN
            echo "<h3 class='section-title'><i class='bi bi-hospital'></i> Seguimiento de Pacientes Hospitalizados</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-hospitalizacion icon-container text-center'>
                            <i class='bi bi-activity role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Pacientes Internados</h5>
                            <p class='card-text'>Editar evolución de hospitalizados</p>
                            <a href='../modeloRol/medico/seguimientosDepacientesHospitalizados/gestionInternadosSeguimiento/indexInternadoSeguimiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-hospitalizacion icon-container text-center'>
                            <i class='bi bi-person-badge role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mis Pacientes Internados</h5>
                            <p class='card-text'>Ver, editar, agregar y dar alta</p>
                            <a href='../modeloRol/medico/seguimientosDepacientesHospitalizados/pacientesQueSeinternan/indexPacienteAinternar.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

             // SECCIÓN: REGISTRO ÚNICO
            echo "<h3 class='section-title'><i class='bi bi-clipboard-plus'></i> Registro Único del Paciente</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-unico icon-container text-center'>
                            <i class='bi bi-droplet-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión Historial Anemia</h5>
                            <p class='card-text'>Listar, registrar y editar anemias</p>
                            <a href='../modeloRol/medico/registroUnicoLLegaPaciente/gestionHistorialAnemia/indexHistorialAnemia.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

        } elseif ($rol == 'Recepcionista') {
            echo "<h3 class='section-title'><i class='bi bi-person-workspace'></i> Gestión de Recepción</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-recepcion icon-container text-center'>
                            <i class='bi bi-person-plus-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Gestión de Pacientes</h5>
                            <p class='card-text'>Registrar y actualizar datos</p>
                            <a href='../modeloRol/recepcion/gestionTotalPacientes/indexTotalPaciente.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-recepcion icon-container text-center'>
                            <i class='bi bi-calendar-week-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Programación de Citas</h5>
                            <p class='card-text'>Agendar y modificar citas</p>
                            <a href='../modeloRol/recepcion/gestionProgramarCitas/indexCita.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
       
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-recepcion icon-container text-center'>
                            <i class='bi bi-telephone-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Confirmar Citas</h5>
                            <p class='card-text'>Confirmación telefónica</p>
                            <a href='../moduloConfirmacion/indexConfirmacion.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-recepcion icon-container text-center'>
                            <i class='bi bi-receipt-cutoff role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Orden Pre-factura</h5>
                            <p class='card-text'>Generar pre-factura</p>
                            <a href='../modeloRol/recepcion/generarOrdenPrefactura/indexOdenPrefactura.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-recepcion icon-container text-center'>
                            <i class='bi bi-card-checklist role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Tipo de Tratamiento</h5>
                            <p class='card-text'>Seleccionar tratamiento y costo</p>
                            <a href='../modeloRol/recepcion/gestionTipoDeTratamientoCosto/indexTipoTratamiento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

        } elseif ($rol == 'Paciente') {
            echo "<h3 class='section-title'><i class='bi bi-person-heart'></i> Portal del Paciente</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-calendar-plus-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Programar Citas ------xxxx</h5>
                            <p class='card-text'>Solicitar citas en línea</p>
                            <a href='../modeloRol/paciente/citas/indexCitas.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-file-medical-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mi Historial Médico</h5>
                            <p class='card-text'>Consultar historial y diagnósticos</p>
                            <a href='../modeloRol/paciente/historial/indexHistorial.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-chat-left-text-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Comunicación-----xxxx</h5>
                            <p class='card-text'>Enviar mensajes a la clínica</p>
                            <a href='../moduloComunicacion/indexComunicacion.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-wallet2 role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mis Pagos</h5>
                            <p class='card-text'>Consultar pagos y presupuestos</p>
                            <a href='../modeloRol/paciente/facturacion/indexPagos.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-file-earmark-text-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mis Documentos.---xxxx</h5>
                            <p class='card-text'>Acceder a documentos médicos</p>
                            <a href='../moduloMisDocumentos/indexMisDocumentos.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-receipt role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Facturación de Pagos----xxx</h5>
                            <p class='card-text'>Consulta de facturaciones</p>
                            <a href='../modeloRol/paciente/facturacion/indexPagos.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-paciente icon-container text-center'>
                            <i class='bi bi-person-circle role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mi Perfil</h5>
                            <p class='card-text'>Actualizar información personal</p>
                            <a href='../modeloRol/paciente/perfil/indexPerfil.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

        } elseif ($rol == 'Cajero') {
            echo "<h3 class='section-title'><i class='bi bi-cash-stack'></i> Gestión de Caja</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";
            
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-cajero icon-container text-center'>
                            <i class='bi bi-printer-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Emitir Boletas</h5>
                            <p class='card-text'>Emitir ticket de boleta de pago</p>
                            <a href='../modeloRol/cajero/gestionEmisionBoletaFinal/indexEmisionBoletaFinal.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

            

            

         
            
            echo "</div>";
             echo "<div class='col'>
                    <div class='card'>
                        <div class='card-cajero icon-container text-center'>
                            <i class='bi bi-printer-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Emitir Boletas Internados</h5>
                            <p class='card-text'>Emitir ticket de boleta Internado de pago</p>
                            <a href='../modeloRol/cajero/gestionDeBoletaInternado/indexFacturacionInternadoPDF.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";

                    


        } elseif ($rol == 'Enfermera') {
          echo "<h3 class='section-title'><i class='bi bi-heart-pulse'></i> Gestión de Enfermería</h3>";
echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

// Consentimiento Informado
echo "<div class='col'>
        <div class='card'>
            <div class='card-enfermera icon-container text-center'>
                <i class='bi bi-file-earmark-text-fill role-icon'></i>
            </div>
            <div class='card-body card-body-white text-center'>
                <h5 class='card-title'>Consentimiento Informado</h5>
                <p class='card-text'>Consentimiento Informado del Paciente</p>
                <a href='../modeloRol/enfermera/gernerarHojaDeConsentimientoInformado/indexConsentimientoInformado.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
            </div>
        </div>
      </div>";

// Evolución Clínica
echo "<div class='col'>
        <div class='card'>
            <div class='card-enfermera icon-container text-center'>
                <i class='bi bi-journal-medical role-icon'></i>
            </div>
            <div class='card-body card-body-white text-center'>
                <h5 class='card-title'>Gestionar Evolución Clínica</h5>
                <p class='card-text'>Editar, registrar evolución clínica</p>
                <a href='../modeloRol/enfermera/gestionEvolucionClinica/indexEvolucionClinicaPacienteHospitalizado.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
            </div>
        </div>
      </div>";

// Examen de Entrada
echo "<div class='col'>
        <div class='card'>
            <div class='card-enfermera icon-container text-center'>
                <i class='bi bi-clipboard2-pulse-fill role-icon'></i>
            </div>
            <div class='card-body card-body-white text-center'>
                <h5 class='card-title'>Examen de Entrada para ser atendido por Médico</h5>
                <p class='card-text'>Editar, registrar el examen de entrada</p>
                <a href='../modeloRol/enfermera/gestionExamenDeEntrada/indexExamenEntrada.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
            </div>
        </div>
      </div>";

            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-enfermera icon-container text-center'>
                            <i class='bi bi-hospital role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Paciente Hospitalizado</h5>
                            <p class='card-text'>Registrar paciente a hospitalizar</p>
                            <a href='../modeloRol/enfermera/gestionPacientesQueSeinternan/indexGestionInternados.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-enfermera icon-container text-center'>
                            <i class='bi bi-folder-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Manejo de Documentos--vvv</h5>
                            <p class='card-text'>Gestionar documentación de pacientes</p>
                            <a href='../modeloRol/enfermera/gestionManejoDocumento/indexDumento.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                    echo "<div class='col'>
                    <div class='card'>
                        <div class='card-enfermera icon-container text-center'>
                            <i class='bi bi-folder-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Registro Historial Paciente</h5>
                            <p class='card-text'>registro Historial, editar, registrar</p>
                            <a href='../modeloRol/enfermera/gestionHistoriaClinica/indexHistoriaClinica.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
            
            echo "</div>";

        } else {
            echo "<div class='row'>";
            echo "<div class='col-12 text-center'>
                    <div class='alert alert-warning' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill'></i> Rol desconocido. Por favor, contacte al administrador del sistema.
                    </div>
                  </div>";
            echo "</div>";
        }

        echo "</div>";  // Cierre del container

      
        $this->pieShow();
        echo "</body></html>";
    }
}
