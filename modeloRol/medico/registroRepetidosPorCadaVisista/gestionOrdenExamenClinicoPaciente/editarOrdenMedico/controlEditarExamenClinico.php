<?php

include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// =====================================================================
// PATRÓN COMMAND: Interfaz y Clases Concretas
// =====================================================================

interface ComandoEdicion
{
    // Método Abstracto: ejecutar
    public function ejecutar(); 
}

class ActualizarOrdenCommand implements ComandoEdicion
{
    // Atributos: $receptor, $datos
    private controlEditarExamenClinico $receptor;
    private array $datos;

    // Método: Constructor
    public function __construct(controlEditarExamenClinico $receptor, array $datos)
    {
        $this->receptor = $receptor;
        $this->datos = $datos;
    }

    // Metodo: ejecutar
    public function ejecutar()
    {
        // El comando llama al método del Receptor
        $this->receptor->ejecutarActualizacion($this->datos);
    }
}

// =====================================================================
// PATRÓN CHAIN OF RESPONSIBILITY: Interfaz y Handlers Concretos
// =====================================================================

abstract class ValidationHandler 
{
    // Atributo Abstracto: $nextHandler
    protected $nextHandler = null; 

    // Método Abstracto: handle
    abstract public function handle(array $data): bool;

    // Metodo: setNext
    public function setNext(ValidationHandler $handler): ValidationHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }
}

class CamposRequeridosHandler extends ValidationHandler
{
    // Metodo: handle
    public function handle(array $data): bool 
    {
        if (empty($data['historiaClinicaId']) || empty($data['fecha']) || empty($data['tipoExamen']) || empty($data['estado'])) {
            throw new Exception("Error de validación: Todos los campos obligatorios deben ser completados.");
        }
        return $this->nextHandler ? $this->nextHandler->handle($data) : true;
    }
}

class PermisoEdicionHandler extends ValidationHandler
{
    // Atributo: $objDAO
    private $objDAO;

    // Metodo: Constructor
    public function __construct(OrdenExamenDAO $objDAO) {
        $this->objDAO = $objDAO;
    }

    // Metodo: handle
    public function handle(array $data): bool 
    {
        // Verificar que el médico de sesión es el dueño de la orden (o tiene permisos)
        $idMedicoOrden = $this->objDAO->obtenerIdMedicoPorOrden($data['idOrden']);
        $idMedicoSesion = $this->objDAO->obtenerIdMedicoPorUsuario($data['idUsuarioMedico']);
        
        if ($idMedicoOrden != $idMedicoSesion) {
            throw new Exception("Error de permisos: No tiene permisos para editar esta orden.");
        }
        return $this->nextHandler ? $this->nextHandler->handle($data) : true;
    }
}

// =====================================================================
// PATRÓN FACTORY METHOD: Creación de comandos
// =====================================================================

class ComandoEdicionFactory
{
    // Metodo: crearComando
    public static function crearComando(string $action, controlEditarExamenClinico $receptor, array $datos): ComandoEdicion
    {
        if ($action === 'actualizar') {
            return new ActualizarOrdenCommand($receptor, $datos);
        }
        throw new Exception("Acción de comando no válida: {$action}");
    }
}

class controlEditarExamenClinico // (Controlador y Receptor Command)
{
    // Atributo: $objDAO
    private $objDAO;
    // Atributo: $objMensaje
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objDAO = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
    }

    // Método Principal: El Invoker llama a este método para iniciar la secuencia
    // Metodo: editarOrdenExamen
    public function editarOrdenExamen(array $datos)
    {
        $rutaRetorno = "./indexEditarExamenClinico.php?id_orden=" . $datos['idOrden'];

        try {
            // PATRÓN CHAIN OF RESPONSIBILITY: Configuración y ejecución
            $validadorCampos = new CamposRequeridosHandler();
            $validadorPermiso = new PermisoEdicionHandler($this->objDAO);
            
            // Atributo: $chain (Configuración de la cadena)
            $validadorCampos->setNext($validadorPermiso); 
            $validadorCampos->handle($datos); // Ejecutar la cadena

            // PATRÓN FACTORY METHOD: Creación del Comando (se puede crear aquí o en el Invoker)
            // Lo moveremos al Invoker (`getEditarExamenClinico.php`) para seguir el flujo Command estándar.

            // El Receptor se llama a sí mismo para ejecutar la acción de negocio
            $this->ejecutarActualizacion($datos);

        } catch (Exception $e) {
            // Captura errores de la cadena (validación/permisos) o de la DAO
            $this->objMensaje->mensajeSistemaShow($e->getMessage(), $rutaRetorno, 'error');
        }
    }

    // RECEPTOR COMMAND: Lógica de negocio real de la actualización
    // Metodo: ejecutarActualizacion
    public function ejecutarActualizacion(array $datos)
    {
        $rutaRetornoExito = "../indexOrdenExamenClinico.php";
        $rutaRetornoFallo = "./indexEditarExamenClinico.php?id_orden=" . $datos['idOrden'];

        // 1. Obtener el ID del Médico (se validó en la cadena que tiene permisos)
        $idMedico = $this->objDAO->obtenerIdMedicoPorUsuario($datos['idUsuarioMedico']);

        // 2. Actualizar la orden en la base de datos
        $resultado = $this->objDAO->actualizarOrden(
            $datos['idOrden'],
            $datos['historiaClinicaId'],
            $idMedico, // Mantener el mismo médico
            $datos['fecha'],
            $datos['tipoExamen'],
            $datos['indicaciones'],
            $datos['estado'],
            $datos['resultados']
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Orden de examen actualizada correctamente.", $rutaRetornoExito, 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al actualizar la orden de examen. Por favor, intente nuevamente.", $rutaRetornoFallo, 'error');
        }
    }
}
?>