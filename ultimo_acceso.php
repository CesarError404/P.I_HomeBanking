<?php session_start(); 
include("conexion.php"); 

$sql = "SELECT U.USUARIO_nombre, U.USUARIO_apellido, L.LOGIN_fecha_y_hora_de_acceso FROM LOGIN L JOIN USUARIO U ON L.LOGIN_idUsuario = U.idUSUARIO ORDER BY L.LOGIN_fecha_y_hora_de_acceso DESC LIMIT 1"; $resultado = $conexion->query($sql); $acceso = $resultado->fetch_assoc(); $nombreCompleto = $acceso["USUARIO_nombre"] . " " . $acceso["USUARIO_apellido"]; $fechaHora = $acceso["LOGIN_fecha_y_hora_de_acceso"]; $fecha = date("d/m/Y", strtotime($fechaHora)); $hora = date("H:i:s", strtotime($fechaHora)); ?> <!DOCTYPE html> <html lang="es"> <head> <meta charset="UTF-8"> <title>Último Acceso</title> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"> <style> * { margin: 0; padding: 0; box-sizing: border-box; }

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
        width: 90%;
        max-width: 600px;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        text-align: center;
        animation: fadeIn 1s ease-in;
    }

    h2 {
        color: #1a237e;
        margin-bottom: 30px;
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
    }

    th {
        background-color: #3f51b5;
        color: white;
        font-weight: 600;
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
    }

    a:hover {
        background-color: #303f9f;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head> <body> <?php include("sidebar.php"); ?>

<div class="container">
    <h2>Último acceso al sistema</h2>
    <table>
        <tr>
            <th>Usuario</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($nombreCompleto) ?></td>
            <td><?= htmlspecialchars($fecha) ?></td>
            <td><?= htmlspecialchars($hora) ?></td>
        </tr>
    </table>

    <a href="menu.php">← Volver al Menú</a>
</div>
</body> </html>


<?php include("sidebar.php"); ?>
