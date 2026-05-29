<?php

// 1. Validar e iniciar la sesión del administrador
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de Acceso Estricto: Si no es administrador, redirigir
if (!isset($_SESSION['id_admin'])) {
    if (isset($_SESSION['id_usuario'])) {
        header("Location: ../cliente.php");
    } else {
        header("Location: ../login.php");
    }
    exit();
}

// Cargar clase y configuración
require_once '../api/db.php';
require_once '../api/config.php';

$mensaje = '';
$id_admin = $_SESSION['id_admin'];
$nombre_admin = $_SESSION['nombre_admin'];

// 1. LÓGICA PARA GENERAR HORAS (De Día X a Día Y, cada 30 min, 9 AM a 5 PM)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_horas'])) {
    $id_profesional = (int) $_POST['id_profesional'];
    $fecha_inicio = new DateTime($_POST['fecha_inicio']);
    $fecha_fin = new DateTime($_POST['fecha_fin']);

    if ($fecha_inicio <= $fecha_fin) {
        $intervalo_dias = new DateInterval('P1D');
        $periodo = new DatePeriod($fecha_inicio, $intervalo_dias, $fecha_fin->modify('+1 day'));

        $horas_creadas = 0;
        foreach ($periodo as $dia) {
            $hora_actual = strtotime('09:00');
            $hora_fin    = strtotime('17:00');

            while ($hora_actual < $hora_fin) {
                $hora_str  = date('H:i:s', $hora_actual);
                $fecha_str = $dia->format('Y-m-d');

                $db->query(
                    "INSERT IGNORE INTO horas_disponibles (fecha, hora, id_profesional, estado) VALUES (?, ?, ?, 'disponible')",
                    $fecha_str, $hora_str, $id_profesional
                );

                if ($db->affectedRows() > 0) $horas_creadas++;

                $hora_actual = strtotime('+30 minutes', $hora_actual);
            }
        }
        $mensaje = "<div class='alert alert-success'>Se generaron $horas_creadas bloques de atención exitosamente.</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>La fecha de inicio debe ser menor o igual a la fecha de fin.</div>";
    }
}

// 2. LÓGICA PARA ACTUALIZAR ESTADO DE UNA HORA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $id_hora     = (int) $_POST['id_hora'];
    $nuevo_estado = $_POST['estado'];

    $db->query("UPDATE horas_disponibles SET estado = ? WHERE id = ?", $nuevo_estado, $id_hora);
    $mensaje = "<div class='alert alert-info'>Estado de la hora actualizado correctamente.</div>";
}

// Obtener lista de profesionales activos
$profesionales = $db->query("SELECT id, nombre FROM profesionales WHERE activo = 1")->fetchAll();

