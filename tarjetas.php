<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener el id_persona correspondiente al usuario logueado
$sql = "SELECT idPERSONA FROM PERSONA WHERE USUARIO_idUSUARIO = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($fila = $result->fetch_assoc()) {
    $id_persona = $fila['idPERSONA'];
} else {
    echo "No se pudo encontrar la persona asociada al usuario.";
    exit();
}

// Variables para editar
$editando = false;
$numero_edit = "";
$tipo_edit = "";
$estado_edit = "";
$fecha_vencimiento_edit = "";

// Procesar eliminar tarjeta
if (isset($_GET['eliminar'])) {
    $numero_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM TARJETA WHERE numero_tarjeta = ? AND PERSONA_idPERSONA = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $numero_eliminar, $id_persona);
    $stmt->execute();
    header("Location: tarjetas.php");
    exit();
}

// Procesar editar: cargar datos para el formulario
if (isset($_GET['editar'])) {
    $numero_edit = $_GET['editar'];
    $sql = "SELECT numero_tarjeta, tipo_tarjeta, estado, fecha_vencimiento FROM TARJETA WHERE numero_tarjeta = ? AND PERSONA_idPERSONA = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $numero_edit, $id_persona);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $editando = true;
        $tipo_edit = $fila['tipo_tarjeta'];
        $estado_edit = $fila['estado'];
        $fecha_vencimiento_edit = $fila['fecha_vencimiento'];
    } else {
        header("Location: tarjetas.php");
        exit();
    }
}

// Procesar POST: agregar o actualizar tarjeta
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero = $_POST['numero'];
    $tipo = $_POST['tipo'];
    $estado = $_POST['estado'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    if (isset($_POST['editar'])) {
        $sql = "UPDATE TARJETA SET tipo_tarjeta = ?, estado = ?, fecha_vencimiento = ? WHERE numero_tarjeta = ? AND PERSONA_idPERSONA = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $tipo, $estado, $fecha_vencimiento, $numero, $id_persona);

        if (!$stmt->execute()) {
            echo "Error al actualizar tarjeta: " . $stmt->error;
        } else {
            header("Location: tarjetas.php");
            exit();
        }
    } else {
        $sql = "INSERT INTO TARJETA (numero_tarjeta, tipo_tarjeta, estado, fecha_vencimiento, PERSONA_idPERSONA) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $numero, $tipo, $estado, $fecha_vencimiento, $id_persona);

        if (!$stmt->execute()) {
            echo "Error al agregar tarjeta: " . $stmt->error;
        } else {
            header("Location: tarjetas.php");
            exit();
        }
    }
}

// Consultar tarjetas del usuario
$sql = "SELECT numero_tarjeta, tipo_tarjeta, estado, fecha_vencimiento FROM TARJETA WHERE PERSONA_idPERSONA = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_persona);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tarjetas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a, #3b82f6);
            color: #1f2937;
            margin: 0;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 1s ease;
            min-height: 100vh;
        }

        h2, h3 {
            color: #ffffff;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            max-width: 900px;
            background: white;
            border-collapse: collapse;
            margin-bottom: 2rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            animation: slideUp 0.7s ease;
        }

        th, td {
            padding: 0.75rem;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #3b82f6;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        form {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.8s ease;
        }

        form input[type="text"],
        form input[type="date"],
        form select {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            color: #000;
            background-color: #fff;
        }

        form button, form a {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        form button {
            background-color: #3b82f6;
            color: white;
        }

        form a {
            color: #ef4444;
            text-decoration: none;
            margin-left: 1rem;
        }

        a[href="menu.php"] {
            margin-top: 2rem;
            text-decoration: none;
            color: white;
            background-color: #1e40af;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }

        a[href="menu.php"]:hover {
            background-color: #1d4ed8;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        @keyframes slideUp {
            from {transform: translateY(30px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }
    </style>
</head>
<body>
    <h2>Mis Tarjetas</h2>

    <?php if ($resultado->num_rows > 0): ?>
        <table>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Acciones</th>
            </tr>
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['numero_tarjeta']); ?></td>
                    <td><?php echo htmlspecialchars($fila['tipo_tarjeta']); ?></td>
                    <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    <td><?php echo htmlspecialchars($fila['fecha_vencimiento']); ?></td>
                    <td>
                        <a href="tarjetas.php?editar=<?php echo urlencode($fila['numero_tarjeta']); ?>">✏️</a>
                        <a href="tarjetas.php?eliminar=<?php echo urlencode($fila['numero_tarjeta']); ?>" onclick="return confirm('¿Seguro que querés eliminar esta tarjeta?');">🗑️</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php else: ?>
        <p style="color: white;">No tenés tarjetas cargadas.</p>
    <?php endif; ?>

    <h3><?php echo $editando ? "Editar tarjeta" : "Agregar nueva tarjeta"; ?></h3>
    <form method="POST" action="tarjetas.php">
        Número de tarjeta: 
        <?php if ($editando): ?>
            <input type="text" name="numero" value="<?php echo htmlspecialchars($numero_edit); ?>" readonly>
        <?php else: ?>
            <input type="text" name="numero" required>
        <?php endif; ?>

        <select name="tipo" required>
            <option value="" disabled <?php echo $tipo_edit == "" ? "selected" : ""; ?>>Seleccionar tipo</option>
            <option value="Débito" <?php echo $tipo_edit == "Débito" ? "selected" : ""; ?>>Débito</option>
            <option value="Crédito" <?php echo $tipo_edit == "Crédito" ? "selected" : ""; ?>>Crédito</option>
        </select>

        <input type="text" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($estado_edit); ?>" required>
        <input type="date" name="fecha_vencimiento" value="<?php echo htmlspecialchars($fecha_vencimiento_edit); ?>" required>

        <?php if ($editando): ?>
            <button type="submit" name="editar">Guardar cambios</button>
            <a href="tarjetas.php">Cancelar</a>
        <?php else: ?>
            <button type="submit">Agregar tarjeta</button>
        <?php endif; ?>
    </form>

    <a href="menu.php">⬅ Volver al menú principal</a>
</body>
</html>
