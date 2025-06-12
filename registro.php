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
        $sql_usuario = "INSERT INTO USUARIO (USUARIO_nombre, USUARIO_apellido, USUARIO_correo_direccion, USUARIO_contrasena)
                        VALUES (?, ?, ?, ?)";
        $stmt_usuario = $conexion->prepare($sql_usuario);
        $stmt_usuario->bind_param("ssss", $nombre, $apellido, $correo, $contrasena);
        $stmt_usuario->execute();
        $usuario_id = $stmt_usuario->insert_id;

        $sql_persona = "INSERT INTO PERSONA (PERSONA_dni, PERSONA_domicilio, PERSONA_telefono, USUARIO_idUSUARIO)
                        VALUES (?, ?, ?, ?)";
        $stmt_persona = $conexion->prepare($sql_persona);
        $stmt_persona->bind_param("sssi", $dni, $domicilio, $telefono, $usuario_id);
        $stmt_persona->execute();

        $numero_cuenta = generarNumeroCuenta($conexion);
        $cbu = generarCBU($conexion);
        $sql_cuenta = "INSERT INTO CUENTA_BANCARIA (CUENTA_BANCARIA_numero_de_cuenta, CUENTA_BANCARIA_cbu, USUARIO_idUSUARIO)
                       VALUES (?, ?, ?)";
        $stmt_cuenta = $conexion->prepare($sql_cuenta);
        $stmt_cuenta->bind_param("ssi", $numero_cuenta, $cbu, $usuario_id);
        $stmt_cuenta->execute();

        $conexion->commit();
        header("Location: login.php");
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
  <meta charset="UTF-8" />
  <title>Registro - Homebanking</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />
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
    padding: 1.5rem 1.5rem; /* un poco más de padding vertical */
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 500px;
    max-height: 700px; /* un poco más alto */
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    }


    .avatar {
    position: absolute;
    top: -0px; /* menos negativo = baja más */
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
    font-size: 1.3rem;  /* más pequeño */
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
    margin-bottom: 0.7rem; /* menos espacio */
    width: 100%;
    }

    .input-group label {
    display: block;
    font-size: 0.85rem; /* más pequeño */
    color: #374151;
    margin-bottom: 0.25rem;
    }

    .input-group input {
    width: 100%;
    padding: 0.5rem; /* menos padding */
    font-size: 0.9rem; /* más pequeño */
    border: 1px solid #cbd5e1;
    border-radius: 0.4rem;
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
    padding: 0.6rem; /* menos padding */
    font-size: 0.95rem; /* más pequeño */
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: 0.6rem; /* menos margen */
    }

    .btn:hover {
    background-color: #2563eb;
    transform: scale(1.03);
    }

    .mensaje {
    margin-top: 0.6rem; /* menos margen */
    text-align: center;
    font-weight: bold;
    color: #10b981;
    font-size: 0.9rem;
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
        <label>Correo:</label>
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
      <button type="submit" class="btn">Registrarse</button>
      <?php if (!empty($mensaje)) : ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
      <?php endif; ?>
    </form>
    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
  </div>
</body>
</html>
