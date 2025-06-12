<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Obtener cuentas activas del usuario
$sql = "SELECT idCUENTA_BANCARIA, CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_saldo FROM CUENTA_BANCARIA 
        WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado_cuentas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos y Servicios</title>
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

        h2 {
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

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="submit"] {
            margin-top: 30px;
            background-color: #0c1c3d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #1e335c;
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
        <h2>Pagar un servicio</h2>
        <form method="POST" action="procesar_pago.php">
            <label for="tipo_servicio">Tipo de servicio:</label>
            <input type="text" name="tipo_servicio" id="tipo_servicio" required>

            <label for="monto">Monto a pagar:</label>
            <input type="number" step="0.01" name="monto" id="monto" required>

            <label for="cuenta_id">Seleccionar cuenta:</label>
            <select name="cuenta_id" id="cuenta_id" required>
                <?php while ($cuenta = $resultado_cuentas->fetch_assoc()) { ?>
                    <option value="<?= $cuenta['idCUENTA_BANCARIA']; ?>">
                        <?= $cuenta['CUENTA_BANCARIA_numero_de_cuenta']; ?> - Saldo: $<?= number_format($cuenta['CUENTA_BANCARIA_saldo'], 2); ?>
                    </option>
                <?php } ?>
            </select>

            <input type="submit" value="Generar factura y pagar">
        </form>
    </div>
</body>
</html>
