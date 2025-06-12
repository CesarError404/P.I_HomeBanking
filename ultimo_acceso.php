<?php
session_start();
include("conexion.php");

$sql = "SELECT U.USUARIO_nombre, U.USUARIO_apellido, L.LOGIN_fecha_y_hora_de_acceso
        FROM LOGIN L
        JOIN USUARIO U ON L.LOGIN_idUsuario = U.idUSUARIO
        ORDER BY L.LOGIN_fecha_y_hora_de_acceso DESC
        LIMIT 1";

$resultado = $conexion->query($sql);
$acceso = $resultado->fetch_assoc();
?>

<h2>Último acceso al sistema</h2>
<p><?php echo $acceso["USUARIO_nombre"] . " " . $acceso["USUARIO_apellido"]; ?> – <?php echo $acceso["LOGIN_fecha_y_hora_de_acceso"]; ?></p>
