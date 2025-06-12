<?php
session_start();
include("conexion.php");
include("menu.php");

$id_usuario = $_SESSION['id_usuario'] ?? null;
$saldo_total = 0;

if ($id_usuario) {
    $query = "SELECT SUM(SALDO) AS saldo_total FROM CUENTA_BANCARIA WHERE ID_USUARIO = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($saldo_total);
    $stmt->fetch();
    $stmt->close();
}
?>

<!-- Sección de saldo -->
<div style="max-width: 600px; margin: 40px auto; padding: 20px; background-color: #f8f9fa; border-radius: 16px; box-shadow: 0 6px 18px rgba(0,0,0,0.1);">
    <div style="background: linear-gradient(135deg, #0b1c2c, #1d3557); padding: 30px; border-radius: 12px; color: white; text-align: center;">
        <h2 style="margin-bottom: 10px; font-size: 24px;">Saldo Total Disponible</h2>
        <p style="font-size: 36px; font-weight: bold; margin: 0;">$<?= number_format($saldo_total, 2, ',', '.') ?></p>
    </div>
</div>

<!-- Botón flotante -->
<a href="transferencias.php"
   title="Nueva Transferencia"
   style="
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #007bff;
        color: #fff;
        padding: 15px 20px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        z-index: 1000;
    ">
    <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='white' viewBox='0 0 16 16'>
        <path d='M8 0a.5.5 0 0 1 .5.5V7h6.5a.5.5 0 0 1 0 1H8.5v6.5a.5.5 0 0 1-1 0V8H1a.5.5 0 0 1 0-1h6.5V.5A.5.5 0 0 1 8 0z'/>
    </svg>
    Nueva Transferencia
</a>
