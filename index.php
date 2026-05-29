<?php
@include "api/db.php";
@include "api/config.php";

@include_once "parts/head.php";
@include_once "parts/nav.php";
@include_once "parts/hero.php";
@include_once "parts/productos_lista.php";
?>
<section id="servicios" class="services-section">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label" style="color: var(--accent-color);">Servicios</p>
            <h2 class="section-title text-white">Nuestros Servicios</h2>
            <p class="section-description text-white-50">
                Ofrecemos soluciones integrales para el cuidado de tu visión.
            </p>
        </div>

        <div class="row g-4">
            <!-- Service 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h5 class="service-title">Examen Visual</h5>
                    <p class="service-description">
                        Evaluación completa de tu salud visual con tecnología de última generación.
                    </p>
                </div>
            </div>

            <!-- Service 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-eyeglasses"></i>
                    </div>
                    <h5 class="service-title">Lentes Graduados</h5>
                    <p class="service-description">
                        Amplia variedad de armazones y lentes con la graduación que necesitas.
                    </p>
                </div>
            </div>

            <!-- Service 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h5 class="service-title">Ajustes y Reparaciones</h5>
                    <p class="service-description">
                        Servicio técnico especializado para mantener tus lentes en perfecto estado.
                    </p>
                </div>
            </div>

            <!-- Service 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="service-title">Lentes de Contacto</h5>
                    <p class="service-description">
                        Asesoría y venta de lentes de contacto de las mejores marcas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Promo Section -->
<section id="nosotros" class="promo-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="promo-image-wrapper">
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&h=600&fit=crop"
                            alt="Servicio profesional">
                    </div>
                    <div class="promo-floating-card">
                        <div class="icon-circle">
                            <span>15</span>
                        </div>
                        <div>
                            <p class="fw-semibold mb-0" style="color: var(--text-dark);">Años de</p>
                            <p class="mb-0" style="color: var(--text-muted);">Experiencia</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="promo-content">
                    <p class="section-label">¿Por qué elegirnos?</p>
                    <h2 class="section-title">Cuidamos Tu Visión Con Pasión</h2>
                    <p class="mb-4" style="color: var(--text-muted); line-height: 1.8;">
                        En OptiVision combinamos la más alta tecnología con atención
                        personalizada para brindarte la mejor experiencia en salud visual.
                        Nuestro equipo de profesionales está comprometido con tu bienestar.
                    </p>

                    <div class="mb-4">
                        <div class="benefit-item">
                            <div class="icon-wrapper">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span>Examen visual completo gratuito</span>
                        </div>
                        <div class="benefit-item">
                            <div class="icon-wrapper">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span>Garantía de 2 años en todos los productos</span>
                        </div>
                        <div class="benefit-item">
                            <div class="icon-wrapper">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span>Ajustes y mantenimiento sin costo</span>
                        </div>
                        <div class="benefit-item">
                            <div class="icon-wrapper">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span>Envío gratis en compras mayores a $500</span>
                        </div>
                    </div>

                    <a href="#" class="btn btn-accent d-inline-flex align-items-center gap-2">
                        Conoce Más Sobre Nosotros
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>



<?php
@include_once "parts/footer.php";
?>