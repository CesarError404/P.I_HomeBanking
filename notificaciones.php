<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT idNOTIFICACIONES, NOTIFICACIONES_mensaje, NOTIFICACIONES_fecha_y_hora, NOTIFICACIONES_tipo_de_notificaciones, NOTIFICACIONES_estado 
        FROM NOTIFICACIONES WHERE USUARIO_idUSUARIO = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notificaciones</title>
</head>
<body>
    <h2>Mis Notificaciones</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Mensaje</th>
            <th>Fecha y Hora</th>
            <th>Tipo</th>
            <th>Estado</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $fila['idNOTIFICACIONES']; ?></td>
                <td><?php echo $fila['NOTIFICACIONES_mensaje']; ?></td>
                <td><?php echo $fila['NOTIFICACIONES_fecha_y_hora']; ?></td>
                <td><?php echo $fila['NOTIFICACIONES_tipo_de_notificaciones']; ?></td>
                <td><?php echo $fila['NOTIFICACIONES_estado']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <br>
    <a href="menu.php">Volver al menuº</a>
</body>
</html>