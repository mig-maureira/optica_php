<?php
// 1. Iniciar o verificar la sesión del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar controladores de base de datos y configuración
@include "api/db.php";
@include "api/config.php";

// Capturar el ID del producto desde la URL de manera segura
$id_producto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$producto = null;

if ($id_producto > 0) {
    try {
        // Consultar el producto actual usando tu clase $db
        $res = $db->query("SELECT * FROM productos WHERE id = ?", $id_producto)->fetchAll();
        if (!empty($res)) {
            $producto = $res[0];
        }
    } catch (Exception $e) {
        $producto = null;
    }
}

// Si el producto no existe o el ID es inválido, puedes redirigir al catálogo o index
if (!$producto) {
    header("Location: index.php");
    exit();
}

// Cargar cabeceras visuales globales de tu sitio
@include_once "parts/head.php";
@include_once "parts/nav.php";
?>

<div class="container py-5" style="margin-top: 100px;">

    <div class="mb-4">
        <a href="index.php#productos"
            class="btn btn-link text-decoration-none text-secondary p-0 align-items-center d-inline-flex gap-2">
            <i class="bi bi-arrow-left fs-5"></i> Volver al Catálogo
        </a>
    </div>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="position-sticky" style="top: 110px;">
                <div class="card border-0 shadow-sm overflow-hidden p-3 bg-white" style="border-radius: 20px;">
                    <?php if (!empty($producto['imagen_url']) && file_exists($producto['imagen_url'])): ?>
                    <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" class="img-fluid w-100"
                        style="object-fit: contain; max-height: 480px; border-radius: 12px;"
                        alt="<?php echo htmlspecialchars($producto['titulo']); ?>">
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light text-muted flex-column gap-2"
                        style="height: 400px; border-radius: 12px;">
                        <i class="bi bi-glasses display-2 text-secondary opacity-25"></i>
                        <span class="small fw-medium text-uppercase tracking-wider">Imagen de Referencia</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="d-flex flex-column h-100 justify-content-center">

                <?php if (!empty($producto['etiqueta'])): ?>
                <div class="mb-2">
                    <span class="badge px-3 py-2 fs-xs text-uppercase tracking-wider shadow-sm text-dark bg-info"
                        style="border-radius: 50px; font-size: 0.75rem; font-weight: 600;">
                        <?php echo htmlspecialchars($producto['etiqueta']); ?>
                    </span>
                </div>
                <?php endif; ?>

                <p class="text-uppercase tracking-widest text-muted fw-semibold mb-1"
                    style="font-size: 0.85rem; color: var(--accent-color) !important;">
                    <?php echo htmlspecialchars($producto['marca']); ?>
                </p>

                <h1 class="display-5 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    <?php echo htmlspecialchars($producto['titulo']); ?>
                </h1>

                <div class="d-flex align-items-baseline gap-3 mb-4 bg-white p-3 border-start border-4 shadow-sm"
                    style="border-radius: 0 12px 12px 0; border-color: var(--primary-color) !important;">
                    <span class="fs-2 fw-bold text-dark">
                        $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                    </span>
                    <?php if (!empty($producto['precio_anterior'])): ?>
                    <span class="fs-5 text-muted text-decoration-line-through">
                        $<?php echo number_format($producto['precio_anterior'], 0, ',', '.'); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold text-secondary mb-2" style="font-family: 'Outfit', sans-serif;">Descripción del
                        Modelo</h5>
                    <p class="text-muted lh-base" style="font-size: 1.05rem;">
                        <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                    </p>
                </div>

                <div class="mb-4 d-flex align-items-center gap-2">
                    <span class="fw-medium text-secondary">Disponibilidad:</span>
                    <?php if ($producto['stock'] > 5): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"
                        style="border-radius: 4px;">✔ Stock Disponible (<?php echo $producto['stock']; ?>
                        unidades)</span>
                    <?php elseif ($producto['stock'] > 0): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"
                        style="border-radius: 4px;">⚠ ¡Últimas unidades disponibles!
                        (<?php echo $producto['stock']; ?>)</span>
                    <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"
                        style="border-radius: 4px;">❌ Temporalmente Agotado</span>
                    <?php endif; ?>
                </div>

                <?php if ($producto['stock'] > 0): ?>
                <form action="agregar_carrito.php" method="POST" class="row g-3 align-items-center">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">

                    <div class="col-3 col-sm-2">
                        <label class="form-label small text-muted fw-bold d-block mb-1">Cant.</label>
                        <select name="cantidad" class="form-select form-select-lg"
                            style="border-radius: 8px; font-size: 0.95rem;">
                            <?php for ($i = 1; $i <= min($producto['stock'], 5); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-9 col-sm-10 pt-4">
                        <button type="submit"
                            class="btn btn-primary-custom w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm"
                            style="border-radius: 10px; background-color: var(--primary-color); border: none; color: #white;">
                            <i class="bi bi-cart-plus-fill fs-5"></i> Agregar al Carrito de Compras
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="bg-light p-3 text-center border rounded-3 text-muted">
                    <i class="bi bi-bell me-2"></i> Próximamente repondremos este hermoso modelo. ¡Mantente atento!
                </div>
                <?php endif; ?>

                <div class="row g-3 mt-5 pt-4 border-top border-light-subtle">
                    <div class="col-sm-6 d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded-circle text-primary"><i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark small">Garantía Óptica</h6>
                            <small class="text-muted">Cristales y armazón 100% asegurados.</small>
                        </div>
                    </div>
                    <div class="col-sm-6 d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded-circle text-success"><i class="bi bi-truck fs-4"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark small">Despacho o Retiro</h6>
                            <small class="text-muted">Envío rápido a domicilio o retiro local.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="bg-white border-top mt-5 py-2">
    <?php 
    // Tu archivo 'productos_lista.php' ya está configurado para leer la variable $_GET['id'] 
    // y traer de forma automática 4 productos recomendados AL AZAR excluyendo el actual.
    @include "parts/productos_lista.php"; 
    ?>
</div>

<?php
// Cargar pie de página global
@include_once "parts/footer.php";
?>