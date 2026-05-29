<?php
// server
@include "api/db.php";
@include "api/config.php";

// 2. Capturar filtros de la URL ($_GET) con valores por defecto
$categorias_seleccionadas = isset($_GET['categorias']) ? $_GET['categorias'] : [];
$materiales_seleccionados = isset($_GET['materiales']) ? $_GET['materiales'] : [];

// Si no viene un precio en la URL, el máximo por defecto es 300000
$precio_maximo = isset($_GET['precio_max']) ? (int)$_GET['precio_max'] : 300000;

// 3. Construir la consulta SQL dinámica base
$sql = "SELECT * FROM productos WHERE stock > 0";
$params = [];

// --- FILTRO: Categorías ---
if (!empty($categorias_seleccionadas)) {
    // Genera marcadores de posición (?, ?, ...) dinámicamente según la cantidad elegida
    $placeholders = implode(',', array_fill(0, count($categorias_seleccionadas), '?'));
    $sql .= " AND categoria IN ($placeholders)";
    foreach ($categorias_seleccionadas as $cat) {
        $params[] = $cat; // Almacena el valor (ej: 'Opticos', 'Sol')
    }
}

// --- FILTRO: Materiales ---
if (!empty($materiales_seleccionados)) {
    $placeholders = implode(',', array_fill(0, count($materiales_seleccionados), '?'));
    $sql .= " AND material IN ($placeholders)";
    foreach ($materiales_seleccionados as $mat) {
        $params[] = $mat; // Almacena el valor (ej: 'Acetato', 'Metal')
    }
}

// --- FILTRO: Rango de Precios (Entre 0 y $precio_maximo) ---
$sql .= " AND precio >= 0 AND precio <= ?";
$params[] = $precio_maximo; // Agrega el tope al arreglo de parámetros

// Ordenar resultados (opcional, ej: del más nuevo al más viejo)
$sql .= " ORDER BY id DESC";

// 4. Ejecutar la consulta de forma segura usando TRY-CATCH para capturar excepciones SQL
$error_bd = false; // Bandera para saber si falló la base de datos
$productos = [];   // Inicializamos la variable vacía

try {
    if (!empty($params)) {
        // El operador ... (splat operator) desempaqueta el array y pasa los elementos
        // uno por uno como argumentos individuales requeridos por tu función query()
        $productos = $db->query($sql, ...$params)->fetchAll();
    } else {
        $productos = $db->query($sql)->fetchAll();
    }
} catch (Exception $e) {
    // Si salta un error de columna desconocida o fallo de conexión, se activa la contingencia
    $error_bd = true;
    $productos = [];
}

@include_once "parts/head.php";
@include_once "parts/nav.php";
?>



<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-3">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Nuestro Catálogo</h1>
        <p class="lead mb-0 opacity-75">Encuentra el armazón perfecto para tu estilo y necesidades visuales.</p>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4">

        <div class="col-lg-3">
            <form action="" method="GET">
                <div class="card border-0 p-4 shadow-sm" style="background-color: #fff; border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0" style="color: var(--text-dark);">Filtros</h5>
                        <?php if(!empty($categorias_seleccionadas) || !empty($materiales_seleccionados) || $precio_maximo != 300000): ?>
                        <a href="lentes.php" class="btn btn-link p-0 text-decoration-none text-muted small">Limpiar</a>
                        <?php endif; ?>
                    </div>
                    <hr class="text-muted opacity-25">

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Categoría</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="categorias[]" value="Opticos"
                                id="cat1" <?php echo in_array('Opticos', $categorias_seleccionadas) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="cat1">Lentes Ópticos</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="categorias[]" value="Sol" id="cat2"
                                <?php echo in_array('Sol', $categorias_seleccionadas) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="cat2">Gafas de Sol</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categorias[]" value="Luz Azul"
                                id="cat3"
                                <?php echo in_array('Luz Azul', $categorias_seleccionadas) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="cat3">Filtro Luz Azul</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Material de Armazón</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="materiales[]" value="Acetato"
                                id="mat1" <?php echo in_array('Acetato', $materiales_seleccionados) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="mat1">Acetato Premium</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="materiales[]" value="Metal" id="mat2"
                                <?php echo in_array('Metal', $materiales_seleccionados) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="mat2">Titanio / Metal</label>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold text-secondary">Precio Máximo: <span
                                id="currentPrice">$<?php echo number_format($precio_maximo, 0, ',', '.'); ?></span></label>
                        <input type="range" class="form-range" name="precio_max" min="0" max="300000" step="50"
                            id="priceRange" value="<?php echo $precio_maximo; ?>" onchange="this.form.submit()">
                        <div class="d-flex justify-content-between text-muted small mt-1">
                            <span>$0</span>
                            <span>$300,000</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-9">
            <div class="row g-4">

                <?php 
        // Si ocurrió un error técnico en la base de datos, invocamos la plantilla de error
        if ($error_bd): 
            @include "parts/error_catalogo.php";
        
        // Si la base de datos funcionó bien pero devolvió un catálogo vacío
        elseif (empty($productos)): 
        ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-secondary">No se encontraron lentes con los criterios seleccionados.</h5>
                    <p class="text-muted">Prueba removiendo algunos filtros para ver más opciones.</p>
                    <a href="?" class="btn btn-sm btn-outline-secondary mt-2">Mostrar todo</a>
                </div>

                <?php 
        // Si hay productos, los recorremos normalmente
        else: 
            foreach ($productos as $prod): 
        ?>
                <div class="col-md-4 col-sm-6">
                    <div class="product-card">
                        <div class="product-image">
                            <a href="detalle.php?id=<?php echo $prod['id']; ?>">
                                <?php $ruta_imagen = !empty($prod['imagen_url']) ? htmlspecialchars($prod['imagen_url']) : 'img/productos/default.jpg'; ?>
                                <img src="<?php echo $ruta_imagen; ?>"
                                    alt="<?php echo htmlspecialchars($prod['titulo'] ?? 'Lente'); ?>">
                            </a>
                            <?php if (!empty($prod['etiqueta'])): ?>
                            <span class="product-badge"><?php echo htmlspecialchars($prod['etiqueta']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
                            <h5 class="product-title">
                                <a href="detalle.php?id=<?php echo $prod['id']; ?>"
                                    class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($prod['titulo'] ?? 'Modelo Óptico'); ?>
                                </a>
                            </h5>
                            <p class="product-description"><?php echo htmlspecialchars($prod['descripcion'] ?? ''); ?>
                            </p>
                            <div class="product-footer">
                                <div>
                                    <span
                                        class="product-price">$<?php echo number_format($prod['precio'] ?? 0, 0, ',', '.'); ?></span>
                                </div>

                                <form action="agregar_carrito.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id_producto" value="<?php echo $prod['id']; ?>">
                                    <input type="hidden" name="cantidad" value="1"> <button type="submit"
                                        class="btn btn-primary-custom btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-cart-plus"></i> Agregar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>


<?php
@include_once "parts/footer.php";
?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pequeño script para actualizar el texto del precio dinámicamente al mover el deslizador
const range = document.getElementById('priceRange');
const currentPrice = document.getElementById('currentPrice');
range.addEventListener('input', function() {
    currentPrice.textContent = '$' + Number(range.value).toLocaleString('es-CL');
});
</script>
</body>

</html>