<?php
// 1. Iniciar la sesión (DEBE SER LA PRIMERA LÍNEA REAL DEL ARCHIVO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir conexiones
@include "api/db.php";
@include "api/config.php";

// Simulamos un ID de usuario logueado
$id_usuario_sesion = 1; 

$mensaje_alerta = "";

// ==========================================
// DETECTAR ACCIONES ANTES DE PINTAR CUALQUIER COSA
// ==========================================

// Acción A: DISMINUIR O ELIMINAR 1 UNIDAD del producto específico en el carrito
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id_eliminar = (int)$_GET['id'];
    
    if (isset($_SESSION['carrito'][$id_eliminar])) {
        // Si la cantidad es mayor a 1, solo restamos una unidad
        if ($_SESSION['carrito'][$id_eliminar]['cantidad'] > 1) {
            $_SESSION['carrito'][$id_eliminar]['cantidad']--;
        } else {
            // Si le quedaba solo 1 unidad, eliminamos el producto por completo del carro
            unset($_SESSION['carrito'][$id_eliminar]);
        }
    }
    
    // Redirección limpia (evita loops de recarga en blanco o re-ejecuciones accidentales)
    header("Location: carrito.php");
    exit();
}
// Acción B: Vaciar todo el carrito
if (isset($_GET['accion']) && $_GET['accion'] === 'vaciar') {
    unset($_SESSION['carrito']);
    header("Location: carrito.php");
    exit();
}

// Acción C: PROCESAR LA COMPRA 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_compra'])) {
    if (!empty($_SESSION['carrito'])) {
        try {
            // Calcular el total general de la compra
            $total_pedido = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $total_pedido += $item['precio'] * $item['cantidad'];
            }

            // 1. Insertar en la tabla `pedidos`
            $sql_pedido = "INSERT INTO pedidos (id_usuario, total, estado) VALUES (?, ?, 'pendiente')";
            $db->query($sql_pedido, $id_usuario_sesion, $total_pedido);
            
            // Obtener el ID del pedido recién creado
            $pedido_id_res = $db->query("SELECT id FROM pedidos WHERE id_usuario = ? ORDER BY id DESC LIMIT 1", $id_usuario_sesion)->fetchAll();
            $id_nuevo_pedido = $pedido_id_res[0]['id']; // Cambiado a fetchAll()[0] por compatibilidad con tu clase

            // 2. Insertar cada producto en la tabla `pedido_detalles` y descontar stock
            foreach ($_SESSION['carrito'] as $id_prod => $item) {
                $sql_detalle = "INSERT INTO pedido_detalles (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
                $db->query($sql_detalle, $id_nuevo_pedido, $id_prod, $item['cantidad'], $item['precio']);

                $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
                $db->query($sql_stock, $item['cantidad'], $id_prod);
            }

            // 3. Vaciar el carrito de la sesión
            unset($_SESSION['carrito']);

            $mensaje_alerta = '
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-bag-check-fill me-2"></i><strong>¡Compra Registrada!</strong> Tu pedido #' . $id_nuevo_pedido . ' ha sido ingresado como pendiente de pago.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

        } catch (Exception $e) {
            $mensaje_alerta = '
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error:</strong> No se pudo procesar tu compra debido a un problema técnico.
            </div>';
        }
    }
}

@include_once "parts/head.php";
@include_once "parts/nav.php";
?>
<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-3">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Tu Carrito de Compras</h1>
        <p class="lead mb-0 opacity-75">Revisa tus artículos seleccionados antes de finalizar tu orden.</p>
    </div>
</section>

<div class="container my-5">

    <?php echo $mensaje_alerta; ?>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px; background-color: #fff;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Artículos Seleccionados</h5>
                    <?php if (!empty($_SESSION['carrito'])): ?>
                    <a href="carrito.php?accion=vaciar" class="btn btn-sm btn-outline-danger style=" border-radius:
                        6px;">
                        <i class="bi bi-trash3 me-1"></i> Vaciar Carrito
                    </a>
                    <?php endif; ?>
                </div>
                <hr class="text-muted opacity-25">

                <?php 
                $subtotal_general = 0;
                if (!empty($_SESSION['carrito'])): 
                    foreach ($_SESSION['carrito'] as $id_prod => $item): 
                        $subtotal_item = $item['precio'] * $item['cantidad'];
                        $subtotal_general += $subtotal_item;
                ?>
                <div class="row align-items-center mb-3 pb-3 border-bottom border-light">
                    <div class="col-3 col-md-2">
                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" class="img-fluid rounded"
                            alt="Lente" style="max-height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-5 col-md-5">
                        <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($item['titulo']); ?></h6>
                        <p class="text-muted small mb-0">Precio Unitario:
                            $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                    </div>
                    <div class="col-4 col-md-3 text-center">
                        <span class="badge bg-light text-dark border p-2 fs-6">Cant:
                            <?php echo $item['cantidad']; ?></span>
                    </div>
                    <div
                        class="col-12 col-md-2 text-md-end mt-2 mt-md-0 d-flex justify-content-between align-items-center">
                        <span
                            class="fw-bold text-dark d-md-block d-none">$<?php echo number_format($subtotal_item, 0, ',', '.'); ?></span>
                        <a href="carrito.php?accion=eliminar&id=<?php echo $id_prod; ?>"
                            class="btn btn-sm text-danger p-1 ms-auto" title="Quitar 1 unidad">
                            <i class="bi bi-dash-circle fs-5"></i> </a>
                    </div>
                </div>
                <?php 
                    endforeach; 
                else: 
                ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3.5rem;"></i>
                    <h5 class="text-secondary mt-3">Tu bolsa de compras está vacía</h5>
                    <p class="text-muted small">Explora nuestro catálogo para añadir los mejores lentes ópticos y de
                        sol.</p>
                    <a href="lentes.php" class="btn btn-primary-custom btn-sm mt-2 px-4 py-2">Ir al Catálogo</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4"
                style="border-radius: 16px; background-color: #fff; position: sticky; top: 100px;">
                <h5 class="fw-bold mb-3 text-dark">Resumen de Orden</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span
                        class="fw-medium text-dark">$<?php echo number_format($subtotal_general, 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Envío</span>
                    <span class="text-success small fw-medium">Gratis (RM)</span>
                </div>
                <hr class="text-muted opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark fs-5">Total General</span>
                    <span
                        class="fw-bold text-primary fs-4">$<?php echo number_format($subtotal_general, 0, ',', '.'); ?></span>
                </div>

                <form action="" method="POST">
                    <button type="submit" name="procesar_compra" class="btn btn-accent w-100 py-3 fw-bold fs-6"
                        <?php echo empty($_SESSION['carrito']) ? 'disabled' : ''; ?>>
                        <i class="bi bi-credit-card me-2"></i> Confirmar Compra
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="lentes.php" class="text-decoration-none text-muted small"><i
                            class="bi bi-arrow-left me-1"></i> Seguir Comprando</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
@include_once "parts/footer.php";
?>