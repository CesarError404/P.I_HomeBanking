<?php
session_start();

// Si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>