// Obtener las próximas horas generadas
$horas = $db->query("
    SELECT h.id, h.fecha, h.hora, h.estado, p.nombre AS profesional
    FROM horas_disponibles h
    JOIN profesionales p ON h.id_profesional = p.id
    WHERE h.fecha >= CURDATE()
    ORDER BY h.fecha ASC, h.hora ASC
    LIMIT 50
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Descubre nuestra exclusiva colección de lentes con los mejores diseños, tecnología de vanguardia y atención personalizada.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- titulo pagina -->
    <title>Opticas graham</title>
    <style>
    :root {
        --primary-color: #2e838b;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8f9fa;
    }

    .sidebar .nav-link:hover {
        background-color: #f1f3f5;
        border-radius: 8px;
    }

    /* Ajustes específicos para el panel de administración */
    body {
        /* padding-top: 100px; */
    }

    .admin-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(46, 131, 139, 0.12);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-disponible {
        background: rgba(46, 131, 139, 0.1);
        color: var(--primary-color);
    }

    .status-no_disponible {
        background: rgba(224, 93, 0, 0.1);
        color: var(--accent-color);
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="../administrador.php">OptiVision <span
                    class="badge bg-secondary fs-xs ms-1">Panel
                    Interno</span></a>
            <div class="d-flex align-items-center text-white gap-3">
                <span class="small d-none d-sm-inline"><i class="bi bi-person-circle me-1 text-info"></i> Hola,
                    <?php echo htmlspecialchars($nombre_admin); ?></span>
                <a href="../logout.php" class="btn btn-sm btn-outline-danger border-0"><i
                        class="bi bi-box-arrow-left"></i>
                    Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar p-3 shadow-sm min-vh-100">
                <div class="position-sticky pt-2">
                    <span class="small d-none d-sm-inline text-center "><i
                            class="bi bi-person-circle me-1 text-info"></i>
                        Hola,
                        <?php echo htmlspecialchars($nombre_admin); ?></span>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link p-3 rounded fw-bold text-secondary mb-1" href="../administrador.php">
                                <i class="bi bi-speedometer2 me-2"></i> Resumen General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white p-3 rounded" href="#"
                                style="background-color: var(--primary-color);">
                                <!-- <a class="nav-link text-secondary p-3" href="admin/control_citas.php"> -->
                                <i class="bi bi-calendar-event me-2"></i> Control de Citas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary p-3" href="inventario.php">
                                <i class="bi bi-box-seam me-2"></i> Inventario Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active p-3 rounded fw-bold text-white mb-1 btn btn-outline-danger"
                                href="../logout.php">
                                <i class="bi bi-box-arrow-left me-2"></i>Salir
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">


                <div class="mb-4">
                    <h2 class="section-title">Control de Citas Médicas</h2>
                    <p class="section-description">Genera disponibilidad horaria y administra la agenda de los
                        profesionales.
                    </p>
                </div>

                <?= $mensaje ?>

                <div class="row">
                    <!-- Formulario generar horario -->
                    <div class="col-lg-4 mb-4">
                        <div class="admin-card">
                            <h5 class="mb-4" style="color: var(--primary-color); font-weight: 600;">
                                <i class="bi bi-calendar-plus me-2"></i>Generar Horario
                            </h5>
                            <form method="POST" action="">
                                <input type="hidden" name="generar_horas" value="1">

                                <div class="mb-3">
                                    <label class="form-label">Profesional</label>
                                    <select name="id_profesional" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($profesionales as $prof): ?>
                                        <option value="<?= $prof['id'] ?>"><?= htmlspecialchars($prof['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Desde el día (X)</label>
                                    <input type="date" name="fecha_inicio" class="form-control" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Hasta el día (Y)</label>
                                    <input type="date" name="fecha_fin" class="form-control" required>
                                </div>

                                <div class="alert alert-warning" style="font-size: 0.85rem;">
                                    <i class="bi bi-info-circle"></i> Se generarán bloques cada 30 min desde las 09:00
                                    hasta
                                    las
                                    17:00 hrs.
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    Generar Disponibilidad
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de horas -->
                    <div class="col-lg-8">
                        <div class="admin-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 style="color: var(--primary-color); font-weight: 600;">
                                    <i class="bi bi-list-task me-2"></i>Horas Próximas
                                </h5>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Profesional</th>
                                            <th>Estado Actual</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($horas) > 0): ?>
                                        <?php foreach ($horas as $h): ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($h['fecha'])) ?></td>
                                            <td><?= date('H:i', strtotime($h['hora'])) ?> hrs</td>
                                            <td><?= htmlspecialchars($h['profesional']) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= $h['estado'] ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $h['estado'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-flex gap-2">
                                                    <input type="hidden" name="actualizar_estado" value="1">
                                                    <input type="hidden" name="id_hora" value="<?= $h['id'] ?>">
                                                    <select name="estado" class="form-select form-select-sm"
                                                        onchange="this.form.submit()">
                                                        <option value="disponible"
                                                            <?= $h['estado'] == 'disponible'    ? 'selected' : '' ?>>
                                                            Disponible
                                                        </option>
                                                        <option value="no_disponible"
                                                            <?= $h['estado'] == 'no_disponible' ? 'selected' : '' ?>>No
                                                            Disponible</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No hay horas generadas
                                                próximamente.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php 
    @include_once "../parts/footer.php";?>