<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$saldo_actual = null;
$usuario_id = $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero_cuenta = $_POST["numero_cuenta"];
    $monto = floatval($_POST["monto"]);
    $moneda = $_POST["moneda"];

    $sql = "SELECT * FROM CUENTA_BANCARIA WHERE CUENTA_BANCARIA_numero_de_cuenta = ? AND USUARIO_idUSUARIO = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $numero_cuenta, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $cuenta = $resultado->fetch_assoc();
        $saldo_actual = $cuenta["CUENTA_BANCARIA_saldo"];
        $nuevo_saldo = $saldo_actual + $monto;

        $update = $conexion->prepare("UPDATE CUENTA_BANCARIA SET CUENTA_BANCARIA_saldo = ? WHERE CUENTA_BANCARIA_numero_de_cuenta = ?");
        $update->bind_param("di", $nuevo_saldo, $numero_cuenta);
        $update->execute();

        $descripcion = "Ingreso por cajero automático";
        $insert = $conexion->prepare("INSERT INTO TRANSACCIONES (
            TRANSACCIONES_cuenta_destino,
            TRANSACCIONES_monto,
            TRANSACCIONES_tipo_de_movimiento,
            TRANSACCIONES_descripcion,
            TRANSACCIONES_moneda,
            TRANSACCIONES_fecha_y_hora
        ) VALUES (?, ?, 'Ingreso', ?, ?, NOW())");
        $insert->bind_param("idss", $numero_cuenta, $monto, $descripcion, $moneda);
        $insert->execute();

        $mensaje = "✅ Se ingresaron $monto $moneda correctamente.";
    } else {
        $mensaje = "❌ Número de cuenta inválido o no pertenece al usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Dinero - BALKFOX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            display: flex;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background-color: #0c1c3d;
            color: white;
            padding: 20px;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
            animation: fadeInLeft 1s ease forwards;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }

        .logo-container h2 {
            margin-top: 10px;
            font-size: 22px;
            color: #fff;
            animation: fadeIn 1.2s ease;
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
            transition: background-color 0.3s, transform 0.2s;
        }

        ul li a:hover {
            background-color: #1e335c;
            transform: scale(1.05);
        }

        ul li a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .contenido {
            flex: 1;
            padding: 50px;
        }

        .formulario {
            background-color: white;
            max-width: 550px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            border-left: 5px solid #0c1c3d;
            animation: bounceIn 0.8s ease;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }
            60% {
                opacity: 1;
                transform: scale(1.02);
            }
            100% {
                transform: scale(1);
            }
        }

        h2 {
            margin-top: 0;
            color: #0c1c3d;
            font-size: 24px;
            animation: fadeIn 1.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }

        input[type="number"],
        select,
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input[type="submit"] {
            background-color: #0c1c3d;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #1e335c;
            transform: scale(1.03);
        }

        input[type="number"]:focus,
        select:focus {
            border-color: #0c1c3d;
            box-shadow: 0 0 5px rgba(12, 28, 61, 0.5);
        }

        .mensaje {
            padding: 12px;
            background-color: #e9f2ff;
            border-left: 5px solid #0c1c3d;
            color: #0c1c3d;
            font-weight: bold;
            border-radius: 6px;
            margin-top: 20px;
            animation: fadeIn 0.6s ease-in;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="logo.png" alt="Logo" class="logo">
        <h2>BALKFOX</h2>
    </div>
    <ul>
        <li><a href="menu.php"><i class="fa-solid fa-house"></i> Menú Principal</a></li>
        <li><a href="transferir.php"><i class="fa-solid fa-right-left"></i> Transferir</a></li>
        <li><a href="pagos_y_servicios.php"><i class="fa-solid fa-file-invoice-dollar"></i> Pagos de servicios</a></li>
    </ul>
</div>

<div class="contenido">
    <div class="formulario">
        <h2><i class="fa-solid fa-coins"></i> Ingreso de Dinero</h2>
        <form method="POST">
            <label>Número de cuenta:</label>
            <input type="number" name="numero_cuenta" required>

            <label>Moneda:</label>
            <select name="moneda" required>
                <option value="ARS">ARS - Pesos Argentinos</option>
                <option value="USD">USD - Dólares</option>
                <option value="EUR">EUR - Euros</option>
            </select>

            <label>Monto a ingresar:</label>
            <input type="number" step="0.01" name="monto" required>

            <input type="submit" value="Confirmar Ingreso">
        </form>

        <?php if ($mensaje): ?>
            <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
