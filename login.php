<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

@include "api/db.php";
@include "api/config.php";


$mensaje_alerta = "";



if (isset($_SESSION['id_admin'])) {
    header("Location: administrador.php");
    exit();
}
// Si está logueado pero es un Cliente común, lo desviamos a cliente.php
if (isset($_SESSION['id_usuario'])) {
    header("Location: cliente.php");
    exit();
}


// PROCESAR INICIO DE SESIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $tipo_login = isset($_POST['tipo_login']) ? $_POST['tipo_login'] : 'usuario'; // 'usuario' o 'admin'

    if (!empty($username) && !empty($password)) {
        try {
            if ($tipo_login === 'usuario') {
                // LOGIN DE CLIENTES (Tabla usuarios)
                $res = $db->query("SELECT * FROM usuarios WHERE username = ?", $username)->fetchAll();
                
                if (!empty($res)) {
                    $user = $res[0];
                    // NOTA: Si usas contraseñas en texto plano para pruebas usa ($password === $user['password'])
                    // Si ya usas hashes seguros (Bcrypt), usa: password_verify($password, $user['password'])
                    if (password_verify($password, $user['password']) || $password === $user['password']) {
                        $_SESSION['id_usuario'] = $user['id'];
                        $_SESSION['nombre_usuario'] = $user['nombre'];
                        $_SESSION['rol'] = 'usuario';
                        
                        header("Location: cliente.php");
                        exit();
                    }
                }
            } else {
                // LOGIN DE ADMINISTRADORES / PROFESIONALES (Tabla profesionales)
                $res = $db->query("SELECT * FROM profesionales WHERE username = ? AND activo = 1", $username)->fetchAll();
                
                if (!empty($res)) {
                    $admin = $res[0];
                    if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                        $_SESSION['id_admin'] = $admin['id'];
                        $_SESSION['nombre_admin'] = $admin['nombre'];
                        $_SESSION['rol'] = 'admin';
                        
                        header("Location: administrador.php"); // Tu futura página de administración
                        exit();
                    }
                }
            }
            
            // Si no entró en los IF anteriores, las credenciales están mal
            $mensaje_alerta = '
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error de acceso:</strong> Usuario o contraseña incorrectos.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

        } catch (Exception $e) {
            $mensaje_alerta = '
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error:</strong> Problemas de conexión con el servidor.
            </div>';
        }
    }
}

@include_once "parts/head.php";
@include_once "parts/nav.php";
?>

<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-2">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Plataforma Óptica</h1>
        <p class="lead mb-0 opacity-75">Ingresa a tu cuenta para gestionar tus compras o agendamientos.</p>
    </div>
</section>

<div class="container my-5 justify-content-center d-flex flex-column align-items-center">

    <div class="col-md-6 col-lg-5 w-100" style="max-width: 500px;">
        <?php echo $mensaje_alerta; ?>
    </div>

    <div class="card border-0 shadow-sm p-4 p-md-5 col-md-6 col-lg-5 w-100"
        style="border-radius: 20px; max-width: 500px; background-color: #fff;">

        <ul class="nav nav-pills nav-justified mb-4 p-1 bg-light" id="loginTabs" role="tablist"
            style="border-radius: 12px;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="user-tab" data-bs-toggle="tab"
                    data-bs-target="#loginFormContainer" type="button" role="tab" onclick="setLoginType('usuario')"
                    style="border-radius: 10px;">
                    <i class="bi bi-person me-1"></i> Cliente
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="admin-tab" data-bs-toggle="tab"
                    data-bs-target="#loginFormContainer" type="button" role="tab" onclick="setLoginType('admin')"
                    style="border-radius: 10px;">
                    <i class="bi bi-shield-lock me-1"></i> Especialista
                </button>
            </li>
        </ul>

        <h3 id="loginTitle" style="font-family: 'Playfair Display', serif;" class="fw-bold text-center mb-4 text-dark">
            Acceso Clientes</h3>

        <form action="" method="POST">
            <input type="hidden" name="tipo_login" id="tipo_login" value="usuario">

            <div class="mb-3">
                <label class="form-label fw-medium text-secondary">Nombre de Usuario</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"
                        style="border-radius: 8px 0 0 8px;"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" class="form-control form-control-lg fs-6 border-start-0"
                        style="border-radius: 0 8px 8px 0;" placeholder="ej: juan_perez" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-medium text-secondary mb-0">Contraseña</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"
                        style="border-radius: 8px 0 0 8px;"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" class="form-control form-control-lg fs-6 border-start-0"
                        style="border-radius: 0 8px 8px 0;" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-accent px-5 py-3 w-100 fw-bold fs-6 mb-3" style="border-radius: 8px;">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
            </button>

            <div class="text-center mt-2">
                <p class="text-muted small mb-0">¿No tienes una cuenta? <a href="registro.php"
                        class="text-decoration-none fw-semibold text-primary-custom">Regístrate aquí</a></p>
            </div>
        </form>
    </div>
</div>

<?php
@include_once "parts/footer.php";
?>

<script>
// Animación dinámica de títulos según la pestaña activa
function setLoginType(type) {
    document.getElementById('tipo_login').value = type;
    const title = document.getElementById('loginTitle');
    if (type === 'usuario') {
        title.textContent = 'Acceso Clientes';
    } else {
        title.textContent = 'Panel de Especialistas';
    }
}
</script>
</body>

</html>