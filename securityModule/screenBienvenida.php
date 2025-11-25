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
                  
                    
          

                  
            
            echo "</div>";

        } elseif ($rol == 'Médico') {
            
            
            // SECCIÓN: REGISTROS POR CADA VISITA
            echo "<h3 class='section-title'><i class='bi bi-clipboard2-check-fill'></i> Registros por Cada Visita</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

             echo "<div class='col'>
                    <div class='card'>
                        <div class='card-registro icon-container text-center'>
                            <i class='bi bi-file-earmark-text-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Historia Clínica,Orden Examen Medico, Registrar Evolución</h5>
                            <p class='card-text'>Ver, registrar y editar historias, generar orden Medico</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/gestionHistorialMedico/indexHistorialMedico.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            
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
                            <i class='bi bi-calendar3 role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Mis Citas Programadas</h5>
                            <p class='card-text'>Ver citas agendadas para atender</p>
                            <a href='../modeloRol/medico/registroRepetidosPorCadaVisista/verMisCitasProgramadas/indexCita.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
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
            
        
            
            // SECCIÓN: SEGUIMIENTO HOSPITALIZACIÓN
            echo "<h3 class='section-title'><i class='bi bi-hospital'></i> Seguimiento de Pacientes Hospitalizados</h3>";
            echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

         
                  
            echo "<div class='col'>
                    <div class='card'>
                        <div class='card-hospitalizacion icon-container text-center'>
                            <i class='bi bi-person-badge role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Paciente a internar y Dar Alta</h5>
                            <p class='card-text'>Ver, editar, agregar , dar alta y gestion de Evolucion</p>
                            <a href='../modeloRol/medico/seguimientosDepacientesHospitalizados/misPacientesInternados/indexGestionInternados.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
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
                            <h5 class='card-title'>Gestión de Pacientes, gestion de citas, generar Pre Factura</h5>
                            <p class='card-text'>Registrar y actualizar datos</p>
                            <a href='../modeloRol/recepcion/gestionTotalPacientes/indexTotalPaciente.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                   echo "<div class='col'>
                    <div class='card'>
                        <div class='card-admin icon-container text-center'>
                            <i class='bi bi-people-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'> Gestion bot telegran</h5>
                            <p class='card-text'>Gestion Telegran, bot registra , editar</p>
                            <a href='../modeloRol/recepcion/gestionRecordatorioPaciente/indexRecordatorioPaciente.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
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
                            <i class='bi bi-file-medical-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Ver Mis Citas Medicas</h5>
                            <p class='card-text'>Consultar historial y diagnósticos</p>
                            <a href='../modeloRol/paciente/citas/indexCitas.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
                        </div>
                    </div>
                  </div>";
                  
            
               

          
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




// Examen de Entrada

            
                     echo "<div class='col'>
                    <div class='card'>
                        <div class='card-enfermera icon-container text-center'>
                            <i class='bi bi-folder-fill role-icon'></i>
                        </div>
                        <div class='card-body card-body-white text-center'>
                            <h5 class='card-title'>Registrar examen entrada</h5>
                            <p class='card-text'>registrar examen de entrada al pacinete</p>
                            <a href='../modeloRol/enfermera/gestionHistoriaClinica/indexHistoriaClinica.php' class='btn btn-primary'><i class='bi bi-arrow-right-circle'></i> Acceder</a>
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
