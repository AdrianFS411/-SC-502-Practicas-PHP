<?php
session_start();

if (!isset($_SESSION['transacciones'])) {
    $_SESSION['transacciones'] = [];
}

function registrarTransaccion($id, $descripcion, $monto) {
    foreach ($_SESSION['transacciones'] as $transaccion) {
        if ($transaccion['id'] === $id) {
            return false;
        }
    }
    $_SESSION['transacciones'][] = ['id' => $id, 'descripcion' => $descripcion, 'monto' => $monto];
    return true;
}

function generarEstadoDeCuenta() {
    $totalContado = 0;
    $texto = "ESTADO DE CUENTA - TARJETA DE CREDITO\n";
    $texto .= "=====================================\n";
    foreach ($_SESSION['transacciones'] as $transaccion) {
        $totalContado += $transaccion['monto'];
        $texto .= "ID: {$transaccion['id']} | {$transaccion['descripcion']} | " . number_format($transaccion['monto'], 2) . "\n";
    }
    $interes = $totalContado * 0.026;
    $totalConInteres = $totalContado + $interes;
    $cashback = $totalContado * 0.001;
    $totalPagar = $totalConInteres - $cashback;
    $texto .= "-------------------------------------\n";
    $texto .= "Total de contado: " . number_format($totalContado, 2) . "\n";
    $texto .= "Interes (2.6%): " . number_format($interes, 2) . "\n";
    $texto .= "Total con intereses: " . number_format($totalConInteres, 2) . "\n";
    $texto .= "Cashback (0.1%): " . number_format($cashback, 2) . "\n";
    $texto .= "MONTO FINAL A PAGAR: " . number_format($totalPagar, 2) . "\n";
    return compact('totalContado', 'interes', 'totalConInteres', 'cashback', 'totalPagar', 'texto');
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id = trim($_POST['id'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $monto = filter_var($_POST['monto'] ?? '', FILTER_VALIDATE_FLOAT);
        if ($id === '' || $descripcion === '' || $monto === false || !is_finite((float)$monto) || $monto <= 0) {
            $mensaje = 'Complete los datos correctamente. El monto debe ser mayor que cero.';
        } elseif (registrarTransaccion($id, $descripcion, (float)$monto)) {
            $mensaje = 'Transaccion registrada correctamente.';
        } else {
            $mensaje = 'Ya existe una transaccion con ese ID.';
        }
    } elseif ($accion === 'limpiar') {
        $_SESSION['transacciones'] = [];
        $mensaje = 'Se eliminaron las transacciones.';
    }
}

$estado = generarEstadoDeCuenta();
if (isset($_GET['descargar']) && $_GET['descargar'] === '1') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="estado_cuenta.txt"');
    echo $estado['texto'];
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de cuenta - Tarjeta de credito</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f4f8; margin: 0; color: #243044; }
        main { max-width: 850px; margin: 35px auto; padding: 0 18px; }
        h1 { color: #173b6b; }
        section { background: white; border-radius: 10px; padding: 22px; margin: 20px 0; box-shadow: 0 2px 10px #00000012; }
        label { display: block; font-weight: bold; margin: 12px 0 5px; }
        input { box-sizing: border-box; width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 5px; }
        button, .boton { display: inline-block; background: #205b9c; color: white; border: 0; border-radius: 5px; padding: 11px 17px; margin-top: 15px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .secundario { background: #64748b; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        th { background: #eaf1f9; }
        .tabla { overflow-x: auto; }
        .resumen p { display: flex; justify-content: space-between; gap: 10px; border-bottom: 1px solid #eee; padding-bottom: 9px; }
        .final { font-weight: bold; font-size: 19px; color: #14532d; }
        .mensaje { background: #e6f1ff; padding: 12px; border-radius: 5px; }
    </style>
</head>
<body>
<main>
    <h1>Estado de cuenta de tarjeta de credito</h1>
    <p>Registro de compras y calculo del monto final a pagar.</p>
    <section>
        <h2>Registrar transaccion</h2>
        <?php if ($mensaje !== ''): ?><p class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form method="post">
            <input type="hidden" name="accion" value="agregar">
            <label for="id">ID de transaccion</label>
            <input id="id" name="id" required maxlength="30" placeholder="Ejemplo: 001">
            <label for="descripcion">Descripcion</label>
            <input id="descripcion" name="descripcion" required maxlength="150" placeholder="Ejemplo: Compra en supermercado">
            <label for="monto">Monto</label>
            <input id="monto" name="monto" type="number" min="0.01" step="0.01" required placeholder="Ejemplo: 25000">
            <button type="submit">Agregar transaccion</button>
        </form>
    </section>
    <section>
        <h2>Transacciones registradas</h2>
        <div class="tabla"><table>
            <thead><tr><th>ID</th><th>Descripcion</th><th>Monto</th></tr></thead>
            <tbody>
            <?php foreach ($_SESSION['transacciones'] as $transaccion): ?>
                <tr><td><?= htmlspecialchars($transaccion['id'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($transaccion['descripcion'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format($transaccion['monto'], 2) ?></td></tr>
            <?php endforeach; ?>
            <?php if (count($_SESSION['transacciones']) === 0): ?><tr><td colspan="3">Todavia no hay transacciones.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
    <section class="resumen">
        <h2>Resumen del estado de cuenta</h2>
        <p><span>Total de contado</span><strong><?= number_format($estado['totalContado'], 2) ?></strong></p>
        <p><span>Interes (2.6%)</span><strong><?= number_format($estado['interes'], 2) ?></strong></p>
        <p><span>Total con intereses</span><strong><?= number_format($estado['totalConInteres'], 2) ?></strong></p>
        <p><span>Cashback (0.1%)</span><strong>- <?= number_format($estado['cashback'], 2) ?></strong></p>
        <p class="final"><span>Monto final a pagar</span><span><?= number_format($estado['totalPagar'], 2) ?></span></p>
        <a class="boton" href="?descargar=1">Descargar estado_cuenta.txt</a>
        <form method="post" onsubmit="return confirm('¿Eliminar todas las transacciones?');">
            <input type="hidden" name="accion" value="limpiar">
            <button class="secundario" type="submit">Limpiar transacciones</button>
        </form>
    </section>
</main>
</body>
</html>
