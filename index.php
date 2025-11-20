<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica González - 90 años cuidando tu salud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .contact-btn {
            font-size: 1.1rem;
            padding: 12px 30px;
            margin: 10px;
            border-radius: 50px;
        }
        .specialty-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .specialty-card:hover {
            transform: translateY(-5px);
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-hospital-alt"></i> Clínica González
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#especialidades">Especialidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="./securityModule/indexLoginSegurity.php">
                            <i class="fas fa-sign-in-alt"></i> Loguear
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Clínica González</h1>
            <p class="lead mb-4">90 años cuidando tu salud y la de los tuyos</p>
            <p class="fs-5 mb-4">Más de 40 especialidades | Calidad y precio justo</p>
            <a href="#contacto" class="btn btn-light btn-lg contact-btn">
                <i class="fas fa-calendar-check"></i> Solicita tu Cita
            </a>
        </div>
    </section>

    <!-- Información -->
    <section class="info-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h5>Horario de Atención</h5>
                        <p class="mb-0">Abierto hasta las 8:00 PM</p>
                        <p class="text-muted">Reservas: 7am - 4pm</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                        <h5>Dirección</h5>
                        <p class="mb-0">Av. Ignacio Merino 1884</p>
                        <p class="text-muted">Lince 15046, Lima</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                        <h5>Teléfono</h5>
                        <p class="mb-0">(01) 471-1579</p>
                        <p class="text-muted">Llámanos ahora</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

                    <!-- Especialidades -->
                    <section id="especialidades" class="py-5">
                        <div class="container">
                            <h2 class="text-center mb-5 fw-bold">Nuestras Especialidades</h2>
                            <div class="row g-4">
                                <?php
                                $especialidades = [
                                    ['nombre' => 'Medicina General', 'icono' => 'fa-user-doctor'],
                                    ['nombre' => 'Pediatría', 'icono' => 'fa-baby'],
                                    ['nombre' => 'Neurología', 'icono' => 'fa-brain'],
                                    ['nombre' => 'Ginecología', 'icono' => 'fa-venus'],
                                    ['nombre' => 'Cardiología', 'icono' => 'fa-heart-pulse'],
                                    ['nombre' => 'Gastroenterología', 'icono' => 'fa-stomach'],
                                    ['nombre' => 'Oftalmología', 'icono' => 'fa-eye'],
                                    ['nombre' => 'Odontología', 'icono' => 'fa-tooth']
                                ];

                                foreach ($especialidades as $esp) {
                                    echo '
                                    <div class="col-md-3 col-sm-6">
                                        <div class="card specialty-card border-0 shadow-sm text-center p-4">
                                            <i class="fas ' . $esp['icono'] . ' fa-3x text-primary mb-3"></i>
                                            <h6 class="fw-bold">' . $esp['nombre'] . '</h6>
                                        </div>
                                    </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </section>

                    <!-- Contacto -->
                    <section id="contacto" class="bg-light py-5">
                        <div class="container">
                            <h2 class="text-center mb-5 fw-bold">Contáctanos</h2>
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card border-0 shadow-sm p-4">
                                        <div class="row text-center">
                                        <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <a href="tel:014711579" class="btn btn-outline-primary w-100 contact-btn">
                    <i class="fas fa-phone fa-lg mb-1 d-block"></i>
                    <span class="small">Teléfono</span>
                    </a>
                </div>

                <div class="col-md-3 col-6 mb-3">
                    <a href="https://wa.me/51997584512" target="_blank" class="btn btn-outline-success w-100 contact-btn">
                    <i class="fab fa-whatsapp fa-lg mb-1 d-block"></i>
                    <span class="small">WhatsApp</span>
                    </a>
                </div>

                <div class="col-md-3 col-6 mb-3">
                    <a href="https://mail.google.com/mail/?view=cm&to=sgerencia@clinicagonzalez.com" target="_blank" class="btn btn-outline-danger w-100 contact-btn">
                    <i class="fas fa-envelope fa-lg mb-1 d-block"></i>
                    <span class="small">Gmail</span>
                    </a>
                </div>

                <div class="col-md-3 col-6 mb-3">
                    <a href="https://www.instagram.com/clinicagonzalez" target="_blank" class="btn btn-outline-info w-100 contact-btn">
                    <i class="fab fa-instagram fa-lg mb-1 d-block"></i>
                    <span class="small">Instagram</span>
                    </a>
                </div>
                </div>

                <hr class="my-4">

                <div class="text-center">
                <h5 class="mb-3">Ubicación</h5>
                <a href="https://maps.app.goo.gl/mPeTLdgE5zjMg9Xa8" target="_blank" class="btn btn-primary">
                    <i class="fas fa-map-marked-alt"></i> Ver en Google Maps
                </a>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Clínica González - 90 años cuidando tu salud</p>
            <p class="mb-0"><small>Av. Ignacio Merino 1884, Lince | Tel: (01) 471-1579 | WSP: 997-584-512</small></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>