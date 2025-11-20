<?php
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/UsuarioDAO.php');

class controlGestionUsuario
{
    private $objUsuarioDAO;
    private $objMensaje;
    private $estadoActual = 'Inicial';

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    private function setEstado($nuevoEstado) {
        $this->estadoActual = $nuevoEstado;
    }
    
    /**
     * COMMAND: Eliminar usuario
     */
    public function eliminarUsuario($idUsuario)
    {
        $this->setEstado('ValidandoID');
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de usuario no válido.", "./indexGestionUsuario.php", "error");
            return;
        }

        $this->setEstado('VerificandoRelaciones');
        $resultado = $this->objUsuarioDAO->eliminarUsuarioSiEsPosible($idUsuario);
        
        if ($resultado['success']) {
            $this->setEstado('Exito');
            $this->objMensaje->mensajeSistemaShow($resultado['message'], "./indexGestionUsuario.php", "success");
        } else {
            $this->setEstado('OfreciendoAlternativa');
            $mensaje = $resultado['message'] . ". ¿Desea desactivarlo en su lugar?";
            echo "<script>
                if (confirm('" . $mensaje . "')) {
                    window.location.href = './getGestionUsuario.php?action=desactivar&id=" . $idUsuario . "';
                } else {
                    window.location.href = './indexGestionUsuario.php';
                }
            </script>";
        }
    }

    /**
     * COMMAND: Desactivar usuario
     */
    public function desactivarUsuario($idUsuario)
    {
        $this->setEstado('ValidandoID');
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de usuario no válido.", "./indexGestionUsuario.php", "error");
            return;
        }

        $this->setEstado('EjecutandoDesactivacion');
        $resultado = $this->objUsuarioDAO->desactivarUsuario($idUsuario);
        
        if ($resultado) {
            $this->setEstado('Exito');
            $this->objMensaje->mensajeSistemaShow("Usuario desactivado correctamente.", "./indexGestionUsuario.php", "success");
        } else {
            $this->setEstado('Fallo');
            $this->objMensaje->mensajeSistemaShow("Error al desactivar el usuario.", "./indexGestionUsuario.php", "error");
        }
    }

    /**
     * COMMAND: Reactivar usuario
     */
    public function reactivarUsuario($idUsuario)
    {
        $this->setEstado('ValidandoID');
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de usuario no válido.", "./indexGestionUsuario.php", "error");
            return;
        }

        $this->setEstado('EjecutandoReactivacion');
        $resultado = $this->objUsuarioDAO->reactivarUsuario($idUsuario);
        
        if ($resultado) {
            $this->setEstado('Exito');
            $this->objMensaje->mensajeSistemaShow("Usuario reactivado correctamente.", "./indexGestionUsuario.php", "success");
        } else {
            $this->setEstado('Fallo');
            $this->objMensaje->mensajeSistemaShow("Error al reactivar el usuario.", "./indexGestionUsuario.php", "error");
        }
    }
}