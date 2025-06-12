<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Obtener cuenta del usuario (simplificado: primera cuenta activa)
$sql_cuenta = "SELECT CUENTA_BANCARIA_numero_de_cuenta FROM CUENTA_BANCARIA WHERE USUARIO_idUSUARIO = ? AND CUENTA_BANCARIA_estado = 'Activa' LIMIT 1";
$stmt_cuenta = $conexion->prepare($sql_cuenta);
$stmt_cuenta->bind_param("i", $usuario_id);
$stmt_cuenta->execute();
$res_cuenta = $stmt_cuenta->get_result();
$cuenta = $res_cuenta->fetch_assoc();

if ($cuenta):
    $nro_cuenta = $cuenta["CUENTA_BANCARIA_numero_de_cuenta"];

    $sql = "SELECT TRANSACCIONES_fecha_y_hora, TRANSACCIONES_monto, TRANSACCIONES_tipo_de_movimiento, 
                   TRANSACCIONES_descripcion, TRANSACCIONES_moneda
            FROM TRANSACCIONES
            WHERE TRANSACCIONES_cuenta_origen = ? OR TRANSACCIONES_cuenta_destino = ?
            ORDER BY TRANSACCIONES_fecha_y_hora DESC LIMIT 5";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $nro_cuenta, $nro_cuenta);
    $stmt->execute();
    $resultado = $stmt->get_result();
?>

<h2>Últimas transacciones</h2>
<?php while ($trans = $resultado->fetch_assoc()): ?>
    <p><?php echo $trans["TRANSACCIONES_fecha_y_hora"]; ?> - <?php echo $trans["TRANSACCIONES_tipo_de_movimiento"]; ?> $<?php echo $trans["TRANSACCIONES_monto"]; ?> - <?php echo $trans["TRANSACCIONES_descripcion"]; ?></p>
<?php endwhile; ?>

<?php else: ?>
    <p>No tenés cuentas activas.</p>
<?php endif; ?>
