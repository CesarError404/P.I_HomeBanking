<?php
include("sesion.php");

$query = "
SELECT SUM(CB.CUENTA_BANCARIA_saldo) AS total_saldo
FROM CUENTA_BANCARIA CB
WHERE CB.USUARIO_idUSUARIO = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

echo "<h2>Saldo Total</h2>";
echo "<p>Tu saldo total es: {$fila['total_saldo']}</p>";
?>
