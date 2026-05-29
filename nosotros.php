<?php
@include "api/db.php";
@include "api/config.php";

@include_once "parts/head.php";
@include_once "parts/nav.php";
?>

<section class="py-5 text-center text-white"
    style="background: linear-gradient(180deg, var(--primary-color) 0%, #236369 100%); margin-top: 80px;">
    <div class="container py-3">
        <h1 style="font-family: 'Playfair Display', serif;" class="fw-bold mb-2">Quiénes Somos</h1>
        <p class="lead mb-0 opacity-75">Comprometidos con la excelencia óptica y la salud de tu mirada desde hace 15
            años.</p>
    </div>
</section>

<div class="container my-5 py-4">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <p class="text-uppercase fw-bold mb-2" style="color:var(--accent-color); letter-spacing: 1px;">Nuestra
                Trayectoria</p>
            <h2 style="font-family: 'Playfair Display', serif;" class="fw-bold text-dark mb-4">Cuidamos cada detalle
                de tu salud visual</h2>
            <p class="text-muted" style="line-height: 1.8;">Nacimos con la clara convicción de revolucionar la
                atención óptica tradicional. En OptiVision no solo adquieres lentes de vanguardia tecnológica, sino
                también la experiencia de un equipo médico enfocado en la prevención y tratamiento integral.</p>
            <p class="text-muted" style="line-height: 1.8;">Creemos fervientemente que la salud visual de primer
                nivel debe ir acompañada de un servicio cercano, honesto y transparente.</p>
        </div>
        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-4 text-center shadow-sm" style="background:#fff; border-radius:16px;">
                        <h2 class="fw-bold" style="color:var(--primary-color);">15+</h2>
                        <small class="text-muted">Años Sanando Miradas</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 text-center shadow-sm" style="background:#fff; border-radius:16px;">
                        <h2 class="fw-bold" style="color:var(--primary-color);">5K+</h2>
                        <small class="text-muted">Pacientes Satisfechos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
@include_once "parts/hero.php";
@include_once "parts/footer.php";
?>