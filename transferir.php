<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Obtener la cuenta activa del usuario que transfiere
$sql_origen = "SELECT CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_saldo FROM CUENTA_BANCARIA 
               WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa' LIMIT 1";
$stmt_origen = $conexion->prepare($sql_origen);
$stmt_origen->bind_param("i", $usuario_id);
$stmt_origen->execute();
$res_origen = $stmt_origen->get_result();
$cuenta_origen = $res_origen->fetch_assoc();

if (!$cuenta_origen) {
    die("No tenés cuentas activas para transferir.");
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destino = trim($_POST["destino"]);
    $monto = floatval($_POST["monto"]);
    $motivo = !empty(trim($_POST["motivo"])) ? trim($_POST["motivo"]) : "Sin motivo especificado";
    $cuenta_origen_nro = $cuenta_origen["CUENTA_BANCARIA_numero_de_cuenta"];
    $saldo_origen = floatval($cuenta_origen["CUENTA_BANCARIA_saldo"]);

    // Buscar cuenta destino por número, CBU o alias
    $sql_dest = "SELECT CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_saldo, USUARIO_idUSUARIO 
                 FROM CUENTA_BANCARIA 
                 WHERE (CUENTA_BANCARIA_numero_de_cuenta = ? OR CUENTA_BANCARIA_cbu = ? OR CUENTA_BANCARIA_alias = ?)
                 AND CUENTA_BANCARIA_estado = 'Activa'
                 LIMIT 1";
    $stmt_dest = $conexion->prepare($sql_dest);
    $stmt_dest->bind_param("sss", $destino, $destino, $destino);
    $stmt_dest->execute();
    $res_dest = $stmt_dest->get_result();

    if ($res_dest->num_rows === 1) {
        $destino_data = $res_dest->fetch_assoc();
        $cuenta_destino_nro = $destino_data["CUENTA_BANCARIA_numero_de_cuenta"];
        $saldo_destino = floatval($destino_data["CUENTA_BANCARIA_saldo"]);
        $usuario_destino_id = $destino_data["USUARIO_idUSUARIO"];

        if ($cuenta_destino_nro == $cuenta_origen_nro) {
            $mensaje = "⚠️ No podés transferirte a tu misma cuenta.";
        } elseif ($monto <= 0 || $monto > $saldo_origen) {
            $mensaje = "⚠️ Monto inválido o saldo insuficiente.";
        } else {
            $conexion->begin_transaction();

            try {
                // Actualizar saldos
                $nuevo_saldo_origen = $saldo_origen - $monto;
                $nuevo_saldo_destino = $saldo_destino + $monto;

                $sql_update_origen = "UPDATE CUENTA_BANCARIA SET CUENTA_BANCARIA_saldo = ? 
                                      WHERE CUENTA_BANCARIA_numero_de_cuenta = ?";
                $stmt = $conexion->prepare($sql_update_origen);
                $stmt->bind_param("ds", $nuevo_saldo_origen, $cuenta_origen_nro);
                $stmt->execute();

                $sql_update_destino = "UPDATE CUENTA_BANCARIA SET CUENTA_BANCARIA_saldo = ? 
                                       WHERE CUENTA_BANCARIA_numero_de_cuenta = ?";
                $stmt = $conexion->prepare($sql_update_destino);
                $stmt->bind_param("ds", $nuevo_saldo_destino, $cuenta_destino_nro);
                $stmt->execute();

                // Registrar transacción
                $sql_transaccion = "INSERT INTO TRANSACCIONES (TRANSACCIONES_fecha_y_hora, TRANSACCIONES_monto, TRANSACCIONES_tipo_de_movimiento, 
                                                              TRANSACCIONES_descripcion, TRANSACCIONES_cuenta_origen, TRANSACCIONES_cuenta_destino, TRANSACCIONES_moneda)
                                    VALUES (NOW(), ?, 'Transferencia', ?, ?, ?, 'ARS')";
                $stmt = $conexion->prepare($sql_transaccion);
                $stmt->bind_param("dsss", $monto, $motivo, $cuenta_origen_nro, $cuenta_destino_nro);
                $stmt->execute();

                // Notificaciones
                $mensaje_origen = "Transferiste $$monto a la cuenta $cuenta_destino_nro.";
                $sql_notif_emisor = "INSERT INTO NOTIFICACIONES (USUARIO_idUSUARIO, NOTIFICACIONES_mensaje, NOTIFICACIONES_fecha_y_hora, NOTIFICACIONES_tipo_de_notificaciones, NOTIFICACIONES_estado)
                                     VALUES (?, ?, NOW(), 'Transferencia', 'No leído')";
                $stmt = $conexion->prepare($sql_notif_emisor);
                $stmt->bind_param("is", $usuario_id, $mensaje_origen);
                $stmt->execute();

                $mensaje_destino = "Recibiste $$monto de la cuenta $cuenta_origen_nro.";
                $sql_notif_receptor = "INSERT INTO NOTIFICACIONES (USUARIO_idUSUARIO, NOTIFICACIONES_mensaje, NOTIFICACIONES_fecha_y_hora, NOTIFICACIONES_tipo_de_notificaciones, NOTIFICACIONES_estado)
                                       VALUES (?, ?, NOW(), 'Transferencia', 'No leído')";
                $stmt = $conexion->prepare($sql_notif_receptor);
                $stmt->bind_param("is", $usuario_destino_id, $mensaje_destino);
                $stmt->execute();

                $conexion->commit();
                $mensaje = "✅ Transferencia realizada con éxito.";
            } catch (Exception $e) {
                $conexion->rollback();
                $mensaje = "❌ Error en la transferencia: " . $e->getMessage();
            }
        }
    } else {
        $mensaje = "⚠️ Cuenta destino no encontrada.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transferir Dinero</title>
</head>
<body>
    <h2>Transferir Dinero</h2>

    <?php if (!empty($mensaje)): ?>
        <p><strong><?= htmlspecialchars($mensaje) ?></strong></p>
    <?php endif; ?>

    <?php if ($cuenta_origen): ?>
        <form method="POST">
            <p><strong>Cuenta origen:</strong> <?= htmlspecialchars($cuenta_origen["CUENTA_BANCARIA_numero_de_cuenta"]) ?></p>
            <p><strong>Saldo disponible:</strong> $<?= htmlspecialchars(number_format($cuenta_origen["CUENTA_BANCARIA_saldo"], 2)) ?></p>

            <label for="destino">Cuenta destino (Número de cuenta, CBU o Alias):</label><br>
            <input type="text" id="destino" name="destino" required><br><br>

            <label for="monto">Monto a transferir:</label><br>
            <input type="number" id="monto" name="monto" step="0.01" min="0.01" required><br><br>

            <label for="motivo">Motivo (opcional):</label><br>
            <input type="text" id="motivo" name="motivo"><br><br>

            <button type="submit">Transferir</button>
        </form>
    <?php else: ?>
        <p>No se encontró una cuenta activa desde la cual realizar la transferencia.</p>
    <?php endif; ?>

    <p><a href="menu.php">Volver al menú</a></p>
</body>
</html>
