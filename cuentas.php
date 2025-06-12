<?php
session_start();
include("conexion.php");

// Verifica que haya sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT * FROM CUENTA_BANCARIA 
        WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cuentas Activas</title>
</head>
<body>
    <h1>Cuentas Bancarias Activas</h1>
    <table border="1">
        <tr>
            <th>Número de Cuenta</th>
            <th>CBU</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Tipo de Cuenta</th>
        </tr>
        <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_numero_de_cuenta"]) ?></td>
             <td><?= htmlspecialchars($row["CUENTA_BANCARIA_cbu"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_saldo"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_estado"]) ?></td>
             <td><?= htmlspecialchars($row["CUENTA_BANCARIA_tipo_de_cuenta"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="menu.php">Volver al menú</a></p>
</body>
</html>