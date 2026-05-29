<?php
// 1. Iniciar la sesión (obligatorio para el carrito)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir las conexiones para que la variable $db exista
@include "api/db.php";
@include "api/config.php";

// 3. Capturar datos por POST (para formularios) o por GET (enlaces rápidos) de manera segura
$id_producto = isset($_REQUEST['id_producto']) ? (int)$_REQUEST['id_producto'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$cantidad_a_agregar = isset($_REQUEST['cantidad']) ? (int)$_REQUEST['cantidad'] : 1;

if ($id_producto > 0 && $cantidad_a_agregar > 0) {
    try {
        // Buscamos si el producto existe y tiene existencias
        $resultado = $db->query("SELECT * FROM productos WHERE id = ? AND stock > 0", $id_producto)->fetchAll();

        // Si el arreglo no está vacío, significa que el producto es válido
        if (!empty($resultado)) {
            $prod = $resultado[0]; 

            // Si el producto ya existe en el carrito, le SUMAMOS la cantidad solicitada
            if (isset($_SESSION['carrito'][$id_producto])) {
                $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad_a_agregar;
            } else {
                // Si es un producto nuevo en el carro, lo registramos con la estructura completa que espera carrito.php
                $_SESSION['carrito'][$id_producto] = [
                    'titulo'   => $prod['titulo'],
                    'precio'   => $prod['precio'],
                    'imagen'   => !empty($prod['imagen_url']) ? $prod['imagen_url'] : 'img/productos/default.jpg',
                    'cantidad' => $cantidad_a_agregar
                ];
            }
        }
    } catch (Exception $e) {
        // En caso de error, volvemos limpiamente a la tienda
        header("Location: ../index.php?error=bd");
        exit;
    }
}

// 4. Redirigir de inmediato al carrito para ver el artículo reflejado
header("Location: carrito.php");
exit;