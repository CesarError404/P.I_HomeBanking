<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

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

                $sql_transaccion = "INSERT INTO TRANSACCIONES (TRANSACCIONES_fecha_y_hora, TRANSACCIONES_monto, TRANSACCIONES_tipo_de_movimiento, 
                                                              TRANSACCIONES_descripcion, TRANSACCIONES_cuenta_origen, TRANSACCIONES_cuenta_destino, TRANSACCIONES_moneda)
                                    VALUES (NOW(), ?, 'Transferencia', ?, ?, ?, 'ARS')";
                $stmt = $conexion->prepare($sql_transaccion);
                $stmt->bind_param("dsss", $monto, $motivo, $cuenta_origen_nro, $cuenta_destino_nro);
                $stmt->execute();

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
  <title>Transferencia - HomeBanking</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      background-color: #f0f2f5;
    }

    .sidebar {
      width: 240px;
      height: 100vh;
      background-color: #0c1c3d;
      color: white;
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
      animation: slideInLeft 0.6s ease-out;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
      animation: fadeInDown 1s ease;
    }

    .logo {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.1) rotate(3deg);
    }

    .logo-container h2 {
      margin-top: 10px;
      font-size: 22px;
      color: white;
    }

    ul {
      list-style: none;
      padding: 0;
      width: 100%;
    }

    ul li {
      margin: 15px 0;
    }

    ul li a {
      text-decoration: none;
      color: white;
      display: flex;
      align-items: center;
      padding: 10px;
      border-radius: 8px;
      transition: background-color 0.3s;
    }

    ul li a:hover {
      background-color: #1e335c;
      transform: scale(1.03);
    }

    ul li a i {
      margin-right: 10px;
    }

    .contenido {
      flex: 1;
      padding: 40px;
      animation: fadeIn 0.8s ease-in-out;
    }

    h1 {
      color: #0c1c3d;
    }

    form {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      max-width: 600px;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 20px;
      margin-bottom: 6px;
    }

    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      margin-top: 30px;
      padding: 12px 20px;
      background-color: #0c1c3d;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #1e335c;
    }

    .mensaje {
      margin-bottom: 20px;
      padding: 12px;
      background-color: #e7f4ff;
      border-left: 6px solid #0c1c3d;
      border-radius: 6px;
      font-weight: 500;
    }

    @keyframes slideInLeft {
      from { transform: translateX(-100px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo-container">
      <img src="logo.png" alt="Logo" class="logo">
      <h2>HomeBanking</h2>
    </div>
    <ul>
      <li><a href="menu.php"><i class="fas fa-home"></i> Menú Principal</a></li>
      <li><a href="ingresar_dinero.php"><i class="fas fa-money-bill-wave"></i> Ingresar Dinero</a></li>
      <li><a href="transferir.php"><i class="fas fa-exchange-alt"></i> Transferir</a></li>
      <li><a href="pagos_y_servicios.php"><i class="fas fa-file-invoice"></i> Pagos y Servicios</a></li>
      <li><a href="notificaciones.php"><i class="fas fa-bell"></i> Notificaciones</a></li>
    </ul>
  </div>

  <div class="contenido">
    <h1>Transferencia de Dinero</h1>
    <?php if (!empty($mensaje)): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($cuenta_origen): ?>
      <form method="POST">
        <p><strong>Cuenta origen:</strong> <?= htmlspecialchars($cuenta_origen["CUENTA_BANCARIA_numero_de_cuenta"]) ?></p>
        <p><strong>Saldo disponible:</strong> $<?= number_format($cuenta_origen["CUENTA_BANCARIA_saldo"], 2) ?></p>

        <label for="destino">Cuenta destino (Número, CBU o Alias):</label>
        <input type="text" name="destino" id="destino" required>

        <label for="monto">Monto a transferir:</label>
        <input type="number" name="monto" id="monto" step="0.01" min="0.01" required>

        <label for="motivo">Motivo (opcional):</label>
        <input type="text" name="motivo" id="motivo">

        <button type="submit">Transferir</button>
      </form>
    <?php else: ?>
      <p>No se encontró una cuenta activa desde la cual realizar la transferencia.</p>
    <?php endif; ?>
  </div>
</body>
</html>