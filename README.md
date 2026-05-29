# optica_php

Sitio optica donde se pueden agendar horas y administrar productos

# 🕶️ Sistema de Gestión de Óptica y Carrito de Compras

Esta una aplicación web integral para la gestión de una óptica moderna. El sistema combina una plataforma de cara al cliente (E-commerce, catálogo dinámico de lentes ópticos/de sol, agendamiento de citas médicas) con un potente panel de administración para el control de inventario, stock, ventas mensuales y horarios de profesionales de la salud visual.

Desarrollado bajo la realidad de salud local, incluye integración de flujos de previsión chilenos (Fonasa, Isapre, Particular) y validación estructural de identidad (RUT).

---

## 🚀 Características Principales

### 🛒 Área de Clientes (E-commerce & Servicios)

- **Catálogo Dinámico:** Despliegue automatizado de lentes clasificados por marca, precio, stock físico y etiquetas promocionales (_Nuevo_, _Oferta_).
- **Páginas de Detalle:** Vista en profundidad de cada producto con selector dinámico de cantidades limitado al stock real disponible en bodega.
- **Carrito de Compras Persistente:** Módulo que permite añadir productos (desde catálogo o detalle), acumular cantidades, descontar artículos de uno en uno, vaciar el carro y calcular totales en tiempo real basados en la base de datos.
- **Procesamiento de Órdenes:** Persistencia nativa de la compra que genera registros relacionales automáticos en las tablas de `pedidos` y `pedido_detalles`, actualizando/restando el stock físico de forma inmediata.
- **Agendamiento de Citas:** Gestión fluida de reserva de horas médicas para exámenes visuales automatizados.
- **Panel de Usuario (`cliente.php`):** Perfil personalizado donde el cliente visualiza sus datos de previsión médica y un historial detallado de pedidos con estados (_pendiente_, _pagado_, _enviado_, etc.).

### 🔐 Área Administrativa (`administrador.php` & `control_citas.php`)

- **Métricas de Control Financiero:** Resumen automático de las ventas totales del mes en curso (excluyendo transacciones canceladas).
- **Panel de Control Operativo:** Visualización en tiempo real de las citas médicas agendadas para el día actual y próximos días.
- **Gestor Avanzado de Horas Médicas:** Generador por lotes de bloques de atención médica parametrizables (definición de rango de fechas, intervalos de tiempo de 30 minutos y horarios de inicio/cierre de jornadas).
- **Control de Acceso Estricto:** Filtros de seguridad mediante sesiones PHP que impiden que usuarios comunes o invitados externos intercepten los paneles de administración.

---

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+ / 8.x (Arquitectura limpia con abstracción de Base de Datos orientada a objetos a través de una clase `$db` personalizada basada en PDO).
- **Frontend:** HTML5, CSS3, JavaScript nativo.
- **Diseño y Estilos:** Bootstrap 5.3.3, Bootstrap Icons y fuentes tipográficas premium vía Google Fonts (`Playfair Display` y `Outfit`).
- **Base de Datos:** MySQL / MariaDB (Estructura relacional e indexada con soporte para transacciones seguras e integridad referencial `FOREIGN KEY`).

---

## 📂 Estructura del Proyecto

```text
├── api/
│   ├── db.php             # Clase controladora de la conexión PDO y consultas preparadas
│   ├── config.php         # Parámetros globales de configuración del sistema
│   └── carrito_agregar.php# Procesador unificado de entradas y lógicas al carro (GET/POST)
├── parts/
│   ├── head.php           # Cabecera HTML común (Meta, estilos y links globales)
│   ├── nav.php            # Barra de navegación con contador de carrito reactivo
│   ├── footer.php         # Pie de página unificado y cierre de etiquetas
│   └── productos_lista.php# Componente renderizador del grid de productos
├── administrador.php      # Dashboard general del administrador
├── control_citas.php      # Panel administrativo de gestión y creación de agendas médicas
├── cliente.php            # Portal privado del usuario / historial de compras
├── carrito.php            # Interfaz del carrito de compras y procesador de pedidos
├── detalle.php            # Ficha técnica e individual de cada artículo
├── index.php              # Landing page principal y catálogo unificado
└── optica.sql             # Script estructural y poblador de la base de datos
```

Instalación y Configuración Local

Sigue estos pasos para clonar y ejecutar el proyecto en tu entorno de desarrollo local (ej: XAMPP, Laragon, MAMP):

1. Clonar el Repositorio

```Bash

git clone [https://github.com/mig-maureira/optica_php.git](https://github.com/mig-maureira/optica_php.git)
cd optica_php
```

2. Importar la Base de Datos

   Abre tu gestor de base de datos preferido (phpMyAdmin, DBeaver, HeidiSQL, etc.).

   Crea una nueva base de datos llamada optica.

   Importa el archivo optica.sql ubicado en la raíz del proyecto para generar la estructura de tablas y los datos de prueba iniciales (previsiones, usuarios, productos, profesionales).

3. Configurar la Conexión

Abre el archivo api/config.php (o el archivo de conexión correspondiente) y edita las credenciales de acceso a tu servidor MySQL:
PHP

```Bash
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'optica');
```

4. Ejecutar el Proyecto

Mueve la carpeta del proyecto al directorio raíz de tu servidor local (htdocs en XAMPP o www en Laragon) y accede desde tu navegador web a través de:
Plaintext

http://localhost/optica_php/index.php

👥 Cuentas de Prueba (Seeders Incluidos)

La base de datos optica.sql ya incluye registros de prueba preestablecidos para agilizar la evaluación del sistema:

    Usuario Cliente: Puedes iniciar sesión con los clientes creados en la tabla usuarios.

    Usuario Administrador: El inicio de sesión administrativo activará el token en $_SESSION['id_admin'] para dar acceso total a las vistas administrador.php y control_citas.php.

    Lógica de Negocio: Las compras simulan un ID de usuario fijo (id = 1) en caso de no detectar una sesión abierta, permitiendo probar el flujo completo del carrito de inmediato.

🛡️ Seguridad Implementada

    Prevención de Inyecciones SQL: Todas las consultas a la base de datos se ejecutan de forma segura utilizando sentencias preparadas nativas mediante el paso de argumentos dinámicos en la clase $db->query().

    Filtros Cross-Site Scripting (XSS): Los datos dinámicos renderizados en pantalla que provienen del usuario o la base de datos están sanitizados mediante htmlspecialchars().

    Control de Estado de Cabeceras: Las acciones del carrito y de compras implementan redirecciones limpias con header("Location: ...") seguidas de exit(); para prevenir loops de recarga en blanco y dobles envíos de formularios.
