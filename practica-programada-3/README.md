# Práctica Programada #3 - PHP

Sistema de registro de transacciones de tarjeta de crédito y generación de estado de cuenta.

## Uso

1. Instalar PHP 8 o superior.
2. Abrir una terminal dentro de esta carpeta.
3. Ejecutar `php -S localhost:8000`.
4. Visitar `http://localhost:8000` en el navegador.
5. Registrar transacciones y descargar `estado_cuenta.txt`.

## Cálculos

- Total de contado = suma de las transacciones.
- Interés = total de contado × 0.026.
- Total con intereses = total de contado + interés.
- Cashback = total de contado × 0.001.
- Total final = total con intereses − cashback.

Las transacciones se conservan en la sesión del navegador mientras esté activa. El archivo de texto se genera al pulsar el botón de descarga.
