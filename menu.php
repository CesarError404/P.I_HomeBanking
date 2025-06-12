<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Obtener nombre del usuario (opcional)
$sql = "SELECT USUARIO_nombre, USUARIO_apellido FROM USUARIO WHERE idUSUARIO = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Obtener cuenta activa del usuario
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
  <title>BALFOX</title>
  <link rel="stylesheet" href="estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 10px;
      text-align: left;
    }
    .contenido {
      padding: 20px;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>BALFOX</h2>
    <ul>
        <li><a href="cuentas.php">Cuentas bancarias</a></li>
        <li><a href="transferir.php">Transferir</a></li>
        <li><a href="pagos_y_servicios.php">Pagos de servicios</a></li>
        <li><a href="prestamos.php">Préstamos</a></li>
        <li><a href="tarjetas.php">Tarjetas</a></li>
        <li><a href="notificaciones.php">Notificaciones</a></li>
        <li><a href="ultimo_acceso.php">Último acceso al sistema</a></li>
        <li><a href="persona.php">Mis Datos Personales</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
    </ul>
  </div>

  <div class="contenido">
    <h1>Bienvenido a tu banca digital</h1>
    <p>Selecciona una opción del menú para comenzar.</p>

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