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
        $cbu = '100' . str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        $sql = "SELECT 1 FROM CUENTA_BANCARIA WHERE CUENTA_BANCARIA_cbu = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $cbu);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $cbu;
}

// Función para generar alias simple basado en nombre + apellido + número random
function generarAlias($nombre, $apellido, $conexion) {
    do {
        $alias = strtolower($nombre) . '.' . strtolower($apellido) . mt_rand(10, 99);
        $sql = "SELECT 1 FROM CUENTA_BANCARIA WHERE CUENTA_BANCARIA_alias = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $alias);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $alias;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $domicilio = trim($_POST['domicilio']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $tipo_cuenta = $_POST['tipo_cuenta'];

    $sql_usuario = "INSERT INTO USUARIO (USUARIO_nombre, USUARIO_apellido, USUARIO_contrasena, USUARIO_correo_direccion)
                    VALUES (?, ?, ?, ?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("ssss", $nombre, $apellido, $contrasena, $correo);

    if ($stmt_usuario->execute()) {
        $idUsuario = $stmt_usuario->insert_id;

        $sql_persona = "INSERT INTO PERSONA (PERSONA_dni, PERSONA_domicilio, PERSONA_telefono, USUARIO_idUSUARIO)
                        VALUES (?, ?, ?, ?)";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("sssi", $dni, $domicilio, $telefono, $idUsuario);

        if ($stmt_persona->execute()) {
            $numero_cuenta = generarNumeroCuenta($conn);
            $cbu = generarCBU($conn);
            $alias = generarAlias($nombre, $apellido, $conn);
            $saldo_inicial = 0;
            $estado = 'Activa';

            $sql_cuenta = "INSERT INTO CUENTA_BANCARIA 
                           (CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_cbu, CUENTA_BANCARIA_alias, CUENTA_BANCARIA_saldo, USUARIO_idUSUARIO, CUENTA_BANCARIA_estado, CUENTA_BANCARIA_tipo_de_cuenta)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_cuenta = $conn->prepare($sql_cuenta);
            $stmt_cuenta->bind_param("sssisss", $numero_cuenta, $cbu, $alias, $saldo_inicial, $idUsuario, $estado, $tipo_cuenta);

            if ($stmt_cuenta->execute()) {
                header("Location: login.php?registro=exitoso");
                exit();
            } else {
                $mensaje = "Error al crear la cuenta bancaria: " . $conn->error;
            }

            $stmt_cuenta->close();
        } else {
            $mensaje = "Error al registrar datos personales: " . $conn->error;
        }

        $stmt_persona->close();
    } else {
        $mensaje = "Error al crear el usuario: " . $conn->error;
    }

    $stmt_usuario->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Registro - Homebanking</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
      padding: 1rem;
      box-sizing: border-box;
    }

    .form-container {
      background-color: #ffffff;
      padding: 1.0rem;
      border-radius: 1rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 500px;
      box-sizing: border-box;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .avatar {
      position: absolute;
      top: -0px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 60px;
      background-color: #3b82f6;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 28px;
      box-shadow: 0 0 10px rgba(59, 130, 246, 0.7);
      border: 3px solid white;
    }

    .form-container h2 {
      font-size: 1.3rem;
      color: #1f2937;
      margin-top: 3rem;
      margin-bottom: 1rem;
      text-align: center;
      width: 100%;
    }

    form {
      width: 100%;
    }

    .input-group {
      margin-bottom: 0.7rem;
      width: 100%;
    }

    .input-group label {
      display: block;
      font-size: 0.85rem;
      color: #374151;
      margin-bottom: 0.25rem;
    }

    .input-group input,
    .input-group select {
      width: 100%;
      padding: 0.5rem;
      font-size: 0.9rem;
      border: 1px solid #cbd5e1;
      border-radius: 0.4rem;
      transition: 0.3s border-color ease;
    }

    .input-group input:focus,
    .input-group select:focus {
      border-color: #3b82f6;
      outline: none;
    }

    .btn {
      width: 100%;
      background-color: #3b82f6;
      color: white;
      font-weight: 600;
      border: none;
      padding: 0.6rem;
      font-size: 0.95rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      margin-top: 0.6rem;
    }

    .btn:hover {
      background-color: #2563eb;
      transform: scale(1.03);
    }

    .mensaje {
      margin-top: 0.6rem;
      text-align: center;
      font-weight: bold;
      color: #ef4444;
      font-size: 0.9rem;
    }

    p {
      margin-top: 1rem;
    }

    p a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    p a:hover {
      color: #2563eb;
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <div class="avatar">
      <i class="fas fa-user"></i>
    </div>
    <h2>Registro de Usuario</h2>
    <form method="POST">
      <div class="input-group">
        <label>Nombre:</label>
        <input type="text" name="nombre" required />
      </div>
      <div class="input-group">
        <label>Apellido:</label>
        <input type="text" name="apellido" required />
      </div>
      <div class="input-group">
        <label>Correo Electrónico:</label>
        <input type="email" name="correo" required />
      </div>
      <div class="input-group">
        <label>Contraseña:</label>
        <input type="password" name="contrasena" required />
      </div>
      <div class="input-group">
        <label>DNI:</label>
        <input type="text" name="dni" required />
      </div>
      <div class="input-group">
        <label>Domicilio:</label>
        <input type="text" name="domicilio" required />
      </div>
      <div class="input-group">
        <label>Teléfono:</label>
        <input type="text" name="telefono" required />
      </div>
      <div class="input-group">
        <label>Tipo de Cuenta:</label>
        <select name="tipo_cuenta" required>
          <option value="">Seleccione una opción</option>
          <option value="Cuenta Corriente">Cuenta Corriente</option>
          <option value="Caja de Ahorro">Caja de Ahorro</option>
        </select>
      </div>
      <button type="submit" class="btn">Registrarse</button>
      <?php if (!empty($mensaje)) : ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
      <?php endif; ?>
    </form>
    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
  </div>
</body>
</html>
