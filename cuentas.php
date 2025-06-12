<?php
session_start();
include("conexion.php");

// Verifica que haya sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Primero chequeamos si el usuario tiene cuentas activas
$sql = "SELECT * FROM CUENTA_BANCARIA 
        WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Si no tiene cuentas activas, generamos una automáticamente
if ($resultado->num_rows === 0) {
    // Generar número de cuenta: 10 dígitos numéricos aleatorios
    $numero_cuenta = strval(rand(1000000000, 9999999999));

    // Generar CBU: 22 dígitos numéricos aleatorios
    $cbu = '';
    for ($i = 0; $i < 22; $i++) {
        $cbu .= rand(0, 9);
    }

    // Generar alias: "usuario" + usuario_id + 4 números aleatorios
    $alias = "usuario" . $usuario_id . rand(1000, 9999);

    // Tipo de cuenta por defecto
    $tipo_cuenta = "Caja de Ahorro";

    // Saldo inicial 0
    $saldo = 0.00;

    // Estado activo
    $estado = "Activa";

    // Insertar la cuenta en la base
    $sql_insert = "INSERT INTO CUENTA_BANCARIA 
        (USUARIO_idUSUARIO, CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_cbu, CUENTA_BANCARIA_alias, CUENTA_BANCARIA_tipo_de_cuenta, CUENTA_BANCARIA_saldo, CUENTA_BANCARIA_estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conexion->prepare($sql_insert);
    $stmt_insert->bind_param("issssid", $usuario_id, $numero_cuenta, $cbu, $alias, $tipo_cuenta, $saldo, $estado);

    if ($stmt_insert->execute()) {
        // Ejecutado correctamente, ahora consultamos de nuevo para mostrar
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        echo "Error al crear la cuenta automática: " . $conexion->error;
        exit();
    }
    $stmt_insert->close();
}
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
            <th>Alias</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Tipo de Cuenta</th>
        </tr>
        <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_numero_de_cuenta"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_cbu"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_alias"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_saldo"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_estado"]) ?></td>
            <td><?= htmlspecialchars($row["CUENTA_BANCARIA_tipo_de_cuenta"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="menu.php">Volver al menú</a></p>
</body>
</html>
