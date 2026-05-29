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

// Definir y crear ruta de imágenes si no existe
$ruta_subida = '../assets/img/productos/';
if (!is_dir($ruta_subida)) {
    mkdir($ruta_subida, 0777, true);
}

/**
 * Función auxiliar para procesar la subida de imágenes
 */
function procesarImagen($file, $ruta_subida) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $file['tmp_name'];
        $file_name = $file['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Extensiones permitidas
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $extensiones_validas)) {
            // Nombre único para evitar sobreescritura
            $nuevo_nombre = 'producto_' . uniqid() . '.' . $ext;
            $destino = $ruta_subida . $nuevo_nombre;
            
            if (move_uploaded_file($file_tmp, $destino)) {
                // Retornamos la ruta relativa para guardarla en la BD
                return 'assets/img/productos/' . $nuevo_nombre;
            }
        }
    }
    return null;
}

// ==========================================
// 1. ACCIÓN: CREAR PRODUCTO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $titulo = trim($_POST['titulo']);
    $marca = trim($_POST['marca']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (int) $_POST['precio'];
    $precio_anterior = !empty($_POST['precio_anterior']) ? (int) $_POST['precio_anterior'] : null;
    $etiqueta = !empty($_POST['etiqueta']) ? trim($_POST['etiqueta']) : null;
    $stock = (int) $_POST['stock'];

    // Procesar Imagen
    $imagen_url = procesarImagen($_FILES['imagen'], $ruta_subida);

    $db->query(
        "INSERT INTO productos (titulo, marca, descripcion, precio, precio_anterior, imagen_url, etiqueta, stock) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        $titulo, $marca, $descripcion, $precio, $precio_anterior, $imagen_url, $etiqueta, $stock
    );

    if ($db->affectedRows() > 0) {
        $mensaje = "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i>Producto creado exitosamente.</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al intentar registrar el producto.</div>";
    }
}
// ==========================================
// 2. ACCIÓN: ACTUALIZAR PRODUCTO (CORREGIDO)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_producto'])) {
    $id_producto = (int) $_POST['id_producto'];
    $titulo = trim($_POST['titulo']);
    $marca = trim($_POST['marca']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (int) $_POST['precio'];
    $precio_anterior = !empty($_POST['precio_anterior']) ? (int) $_POST['precio_anterior'] : null;
    $etiqueta = !empty($_POST['etiqueta']) ? trim($_POST['etiqueta']) : null;
    $stock = (int) $_POST['stock'];
    
    // CORRECCIÓN AQUÍ: Se cambió ->fetch() por ->fetchAll() y se evalúa la posición [0]
    $prod_actual = $db->query("SELECT imagen_url FROM productos WHERE id = ?", $id_producto)->fetchAll();
    $imagen_url = !empty($prod_actual) ? $prod_actual[0]['imagen_url'] : null;

    // Verificar si viene una nueva imagen
    $nueva_imagen = procesarImagen($_FILES['imagen'], $ruta_subida);
    if ($nueva_imagen !== null) {
        // Eliminar la foto antigua físicamente del servidor si existía
        if (!empty($imagen_url) && file_exists('../' . $imagen_url)) {
            @unlink('../' . $imagen_url);
        }
        $imagen_url = $nueva_imagen;
    }

    $db->query(
        "UPDATE productos SET titulo = ?, marca = ?, descripcion = ?, precio = ?, precio_anterior = ?, imagen_url = ?, etiqueta = ?, stock = ? WHERE id = ?",
        $titulo, $marca, $descripcion, $precio, $precio_anterior, $imagen_url, $etiqueta, $stock, $id_producto
    );

    $mensaje = "<div class='alert alert-info'><i class='bi bi-info-circle-fill me-2'></i>Producto actualizado correctamente.</div>";
}
// ==========================================
// 3. ACCIÓN: ELIMINAR PRODUCTO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_producto'])) {
    $id_eliminar = (int) $_POST['id_eliminar'];

    // Cambiado ->fetch() por ->fetchAll() para que no de error
    $prod = $db->query("SELECT imagen_url FROM productos WHERE id = ?", $id_eliminar)->fetchAll();
    
    // Verificamos si el arreglo no está vacío antes de intentar borrar el archivo físico
    if (!empty($prod) && !empty($prod[0]['imagen_url']) && file_exists('../' . $prod[0]['imagen_url'])) {
        @unlink('../' . $prod[0]['imagen_url']);
    }

    $db->query("DELETE FROM productos WHERE id = ?", $id_eliminar);
    $mensaje = "<div class='alert alert-danger'><i class='bi bi-trash-fill me-2'></i>Producto eliminado del inventario.</div>";
}
// Obtener todos los productos para listarlos en la tabla
$productos = $db->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <title>Inventario de Productos | Ópticas Graham</title>
    <style>
    :root {
        --primary-color: #2e838b;
        --accent-color: #E05D00;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8f9fa;
    }

    .sidebar .nav-link:hover {
        background-color: #f1f3f5;
        border-radius: 8px;
    }

    .admin-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(46, 131, 139, 0.12);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .img-preview-table {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        background-color: #eaeaea;
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="../administrador.php">OptiVision <span
                    class="badge bg-secondary fs-xs ms-1">Panel Interno</span></a>
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
                    <span class="small d-none d-sm-inline text-center mb-3 d-block"><i
                            class="bi bi-person-circle me-1 text-info"></i> Hola,
                        <?php echo htmlspecialchars($nombre_admin); ?></span>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link p-3 rounded fw-bold text-secondary mb-1" href="../administrador.php">
                                <i class="bi bi-speedometer2 me-2"></i> Resumen General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary p-3 rounded mb-1" href="control_citas.php">
                                <i class="bi bi-calendar-event me-2"></i> Control de Citas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white p-3 rounded" href="#"
                                style="background-color: var(--primary-color);">
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="section-title m-0">Inventario de Productos</h2>
                        <p class="section-description text-muted">Añade, edita o elimina los lentes del catálogo web.
                        </p>
                    </div>
                    <button class="btn text-white" style="background-color: var(--primary-color); border-radius: 8px;"
                        data-bs-toggle="modal" data-bs-target="#modalAgregar">
                        <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Lente
                    </button>
                </div>

                <?= $mensaje ?>

                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Marca</th>
                                    <th>Precio (CLP)</th>
                                    <th>Stock</th>
                                    <th>Etiqueta</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($productos) > 0): ?>
                                <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($p['imagen_url'])): ?>
                                        <img src="../<?= htmlspecialchars($p['imagen_url']) ?>"
                                            class="img-preview-table" alt="Foto">
                                        <?php else: ?>
                                        <div
                                            class="img-preview-table d-flex align-items-center justify-content-center text-muted small">
                                            Sin foto</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">
                                            <?= htmlspecialchars($p['descripcion']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($p['marca']) ?></td>
                                    <td>
                                        <strong>$<?= number_format($p['precio'], 0, ',', '.') ?></strong>
                                        <?php if($p['precio_anterior']): ?>
                                        <div class="text-muted text-decoration-line-through small">
                                            $<?= number_format($p['precio_anterior'], 0, ',', '.') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($p['stock'] > 5): ?>
                                        <span class="badge bg-success-subtle text-success"><?= $p['stock'] ?> uds</span>
                                        <?php elseif($p['stock'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning"><?= $p['stock'] ?>
                                            bajas</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Agotado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($p['etiqueta']): ?>
                                        <span
                                            class="badge bg-info text-dark"><?= htmlspecialchars($p['etiqueta']) ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#modalEditar" data-id="<?= $p['id'] ?>"
                                                data-titulo="<?= htmlspecialchars($p['titulo']) ?>"
                                                data-marca="<?= htmlspecialchars($p['marca']) ?>"
                                                data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
                                                data-precio="<?= $p['precio'] ?>"
                                                data-precio_anterior="<?= $p['precio_anterior'] ?>"
                                                data-etiqueta="<?= htmlspecialchars($p['etiqueta'] ?? '') ?>"
                                                data-stock="<?= $p['stock'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form method="POST" action=""
                                                onsubmit="return confirm('¿Seguro que deseas eliminar este producto? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="id_eliminar" value="<?= $p['id'] ?>">
                                                <button type="submit" name="eliminar_producto"
                                                    class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">No hay productos en la tienda.
                                        Haz clic en "Agregar Nuevo Lente" para comenzar.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Nuevo Producto
                        (Lente)</h5>
                    <button type="button" class="btn-close" data-bs-redirect="modal" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Título del Producto *</label>
                                <input type="text" name="titulo" class="form-control"
                                    placeholder="Ej: Classic Black Aviator" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Marca *</label>
                                <input type="text" name="marca" class="form-control" placeholder="Ej: Ray-Ban" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción Corta</label>
                                <textarea name="descripcion" class="form-control" rows="2" maxlength="255"
                                    placeholder="Breve reseña del armazón, materiales, etc."></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio Actual (CLP) *</label>
                                <input type="number" name="precio" class="form-control" placeholder="120000" min="0"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio Anterior (Opcional)</label>
                                <input type="number" name="precio_anterior" class="form-control" placeholder="150000"
                                    min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Stock Inicial *</label>
                                <input type="number" name="stock" class="form-control" value="0" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Etiqueta Especial</label>
                                <input type="text" name="etiqueta" class="form-control"
                                    placeholder="Ej: Nuevo, Oferta, Tendencia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Imagen del Producto *</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*" required>
                                <div class="form-text">Formatos permitidos: JPG, PNG, WEBP.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_producto" class="btn text-white"
                            style="background-color: var(--primary-color);">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Modificar
                        Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_producto" id="edit_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Título del Producto *</label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Marca *</label>
                                <input type="text" name="marca" id="edit_marca" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción Corta</label>
                                <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2"
                                    maxlength="255"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio Actual (CLP) *</label>
                                <input type="number" name="precio" id="edit_precio" class="form-control" min="0"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio Anterior</label>
                                <input type="number" name="precio_anterior" id="edit_precio_anterior"
                                    class="form-control" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Stock disponible *</label>
                                <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Etiqueta Especial</label>
                                <input type="text" name="etiqueta" id="edit_etiqueta" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cambiar Imagen (Opcional)</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                                <div class="form-text text-primary">Deja este campo vacío si no deseas modificar la
                                    imagen actual.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar_producto" class="btn btn-primary">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            // Extraer atributos data-* del botón presionado
            const id = button.getAttribute('data-id');
            const titulo = button.getAttribute('data-titulo');
            const marca = button.getAttribute('data-marca');
            const descripcion = button.getAttribute('data-descripcion');
            const precio = button.getAttribute('data-precio');
            const precioAnterior = button.getAttribute('data-precio_anterior');
            const etiqueta = button.getAttribute('data-etiqueta');
            const stock = button.getAttribute('data-stock');

            // Asignar los valores correspondientes al formulario dentro del modal
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_titulo').value = titulo;
            document.getElementById('edit_marca').value = marca;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_precio').value = precio;
            document.getElementById('edit_precio_anterior').value = precioAnterior ? precioAnterior : '';
            document.getElementById('edit_etiqueta').value = etiqueta;
            document.getElementById('edit_stock').value = stock;
        });
    }
    </script>
</body>

</html>
<?php 
@include_once "../parts/footer.php";
?>