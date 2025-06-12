<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuarioId = $_SESSION["usuario_id"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $cantidad_cuotas = intval($_POST['cantidad_cuotas']);
    $monto_solicitado = floatval($_POST['monto_solicitado']);
    $tipo_interes = $_POST['tipo_interes'];
    $tipo_movimiento = "Crédito";
    $estado = 'Pendiente';
    $monto_aprobado = $monto_solicitado * 0.96;

    $sql_insert = "INSERT INTO PRESTAMO 
        (PRESTAMO_cantidad_cuotas, PRESTAMO_estado, PRESTAMO_monto_solicitado, PRESTAMO_monto_aprobado, PRESTAMO_tipo_de_interes, PRESTAMO_tipo_de_movimiento_prestamo, USUARIO_idUSUARIO)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("isddssi", $cantidad_cuotas, $estado, $monto_solicitado, $monto_aprobado, $tipo_interes, $tipo_movimiento, $usuarioId);
    $stmt->execute();
    $stmt->close();
}

$sql_prestamos = "SELECT idPRESTAMO, PRESTAMO_cantidad_cuotas, PRESTAMO_estado, PRESTAMO_monto_solicitado, PRESTAMO_monto_aprobado, PRESTAMO_tipo_de_interes, PRESTAMO_tipo_de_movimiento_prestamo FROM PRESTAMO WHERE USUARIO_idUSUARIO = ?";
$stmt = $conexion->prepare($sql_prestamos);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestión de Préstamos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298); /* mismo fondo que tarjetas.php */
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 50px 20px;
        }

        .container {
            background: white;
            color: #333;
            width: 95%;
            max-width: 1100px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-in;
        }

        h2 {
            text-align: center;
            color: #1a237e;
            margin-bottom: 25px;
        }

        form {
            display: grid;
            gap: 15px;
            animation: fadeInUp 1s ease;
        }

        input, select, button {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 5px rgba(63, 81, 181, 0.3);
            outline: none;
        }

        button {
            background: #3f51b5;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #303f9f;
        }

        .separador {
            height: 50px; /* espacio extra entre formularios */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            animation: fadeInUp 1s ease;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }

        th {
            background-color: #3f51b5;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Solicitar Nuevo Préstamo</h2>
        <form method="POST" action="prestamos.php">
            <input type="number" name="cantidad_cuotas" placeholder="Cantidad de Cuotas" min="1" required />
            <input type="number" name="monto_solicitado" placeholder="Monto Solicitado" step="0.01" min="0.01" required />
            
            <select name="tipo_interes" required>
                <option value="">Seleccionar Tipo de Interés</option>
                <option value="Fijo">Fijo</option>
                <option value="Variable">Variable</option>
            </select>
            
            <button type="submit" name="agregar">Solicitar Préstamo</button>
        </form>

        <div class="separador"></div>

        <h2>Préstamos Solicitados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cuotas</th>
                    <th>Estado</th>
                    <th>Solicitado</th>
                    <th>Aprobado</th>
                    <th>Interés</th>
                    <th>Movimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($prestamo = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($prestamo['idPRESTAMO']) ?></td>
                        <td><?= htmlspecialchars($prestamo['PRESTAMO_cantidad_cuotas']) ?></td>
                        <td><?= htmlspecialchars($prestamo['PRESTAMO_estado']) ?></td>
                        <td>$<?= number_format($prestamo['PRESTAMO_monto_solicitado'], 2, ',', '.') ?></td>
                        <td>$<?= number_format($prestamo['PRESTAMO_monto_aprobado'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($prestamo['PRESTAMO_tipo_de_interes']) ?></td>
                        <td><?= htmlspecialchars($prestamo['PRESTAMO_tipo_de_movimiento_prestamo']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>
