<?php
include 'conexion.php';
$conn = $conexion;

// Función para generar número de cuenta único (8 dígitos)
function generarNumeroCuenta($conexion) {
    do {
        $numero = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $sql = "SELECT 1 FROM CUENTA_BANCARIA WHERE CUENTA_BANCARIA_numero_de_cuenta = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $numero);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $numero;
}

// Función para generar CBU único de 10 dígitos que empieza con 100
function generarCBU($conexion) {
    do {
        $cbu = "100" . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        $sql = "SELECT 1 FROM CUENTA_BANCARIA WHERE CUENTA_BANCARIA_cbu = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $cbu);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $cbu;
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $dni = $_POST['dni'];
    $domicilio = $_POST['domicilio'];
    $telefono = $_POST['telefono'];

    $conexion->begin_transaction();
    try {
        // Insertar usuario
        $sql_usuario = "INSERT INTO USUARIO (USUARIO_nombre, USUARIO_apellido, USUARIO_correo_direccion, USUARIO_contrasena)
                        VALUES (?, ?, ?, ?)";
        $stmt_usuario = $conexion->prepare($sql_usuario);
        $stmt_usuario->bind_param("ssss", $nombre, $apellido, $correo, $contrasena);
        $stmt_usuario->execute();
        $usuario_id = $stmt_usuario->insert_id;

        // Insertar persona
        $sql_persona = "INSERT INTO PERSONA (PERSONA_dni, PERSONA_domicilio, PERSONA_telefono, USUARIO_idUSUARIO)
                        VALUES (?, ?, ?, ?)";
        $stmt_persona = $conexion->prepare($sql_persona);
        $stmt_persona->bind_param("sssi", $dni, $domicilio, $telefono, $usuario_id);
        $stmt_persona->execute();

        // Insertar cuenta bancaria
        $numero_cuenta = generarNumeroCuenta($conexion);
        $cbu = generarCBU($conexion);
        $sql_cuenta = "INSERT INTO CUENTA_BANCARIA (CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_cbu, USUARIO_idUSUARIO)
                       VALUES (?, ?, ?)";
        $stmt_cuenta = $conexion->prepare($sql_cuenta);
        $stmt_cuenta->bind_param("ssi", $numero_cuenta, $cbu, $usuario_id);
        $stmt_cuenta->execute();

        $conexion->commit();
        header("Location: login.php"); // ✅ Redirección automática tras registro
        exit();
    } catch (Exception $e) {
        $conexion->rollback();
        $mensaje = "Error al registrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Homebanking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a, #3b82f6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1.2s ease;
        }

        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            animation: slideUp 1s ease;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            color: #374151;
            margin-bottom: 0.3rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            transition: 0.3s border-color ease;
        }

        .input-group input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .btn {
            width: 100%;
            background-color: #3b82f6;
            color: white;
            font-weight: 600;
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: #2563eb;
            transform: scale(1.03);
        }

        .mensaje {
            margin-top: 1rem;
            text-align: center;
            font-weight: bold;
            color: #10b981;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Registro de Usuario</h2>
        <form method="POST">
            <div class="input-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="input-group">
                <label>Apellido:</label>
                <input type="text" name="apellido" required>
            </div>
            <div class="input-group">
                <label>Correo:</label>
                <input type="email" name="correo" required>
            </div>
            <div class="input-group">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" required>
            </div>
            <div class="input-group">
                <label>DNI:</label>
                <input type="text" name="dni" required>
            </div>
            <div class="input-group">
                <label>Domicilio:</label>
                <input type="text" name="domicilio" required>
            </div>
            <div class="input-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono" required>
            </div>
            <button type="submit" class="btn">Registrarse</button>
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje"><?php echo $mensaje; ?></div>
            <?php endif; ?>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>
