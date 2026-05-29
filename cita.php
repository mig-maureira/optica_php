<?php
// 1. Iniciar o verificar la sesión del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar controladores de base de datos y configuración
@include "api/db.php";
@include "api/config.php";

// Si el usuario inició sesión, tomamos sus datos; de lo contrario, asignamos un ID de pruebas
$id_usuario_sesion = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 1; 
$nombre_usuario_sesion = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : "Usuario Activo (Demo)"; 

$mensaje_alerta = ""; 

// =========================================================================
// 2. PROCESAR EL AGENDAMIENTO CUANDO SE ENVÍA EL FORMULARIO
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    $id_hora_seleccionada = isset($_POST['id_hora_disponible']) ? (int)$_POST['id_hora_disponible'] : 0;

    if ($id_hora_seleccionada > 0) {
        try {
            // A. Insertar el registro en la tabla `citas`
            $sql_cita = "INSERT INTO citas (id_hora_disponible, id_usuario, estado) VALUES (?, ?, 'activo')";
            $db->query($sql_cita, $id_hora_seleccionada, $id_usuario_sesion);

            // B. Actualizar el estado en `horas_disponibles` a 'no_disponible'
            $sql_update_hora = "UPDATE horas_disponibles SET estado = 'no_disponible' WHERE id = ?";
            $db->query($sql_update_hora, $id_hora_seleccionada);

            $mensaje_alerta = '
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>¡Cita Agendada con Éxito!</strong> Tu hora médica ha sido reservada correctamente. Te esperamos en nuestra sucursal.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } catch (Exception $e) {
            $mensaje_alerta = '
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error:</strong> Esta hora ya no se encuentra disponible o hubo un problema en los servidores.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    } else {
        $mensaje_alerta = '
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-circle-fill me-2"></i><strong>Selección Inválida:</strong> Por favor escoge un bloque de horario válido de la lista.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// =========================================================================
// 3. CONSULTAR LAS HORAS DISPONIBLES REALES DE LA BASE DE DATOS
// =========================================================================
$horas_disponibles_lista = [];
try {
    $sql_cargar_horas = "SELECT h.id, h.fecha, h.hora, p.nombre AS profesional_nombre 
                         FROM horas_disponibles h
                         INNER JOIN profesionales p ON h.id_profesional = p.id
                         WHERE h.estado = 'disponible' AND h.fecha >= CURDATE()
                         ORDER BY h.fecha ASC, h.hora ASC";
    $horas_disponibles_lista = $db->query($sql_cargar_horas)->fetchAll();
} catch (Exception $e) {
    $horas_disponibles_lista = [];
}

// Cargar cabeceras visuales de la estructura del sitio
@include_once "parts/head.php";
@include_once "parts/nav.php";
?>

<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-3">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Reserva de Horas</h1>
        <p class="lead mb-0 opacity-75">Selecciona el día y bloque horario de tu preferencia de forma inmediata.</p>
    </div>
</section>

<div class="container my-5 justify-content-center d-flex flex-column align-items-center">

    <div class="col-lg-8 w-100" style="max-width: 800px;">
        <?php echo $mensaje_alerta; ?>
    </div>

    <div class="card border-0 shadow-sm p-4 p-md-5 col-lg-8 w-100" style="border-radius: 20px; max-width: 800px;">
        <h3 style="font-family: 'Playfair Display', serif;" class="fw-bold text-center mb-4 text-dark">Información de la
            Cita</h3>

        <form action="" method="POST">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-medium text-secondary">Nombre del Paciente</label>
                    <input type="text" class="form-control form-control-lg fs-6"
                        style="border-radius: 8px; background-color: #f8f9fa;" disabled
                        value="<?php echo htmlspecialchars($nombre_usuario_sesion); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium text-secondary">Tipo de Atención</label>
                    <select name="tipo_atencion" class="form-select form-select-lg fs-6" style="border-radius: 8px;"
                        required>
                        <option value="Examen Visual de Rutina (Gratuito)">Examen Visual de Rutina (Gratuito)</option>
                        <option value="Consulta Médica con Oculista / Oftalmólogo">Consulta Médica con Oculista /
                            Oftalmólogo</option>
                        <option value="Evaluación y Adaptación de Lentes de Contacto">Evaluación y Adaptación de Lentes
                            de Contacto</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-medium text-secondary">Selecciona Bloque Horario y Especialista
                        disponible</label>
                    <select name="id_hora_disponible" class="form-select form-select-lg fs-6"
                        style="border-radius: 8px;" required>
                        <option value="" disabled selected>-- Elige un día y horario disponible --</option>

                        <?php if (!empty($horas_disponibles_lista) && count($horas_disponibles_lista) > 0): ?>
                        <?php foreach ($horas_disponibles_lista as $h): 
                                // Formatear fecha para el usuario de manera legible (ej: 28-05-2026)
                                $fecha_formateada = date("d-m-Y", strtotime($h['fecha']));
                                // Quitar los segundos finales a la hora (ej: 15:30:00 pasa a 15:30)
                                $hora_formateada = date("H:i", strtotime($h['hora']));
                            ?>
                        <option value="<?php echo $h['id']; ?>">
                            <?php echo "📅 {$fecha_formateada} — ⏰ {$hora_formateada} hrs — 👨‍⚕️ Prof: {$h['profesional_nombre']}"; ?>
                        </option>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <option value="" disabled>⚠️ No hay horas disponibles publicadas por el momento.</option>
                        <?php endif; ?>

                    </select>
                </div>

            </div>

            <div class="text-center mt-4 pt-2">
                <button type="submit" name="agendar_cita" class="btn btn-accent px-5 py-3 w-100 fw-bold fs-6"
                    style="border-radius: 8px;">
                    <i class="bi bi-calendar-check me-2"></i>Confirmar Agendamiento
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Pie de página de la estructura del sitio
@include_once "parts/footer.php";
?>