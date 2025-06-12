<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    // Consulta con LEFT JOIN para obtener datos de usuario y persona
    $sql = "
        SELECT 
            U.idUSUARIO, 
            U.USUARIO_nombre, 
            U.USUARIO_apellido,
            U.USUARIO_contrasena,
            U.USUARIO_correo_direccion,
            P.idPERSONA,
            P.PERSONA_dni,
            P.PERSONA_domicilio,
            P.PERSONA_telefono
        FROM USUARIO U
        LEFT JOIN PERSONA P ON U.idUSUARIO = P.USUARIO_idUSUARIO
        WHERE U.USUARIO_correo_direccion = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        $passDB = $usuario["USUARIO_contrasena"];

        // Detectar si la contraseña está hasheada con bcrypt
        if (strlen($passDB) === 60 && preg_match('/^\$2[ayb]\$.{56}$/', $passDB)) {
            $acceso_valido = password_verify($contrasena, $passDB);
        } else {
            $acceso_valido = ($contrasena === $passDB); // Para contraseñas antiguas sin hash
        }

        if ($acceso_valido) {
            // Guardar datos en sesión
            $_SESSION["usuario_id"] = $usuario["idUSUARIO"];
            $_SESSION["usuario_nombre"] = $usuario["USUARIO_nombre"];
            $_SESSION["usuario_apellido"] = $usuario["USUARIO_apellido"];
            $_SESSION["persona_id"] = $usuario["idPERSONA"];
            $_SESSION["persona_dni"] = $usuario["PERSONA_dni"];
            $_SESSION["persona_domicilio"] = $usuario["PERSONA_domicilio"];
            $_SESSION["persona_telefono"] = $usuario["PERSONA_telefono"];

            // Registrar login en tabla LOGIN
            $sql_login = "INSERT INTO LOGIN (LOGIN_idUsuario, LOGIN_estado, LOGIN_fecha_y_hora_de_acceso) VALUES (?, 'Activo', NOW())";
            $stmt_login = $conexion->prepare($sql_login);
            $stmt_login->bind_param("i", $usuario["idUSUARIO"]);
            $stmt_login->execute();

            $_SESSION["idLOGIN"] = $stmt_login->insert_id;

            header("Location: menu.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Correo no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar Sesión - Homebanking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1.2s ease;
        }

        .login-container {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: slideUp 1s ease;
        }

        .login-container h2 {
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .input-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: border-color 0.3s ease;
            font-size: 1rem;
        }

        .input-group input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-size: 1rem;
        }

        .btn:hover {
            background-color: #2563eb;
            transform: scale(1.03);
        }

        .error {
            color: red;
            margin-top: 1rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .register-link {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #374151;
        }

        .register-link a {
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form method="POST" action="">
            <div class="input-group">
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="correo" required />
            </div>
            <div class="input-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required />
            </div>
            <button type="submit" class="btn">Ingresar</button>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="register-link">
                ¿No tenés cuenta?
                <a href="registro.php">Registrate</a>
            </div>
        </form>
    </div>
</body>
</html>
