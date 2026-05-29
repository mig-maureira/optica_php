<?php

// Identificar si estamos en el detalle de un producto
$id_producto_actual = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_producto_actual > 0) {
    // Usando tu clase: El segundo parámetro reemplaza automáticamente las variables de forma segura
    $sql = "SELECT * FROM productos WHERE id != ? AND stock > 0 ORDER BY RAND() LIMIT 4";
    $productos = $db->query($sql, $id_producto_actual)->fetchAll();
} else {
    // Si es la página principal
    $sql = "SELECT * FROM productos WHERE stock > 0 LIMIT 4";
    $productos = $db->query($sql)->fetchAll();
}


?>
<section id="productos" class="products-section">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label">Colección</p>
            <h2 class="section-title">Te Puede Interesar</h2>
            <p class="section-description">
                Descubre nuestros modelos más populares con la mejor calidad y diseño.
            </p>
        </div>

        <div class="row g-4">
            <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $prod): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card">
                    <div class="product-image">
                        <a href="detalle.php?id=<?php echo $prod['id']; ?>">
                            <?php 
                                    $ruta_imagen = !empty($prod['imagen_url']) ? htmlspecialchars($prod['imagen_url']) : 'assets/img/productos/default.jpg'; 
                                    ?>
                            <img src="<?php echo $ruta_imagen; ?>"
                                alt="<?php echo htmlspecialchars($prod['titulo']); ?>">
                        </a>

                        <?php if (!empty($prod['etiqueta'])): ?>
                        <span class="product-badge"><?php echo htmlspecialchars($prod['etiqueta']); ?></span>
                        <?php endif; ?>

                        <div class="product-actions">
                            <button class="btn-icon"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>

                    <div class="product-body">
                        <h5 class="product-title">
                            <a href="detalle.php?id=<?php echo $prod['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($prod['titulo']); ?>
                            </a>
                        </h5>
                        <p class="product-description">
                            <?php echo htmlspecialchars($prod['descripcion']); ?>
                        </p>
                        <div class="product-footer">
                            <div>
                                <span
                                    class="product-price">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></span>

                                <?php if (!empty($prod['precio_anterior'])): ?>
                                <span
                                    class="product-price-old">$<?php echo number_format($prod['precio_anterior'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary-custom btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-cart-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12 text-center">
                <p>No hay más productos recomendados por el momento.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>