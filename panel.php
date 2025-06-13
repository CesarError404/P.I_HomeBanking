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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
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

        .panel {
            background: white;
            border-radius: 16px;
            padding: 30px 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            max-width: 700px;
        }

        h1 {
            color: #0c1c3d;
            margin-bottom: 20px;
        }

        .mensaje {
            background-color: #dcfce7;
            color: #15803d;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            animation: fadeIn 1s ease-in-out;
        }

        .botones {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .botones a {
            text-decoration: none;
            padding: 14px 24px;
            background-color: #3b82f6;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
        }

        .botones a:hover {
            background-color: #2563eb;
            transform: scale(1.05);
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
        <li><a href="pagos_y_servicios.php"><i class="fas fa-file-invoice"></i> Pagar Servicios</a></li>
        <li><a href="notificaciones.php"><i class="fas fa-bell"></i> Notificaciones</a></li>
    </ul>
</div>

<div class="contenido">
    <div class="panel">
      <h2><i class="fa-solid fa-receipt"></i> SERVICIOS</h2>
      
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="botones">
            <a href="menu.php"><i class="fas fa-arrow-left"></i> Volver al Menú</a>
        </div>
    </div>
</div>

</body>
</html>
