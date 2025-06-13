<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT USUARIO_nombre, USUARIO_apellido FROM USUARIO WHERE idUSUARIO = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$sql_cuenta = "SELECT CUENTA_BANCARIA_numero_de_cuenta FROM CUENTA_BANCARIA WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa' LIMIT 1";
$stmt_cuenta = $conexion->prepare($sql_cuenta);
$stmt_cuenta->bind_param("i", $usuario_id);
$stmt_cuenta->execute();
$res_cuenta = $stmt_cuenta->get_result();
$cuenta = $res_cuenta->fetch_assoc();

$transacciones = [];

if ($cuenta) {
    $nro_cuenta = $cuenta["CUENTA_BANCARIA_numero_de_cuenta"];

    $sql = "SELECT TRANSACCIONES_fecha_y_hora, TRANSACCIONES_monto, TRANSACCIONES_tipo_de_movimiento, 
                   TRANSACCIONES_descripcion, TRANSACCIONES_moneda
            FROM TRANSACCIONES
            WHERE TRANSACCIONES_cuenta_origen = ? OR TRANSACCIONES_cuenta_destino = ?
            ORDER BY TRANSACCIONES_fecha_y_hora DESC LIMIT 10";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $nro_cuenta, $nro_cuenta);
    $stmt->execute();
    $transacciones = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>BALKFOX - Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-direction: row;
      background-color: #f0f2f5;
    }

    .sidebar {
      width: 240px;
      background-color: #0c1c3d;
      color: white;
      padding: 20px;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
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
      font-weight: bold;
      color: #ffffff;
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
      font-size: 18px;
    }

    .contenido {
      flex: 1;
      padding: 40px;
      min-width: 0;
      box-sizing: border-box;
    }

    h1 {
      font-size: 26px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #0c1c3d;
      color: white;
    }

    tr:hover {
      background-color: #f0f0f0;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
        height: auto;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        padding: 10px;
      }

      .logo-container {
        display: none;
      }

      .sidebar ul {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 0;
        margin: 0;
      }

      .sidebar ul li {
        margin: 5px;
      }

      .sidebar ul li a {
        padding: 8px 12px;
        font-size: 14px;
      }

      .contenido {
        padding: 20px;
      }

      table, th, td {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo-container">
      <img src="logo.png" alt="Logo Balkfox" class="logo">
      <h2>BALKFOX</h2>
    </div>
    <ul>
      <li><a href="ingresar_dinero.php"><i class="fa-solid fa-wallet"></i> Ingresar Dinero</a></li>
      <li><a href="transferir.php"><i class="fa-solid fa-right-left"></i> Transferir</a></li>
      <li><a href="pagos_y_servicios.php"><i class="fa-solid fa-file-invoice-dollar"></i> Pagos de servicios</a></li>
      <li><a href="prestamos.php"><i class="fa-solid fa-hand-holding-dollar"></i> Préstamos</a></li>
      <li><a href="tarjetas.php"><i class="fa-solid fa-credit-card"></i> Tarjetas</a></li>
      <li><a href="notificaciones.php"><i class="fa-solid fa-bell"></i> Notificaciones</a></li>
      <li><a href="ultimo_acceso.php"><i class="fa-solid fa-clock-rotate-left"></i> Último acceso</a></li>
      <li><a href="persona.php"><i class="fa-solid fa-user"></i> Mis Datos</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-door-open"></i> Cerrar sesión</a></li>
    </ul>
  </div>

  <div class="contenido">
    <h1>Bienvenido <?= htmlspecialchars($usuario["USUARIO_nombre"] ?? '') ?></h1>
    <p>Seleccioná una opción del menú para comenzar.</p>

    <?php if ($cuenta): ?>
      <?php
        $sql_info = "SELECT CUENTA_BANCARIA_cbu, CUENTA_BANCARIA_saldo, CUENTA_BANCARIA_estado, CUENTA_BANCARIA_tipo_de_cuenta 
                     FROM CUENTA_BANCARIA 
                     WHERE CUENTA_BANCARIA_numero_de_cuenta = ?";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->bind_param("i", $cuenta["CUENTA_BANCARIA_numero_de_cuenta"]);
        $stmt_info->execute();
        $res_info = $stmt_info->get_result();
        $info = $res_info->fetch_assoc();
      ?>
      <div style="
          background-color: white;
          padding: 20px;
          margin: 30px 0;
          border-radius: 12px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          max-width: 600px;
          border-left: 5px solid #0c1c3d;
      ">
        <h2 style="margin-top: 0; color: #0c1c3d;"><i class="fas fa-id-card"></i> Tu cuenta</h2>
        <p><i class="fas fa-hashtag"></i> <strong>Número de Cuenta:</strong> <?= htmlspecialchars($cuenta["CUENTA_BANCARIA_numero_de_cuenta"]) ?></p>
        <?php if ($info): ?>
          <p><i class="fas fa-barcode"></i> <strong>CBU:</strong> <?= htmlspecialchars($info["CUENTA_BANCARIA_cbu"]) ?></p>
          <p><i class="fas fa-wallet"></i> <strong>Saldo:</strong> $<?= number_format($info["CUENTA_BANCARIA_saldo"], 2) ?></p>
          <p><i class="fas fa-toggle-on"></i> <strong>Estado:</strong> <?= htmlspecialchars($info["CUENTA_BANCARIA_estado"]) ?></p>
          <p><i class="fas fa-layer-group"></i> <strong>Tipo de Cuenta:</strong> <?= htmlspecialchars($info["CUENTA_BANCARIA_tipo_de_cuenta"]) ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h2>Transacciones recientes</h2>
    <?php if ($cuenta && $transacciones->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Fecha y hora</th>
            <th>Movimiento</th>
            <th>Monto</th>
            <th>Moneda</th>
            <th>Descripción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($fila = $transacciones->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($fila['TRANSACCIONES_fecha_y_hora']) ?></td>
              <td><?= htmlspecialchars($fila['TRANSACCIONES_tipo_de_movimiento']) ?></td>
              <td>$<?= number_format($fila['TRANSACCIONES_monto'], 2) ?></td>
              <td><?= htmlspecialchars($fila['TRANSACCIONES_moneda']) ?></td>
              <td><?= htmlspecialchars($fila['TRANSACCIONES_descripcion']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php elseif (!$cuenta): ?>
      <p>No tenés cuentas activas.</p>
    <?php else: ?>
      <p>No hay transacciones registradas.</p>
    <?php endif; ?>
  </div>
</body>
</html>
