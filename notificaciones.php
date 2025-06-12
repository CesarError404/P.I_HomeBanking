<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT idNOTIFICACIONES, NOTIFICACIONES_mensaje, NOTIFICACIONES_fecha_y_hora, NOTIFICACIONES_tipo_de_notificaciones, NOTIFICACIONES_estado 
        FROM NOTIFICACIONES WHERE USUARIO_idUSUARIO = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
        }

        .container {
            background: white;
            color: #333;
            width: 100%;
            max-width: 900px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-in;
        }

        h2 {
            color: #1a237e;
            margin-bottom: 30px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 14px;
            font-size: 16px;
            text-align: center;
        }

        th {
            background-color: #3f51b5;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a {
            display: inline-block;
            padding: 10px 25px;
            background-color: #3f51b5;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        a:hover {
            background-color: #303f9f;
        }

        .volver {
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📩 Mis Notificaciones</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Mensaje</th>
                <th>Fecha y Hora</th>
                <th>Tipo</th>
                <th>Estado</th>
            </tr>
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($fila['idNOTIFICACIONES']) ?></td>
                    <td><?= htmlspecialchars($fila['NOTIFICACIONES_mensaje']) ?></td>
                    <td><?= htmlspecialchars($fila['NOTIFICACIONES_fecha_y_hora']) ?></td>
                    <td><?= htmlspecialchars($fila['NOTIFICACIONES_tipo_de_notificaciones']) ?></td>
                    <td><?= htmlspecialchars($fila['NOTIFICACIONES_estado']) ?></td>
                </tr>
            <?php } ?>
        </table>

        <div class="volver">
            <a href="menu.php">← Volver al Menú</a>
        </div>
    </div>
</body>
</html>
