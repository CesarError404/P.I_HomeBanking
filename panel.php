<?php
session_start();

// Redirige si no está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Captura mensaje si viene por GET
$mensaje = $_GET['mensaje'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Principal - HomeBanking</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f1f5f9;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding-top: 60px;
    }
    .panel {
      background: white;
      border-radius: 16px;
      padding: 30px 40px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      max-width: 600px;
      width: 100%;
    }
    h1 {
      color: #1e293b;
      margin-bottom: 20px;
    }
    .mensaje {
      background-color: #dcfce7;
      color: #15803d;
      padding: 12px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-weight: 600;
    }
    .botones {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .botones a {
      text-decoration: none;
      padding: 12px 20px;
      background-color: #3b82f6;
      color: white;
      border-radius: 10px;
      font-weight: 600;
      transition: background-color 0.2s ease;
    }
    .botones a:hover {
      background-color: #2563eb;
    }
  </style>
</head>
<body>

<div class="panel">
  <h1>BALFOX</h1>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <div class="botones">
    <a href="cuentas.php">Mis Cuentas</a>
    <a href="transacciones.php">Transacciones</a>
    <a href="pagos_y_servicios.php">Pagar Servicios</a>
    <a href="menu.php">Volver al menu</a>
  </div>
</div>

</body>
</html>
