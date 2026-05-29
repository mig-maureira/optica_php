<?php
// 1. Validar e iniciar la sesión del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario no ha iniciado sesión, lo mandamos de vuelta al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// conexiones de la base de datos
@include "api/db.php";
@include "api/config.php";

$id_usuario = $_SESSION['id_usuario'];

try {
    //  Obtener los datos del cliente y su previsión de salud
    $sql_usuario = "SELECT u.*, p.nombre AS prevision_nombre 
                    FROM usuarios u 
                    LEFT JOIN prevision p ON u.id_prevision = p.id 
                    WHERE u.id = ?";
    $res_user = $db->query($sql_usuario, $id_usuario)->fetchAll();
    
    if (empty($res_user)) {
        // Por seguridad, si el ID no existe en la BD limpiamos sesión
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    $usuario = $res_user[0];

    // Extraer iniciales para el avatar circular (Ej: Juan Pérez -> JP)
    $palabras = explode(" ", $usuario['nombre']);
    $iniciales = strtoupper(substr($palabras[0], 0, 1) . (isset($palabras[1]) ? substr($palabras[1], 0, 1) : ''));

    // historial de compras  (Tabla `pedidos`)
    $sql_pedidos = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_pedido DESC";
    $historial_pedidos = $db->query($sql_pedidos, $id_usuario)->fetchAll();

} catch (Exception $e) {
    // Evitar caídas críticas de pantalla en blanco
    $usuario = [
        'nombre' => $_SESSION['nombre_usuario'],
        'correo' => 'Error al cargar',
        'rut' => 'Error al cargar',
        'prevision_nombre' => 'Particular'
    ];
    $iniciales = "??";
    $historial_pedidos = [];
}

@include_once "parts/head.php";
@include_once "parts/nav.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel | OptiVision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link class="styles" rel="stylesheet" href="optivision-styles.css">
</head>

<body style="background-color: var(--bg-light);">

    <div class="container" style="margin-top: 120px; margin-bottom: 60px;">
        <div class="row g-4">

            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm p-4 text-center" style="border-radius:16px; background:#fff;">
                    <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                        style="width:72px; height:72px; background-color: var(--primary-color) !important;">
                        <?php echo $iniciales; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                    <p class="small text-muted mb-1">Previsión:
                        <?php echo htmlspecialchars($usuario['prevision_nombre'] ?? 'No registrada'); ?></p>
                    <p class="text-xs text-secondary opacity-75 mb-3" style="font-size: 0.8rem;">
                        <?php echo htmlspecialchars($usuario['correo']); ?></p>

                    <hr class="opacity-25">

                    <div class="list-group list-group-flush text-start mt-2">
                        <span class="list-group-item border-0 p-2 text-muted small"><i class="bi bi-card-text me-2"></i>
                            RUT: <?php echo htmlspecialchars($usuario['rut']); ?></span>
                        <span class="list-group-item border-0 p-2 text-muted small"><i
                                class="bi bi-person-badge me-2"></i> User:
                            @<?php echo htmlspecialchars($usuario['username']); ?></span>

                        <hr class="opacity-25 my-2">

                        <a href="carrito.php"
                            class="list-group-item list-group-item-action border-0 p-2 rounded small"><i
                                class="bi bi-cart3 me-2"></i> Mi Carrito</a>
                        <a href="logout.php"
                            class="list-group-item list-group-item-action border-0 p-2 rounded small text-danger"><i
                                class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-lg-9">

                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:16px; background:#fff;">
                    <h5 style="font-family: Playfair Display, serif;" class="fw-bold mb-3 text-dark">
                        <i class="bi bi-file-earmark-medical me-2 text-info"></i>Receta Médica Guardada
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center small align-middle mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th>Ojo</th>
                                    <th>Esfera (ESF)</th>
                                    <th>Cilindro (CIL)</th>
                                    <th>Eje</th>
                                    <th>Adición</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">Derecho (OD)</td>
                                    <td>-1.50</td>
                                    <td>-0.50</td>
                                    <td>180°</td>
                                    <td>+1.25</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Izquierdo (OI)</td>
                                    <td>-1.25</td>
                                    <td>-0.75</td>
                                    <td>165°</td>
                                    <td>+1.25</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted mt-2 d-block">* Validado por Consulta de Especialistas OptiVision.</small>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px; background:#fff;">
                    <h5 style="font-family: Playfair Display, serif;" class="fw-bold mb-3 text-dark">
                        <i class="bi bi-bag-check me-2 text-primary"></i>Historial de Compras Recientes
                    </h5>

                    <?php if (!empty($historial_pedidos)): ?>
                    <?php foreach ($historial_pedidos as $pedido): 
                            // Configurar colores de badges según el estado del pedido en tu ENUM
                            $badge_color = "bg-warning text-dark"; // pendiente
                            if ($pedido['estado'] === 'pagado') $badge_color = "bg-info text-white";
                            if ($pedido['estado'] === 'enviado') $badge_color = "bg-primary text-white";
                            if ($pedido['estado'] === 'entregado') $badge_color = "bg-success text-white";
                            if ($pedido['estado'] === 'cancelado') $badge_color = "bg-danger text-white";
                        ?>
                    <div
                        class="d-flex align-items-center justify-content-between border border-light p-3 rounded mb-2 shadow-xs">
                        <div>
                            <span class="badge <?php echo $badge_color; ?> mb-1 text-capitalize">
                                <?php echo $pedido['estado']; ?>
                            </span>
                            <p class="mb-0 fw-bold small text-dark">Pedido #OV-<?php echo $pedido['id']; ?></p>
                            <small class="text-muted">Fecha:
                                <?php echo date("d-m-Y H:i", strtotime($pedido['fecha_pedido'])); ?></small>
                        </div>
                        <span class="fw-bold text-dark">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?>
                            CLP</span>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-4 opacity-75">
                        <i class="bi bi-bag-x text-muted fs-2"></i>
                        <p class="small text-secondary mt-2 mb-0">Aún no has registrado ninguna compra en nuestro sitio.
                        </p>
                        <a href="lentes.php" class="btn btn-sm btn-link text-decoration-none p-0 mt-1">Explorar
                            catálogo</a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>



    <?php
@include_once "parts/footer.php";
?>