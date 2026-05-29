<?php
@include "api/db.php";
@include "api/config.php";

@include_once "parts/head.php";
@include_once "parts/nav.php";

?>

<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-3">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Servicios Integrales</h1>
        <p class="lead mb-0 opacity-75">Cuidado médico, diagnóstico preciso y las mejores alternativas estéticas
            para tus ojos.</p>
    </div>
</section>

<div class="container my-5 py-3">
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 p-4 text-center shadow-sm h-100" style="border-radius:16px;">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                    style="width:64px; height:64px; background:rgba(46,131,139,0.1); border-radius:16px; color:var(--primary-color);">
                    <i class="bi bi-eyeglasses fs-3"></i>
                </div>
                <h4 class="fw-bold mb-2">Venta de Armazones y Sol</h4>
                <p class="text-muted small">Colecciones internacionales seleccionadas minuciosamente bajo altos
                    estándares de ergonomía y moda vanguardista.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 p-4 text-center shadow-sm h-100" style="border-radius:16px;">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                    style="width:64px; height:64px; background:rgba(46,131,139,0.1); border-radius:16px; color:var(--primary-color);">
                    <i class="bi bi-shield-check fs-3"></i>
                </div>
                <h4 class="fw-bold mb-2">Contactología Avanzada</h4>
                <p class="text-muted small">Adaptación personalizada de lentes de contacto blandos, cosméticos y gas
                    permeables con la mejor hidratación.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 p-4 text-center shadow-sm h-100" style="border-radius:16px;">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                    style="width:64px; height:64px; background:rgba(46,131,139,0.1); border-radius:16px; color:var(--primary-color);">
                    <i class="bi bi-tools fs-3"></i>
                </div>
                <h4 class="fw-bold mb-2">Mantenimiento Gratis</h4>
                <p class="text-muted small">Ajustes de plaquetas, calibración de varillas y limpiezas ultrasónicas
                    profundas de por vida para tus compras.</p>
            </div>
        </div>
    </div>

    <div class="p-5 text-white shadow-sm"
        style="background: linear-gradient(135deg, #236369 0%, var(--primary-color) 100%); border-radius: 24px;">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-uppercase mb-2"
                    style="background-color: var(--accent-color); padding: 6px 16px; border-radius: 50px;">Área
                    Médica</span>
                <h2 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-3">Consulta con Especialistas
                    en Oftalmología</h2>
                <p class="mb-0 opacity-75 lead">¿Necesitas una receta médica o evaluar tu salud ocular de fondo?
                    Ofrecemos convenios y citas directas con oculistas certificados. Agenda hoy tu examen clínico
                    completo.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="cita.php" class="btn btn-accent btn-lg px-4 py-3 text-nowrap"><i
                        class="bi bi-calendar-check me-2"></i> Reservar Mi Hora</a>
            </div>
        </div>
    </div>
</div>
<?php
@include_once "parts/footer.php";
?>