<?php
// 1. Validar e iniciar la sesión del administrador
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de Acceso Estricto: Si no es administrador, redirigir
if (!isset($_SESSION['id_admin'])) {
    if (isset($_SESSION['id_usuario'])) {
        header("Location: cliente.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

// 2. Incluir conexiones de la base de datos
@include "api/db.php";
@include "api/config.php";

$id_admin = $_SESSION['id_admin'];
$nombre_admin = $_SESSION['nombre_admin'];

try {
    // 3. CONSULTA: Ventas totales del mes en curso (Excluyendo cancelados)
    $mes_actual = date('Y-m');
    $sql_ventas = "SELECT SUM(total) AS total_mes FROM pedidos WHERE fecha_pedido LIKE ? AND estado != 'cancelado'";
    $res_ventas = $db->query($sql_ventas, $mes_actual . '%')->fetchAll();
    $ventas_mes = !empty($res_ventas[0]['total_mes']) ? $res_ventas[0]['total_mes'] : 0;

    // 4. CONSULTA: Citas programadas para el día de hoy
    $hoy = date('Y-m-d');
    // Nota: Adapta el nombre de tu tabla de citas/agenda (aquí asumimos 'agendamiento' o 'citas')
    // Si tu tabla se llama de otra forma, modifica el FROM. Ejemplo basándonos en una estructura estándar de citas:
    $sql_citas_hoy = "SELECT COUNT(*) AS total_citas FROM citas WHERE fecha = ?";
    $res_citas = $db->query($sql_citas_hoy, $hoy)->fetchAll();
    $citas_hoy = !empty($res_citas[0]['total_citas']) ? $res_citas[0]['total_citas'] : 0;

    // 5. CONSULTA: Listado de próximas citas médicas para la tabla
    $sql_lista_citas = "SELECT c.id, u.nombre AS paciente, c.tipo_consulta, c.fecha, c.hora, c.estado 
                        FROM citas c
                        INNER JOIN usuarios u ON c.id_usuario = u.id
                        WHERE c.fecha >= ? 
                        ORDER BY c.fecha ASC, c.hora ASC LIMIT 10";
    $lista_citas = $db->query($sql_lista_citas, $hoy)->fetchAll();

} catch (Exception $e) {
    // Valores por defecto en caso de que falte crear alguna tabla en la base de datos
    $ventas_mes = 0;
    $citas_hoy = 0;
    $lista_citas = [];
}

// Procesar cambio de estado de cita si se presiona el botón check (Opcional)
if (isset($_GET['completar_cita'])) {
    $id_cita = (int)$GET['completar_cita'];
    try {
        $db->query("UPDATE citas SET estado = 'completada' WHERE id = ?", $id_cita);
        header("Location: administrador.php");
        exit();
    } catch(Exception $e) {}
}

// 6. Vincular componentes visuales globales si existen
@include_once "parts/head.php";
// @include_once "parts/nav.php";
?>

<head>
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
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="administrador.php">OptiVision <span
                    class="badge bg-secondary fs-xs ms-1">Panel
                    Interno</span></a>
            <div class="d-flex align-items-center text-white gap-3">
                <span class="small d-none d-sm-inline"><i class="bi bi-person-circle me-1 text-info"></i> Hola,
                    <?php echo htmlspecialchars($nombre_admin); ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-box-arrow-left"></i>
                    Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar p-3 shadow-sm min-vh-100">
                <div class="position-sticky pt-2">
                    <span class="small d-none d-sm-inline text-center "><i
                            class="bi bi-person-circle me-1 text-info"></i> Hola,
                        <?php echo htmlspecialchars($nombre_admin); ?></span>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link active p-3 rounded fw-bold text-white mb-1"
                                style="background-color: var(--primary-color);" href="administrador.php">
                                <i class="bi bi-speedometer2 me-2"></i> Resumen General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary p-3" href="admin/control_citas.php">
                                <i class="bi bi-calendar-event me-2"></i> Control de Citas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary p-3" href="admin/inventario.php">
                                <i class="bi bi-box-seam me-2"></i> Inventario Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active p-3 rounded fw-bold text-white mb-1 btn btn-outline-danger"
                                href="logout.php">
                                <i class="bi bi-box-arrow-left me-2"></i>Salir
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 style="font-family: Playfair Display, serif;" class="fw-bold mb-0 text-dark">Panel de Control
                    </h2>
                    <span class="badge bg-light text-dark border p-2 shadow-xs"><i class="bi bi-clock me-1"></i>
                        <?php echo date('d-m-Y'); ?></span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius:12px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Ventas del Mes</h6>
                                    <h3 class="fw-bold mb-0 text-dark">
                                        $<?php echo number_format($ventas_mes, 0, ',', '.'); ?></h3>
                                </div>
                                <div class="bg-light p-3 rounded-circle text-success">
                                    <i class="bi bi-currency-dollar fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius:12px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Citas para Hoy</h6>
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo $citas_hoy; ?> Pacientes</h3>
                                </div>
                                <div class="bg-light p-3 rounded-circle text-info">
                                    <i class="bi bi-calendar-check fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px; background:#fff;">
                    <h5 style="font-family: Playfair Display, serif;" class="fw-bold text-dark mb-3">Próximas Citas
                        Médicas</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Paciente</th>
                                    <th>Tipo de Consulta</th>
                                    <th>Fecha / Hora</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lista_citas)): ?>
                                <?php foreach ($lista_citas as $cita): 
                                        $badge_estado = "bg-warning text-dark"; // pendiente
                                        if ($cita['estado'] === 'completada') $badge_estado = "bg-success text-white";
                                        if ($cita['estado'] === 'cancelada') $badge_estado = "bg-danger text-white";
                                        
                                        // Formatear fecha sutilmente si es hoy
                                        $fecha_cita = ($cita['fecha'] === $hoy) ? "Hoy" : date("d-m-Y", strtotime($cita['fecha']));
                                    ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($cita['paciente']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($cita['tipo_consulta']); ?></td>
                                    <td><?php echo $fecha_cita; ?> - <?php echo substr($cita['hora'], 0, 5); ?> hrs</td>
                                    <td><span
                                            class="badge <?php echo $badge_estado; ?> text-capitalize"><?php echo $cita['estado']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cita['estado'] === 'pendiente'): ?>
                                        <a href="?completar_cita=<?php echo $cita['id']; ?>"
                                            class="btn btn-sm btn-outline-success" title="Marcar como atendido">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-light border-0 text-muted" disabled><i
                                                class="bi bi-dash"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                        No hay citas médicas próximas registradas en el sistema.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

        </div>
    </div>



    <?php
@include_once "parts/footer.php";
?>