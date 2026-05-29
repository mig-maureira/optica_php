<?php
session_start();

$vacio = false;

if (empty($_SESSION['idUsuario']) || !is_numeric($_SESSION['idUsuario'])) {
    $vacio = true;
}

if ($vacio) {
    header("Location: login.php");
    exit();
}
?>
