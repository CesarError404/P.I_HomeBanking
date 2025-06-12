<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo "No estás logueado.";
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$personaId = $_SESSION['persona_id'] ?? null;

$mensaje = "";
$mostrarFormulario = false;

// Procesar POST (guardar o actualizar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni       = $_POST['dni'];
    $domicilio = $_POST['domicilio'];
    $telefono  = $_POST['telefono'];

    if ($personaId) {
        $sql = "UPDATE PERSONA SET PERSONA_dni=?, PERSONA_domicilio=?, PERSONA_telefono=? WHERE idPERSONA=? AND USUARIO_idUSUARIO=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssii", $dni, $domicilio, $telefono, $personaId, $usuarioId);
    } else {
        $sql = "INSERT INTO PERSONA (PERSONA_dni, PERSONA_domicilio, PERSONA_telefono, USUARIO_idUSUARIO) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $dni, $domicilio, $telefono, $usuarioId);
    }

    if ($stmt->execute()) {
        if (!$personaId) {
            $personaId = $conexion->insert_id;
            $_SESSION['persona_id'] = $personaId;
        }

        $_SESSION['persona_dni']       = $dni;
        $_SESSION['persona_domicilio'] = $domicilio;
        $_SESSION['persona_telefono']  = $telefono;

        $mensaje = "Datos guardados correctamente.";
        $mostrarFormulario = false;
    } else {
        $mensaje = "Error al guardar datos: " . $stmt->error;
        $mostrarFormulario = true;
    }
} else {
    // Si vino GET con ?editar=1 mostramos formulario para editar
    if (isset($_GET['editar']) && $_GET['editar'] == 1) {
        $mostrarFormulario = true;
    }
}

// Datos para mostrar
$dni       = $_SESSION['persona_dni'] ?? "";
$domicilio = $_SESSION['persona_domicilio'] ?? "";
$telefono  = $_SESSION['persona_telefono'] ?? "";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Mis datos personales</title>
    <style>
        /* Fondo general y centrado */
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



        h2 {
            color: white;
            margin-bottom: 2rem;
            font-weight: 700;
            text-shadow: 1px 1px 6px rgba(0,0,0,0.3);
        }

        .tarjeta {
            background: linear-gradient(135deg, #e0e7ff, #ffffff);
            padding: 2.5rem 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
            max-width: 700px;
            width: 100%;
            color: #1e293b;
            position: relative;
            animation: slideUp 0.8s ease;
            display: flex;
            flex-direction: column;
            gap: 1.6rem;
        }

        @keyframes slideUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .tarjeta h3 {
            margin: 0 0 1rem 0;
            padding-bottom: 0.4rem;
            border-bottom: 3px solid #3b82f6;
            color: #2563eb;
            font-weight: 700;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .tarjeta h3::before {
            content: "👤";
            font-size: 2rem;
        }

        .info-fila {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 1rem;
            background: #f1f5f9;
            border-radius: 0.75rem;
            font-size: 1.2rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            transition: background-color 0.3s ease;
        }

        .info-fila:hover {
            background-color: #e0e7ff;
        }

        .info-label {
            font-weight: 600;
            color: #334155;
        }

        .info-valor {
            font-weight: 500;
            color: #1e293b;
        }

        .btn-editar {
            position: absolute;
            top: 1.8rem;
            right: 1.8rem;
            padding: 8px 16px;
            background-color: #2563eb;
            font-size: 1rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            border: none;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-editar:hover {
            background-color: #1e40af;
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.6);
        }

        form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 6px 0 12px 0;
            box-sizing: border-box;
            font-size: 1.1rem;
            border-radius: 0.5rem;
            border: 1.5px solid #94a3b8;
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 5px #2563ebaa;
        }

        form button {
            padding: 12px 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 1rem;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #1e40af;
        }

        .mensaje {
            max-width: 700px;
            margin: 0 auto 20px auto;
            color: #22c55e;
            font-weight: 700;
            font-size: 1.2rem;
            text-align: center;
            text-shadow: 0 0 3px #22c55e88;
        }

        a[href="menu.php"] {
            margin-top: 3rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            text-decoration: none;
            text-align: center;
            display: block;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
            user-select: none;
            transition: color 0.3s ease;
        }

        a[href="menu.php"]:hover {
            color: #c7d2fe;
        }
    </style>
</head>
<body>

<h2>Mis datos personales</h2>

<?php if ($mensaje): ?>
    <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<?php if ($mostrarFormulario): ?>

    <div class="tarjeta">
        <form method="POST" action="persona.php">
            <label>DNI:</label>
            <input type="text" name="dni" value="<?php echo htmlspecialchars($dni); ?>" required>

            <label>Domicilio:</label>
            <input type="text" name="domicilio" value="<?php echo htmlspecialchars($domicilio); ?>">

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">

            <button type="submit">Guardar cambios</button>
        </form>
    </div>

<?php else: ?>

    <div class="tarjeta">
        <h3>Mis datos personales</h3>
        <div class="info-fila"><span class="info-label">DNI:</span> <span class="info-valor"><?php echo htmlspecialchars($dni); ?></span></div>
        <div class="info-fila"><span class="info-label">Domicilio:</span> <span class="info-valor"><?php echo htmlspecialchars($domicilio); ?></span></div>
        <div class="info-fila"><span class="info-label">Teléfono:</span> <span class="info-valor"><?php echo htmlspecialchars($telefono); ?></span></div>

        <a href="persona.php?editar=1"><button class="btn-editar">Editar</button></a>
    </div>

<?php endif; ?>

<br>
<a href="menu.php">⬅ Volver al menú</a>

</body>
</html>