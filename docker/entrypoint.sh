#!/bin/bash
set -e

echo "=== Iniciando servicios Bluetooth ==="

# Limpiar pid files previos
rm -f /run/dbus/pid
rm -f /run/bluetooth/pid

# Crear directorios necesarios
mkdir -p /run/dbus /run/bluetooth

# Iniciar D-Bus system daemon
echo "Iniciando D-Bus..."
dbus-daemon --system --nofork --nopidfile &
DBUS_PID=$!
sleep 1

if ! kill -0 $DBUS_PID 2>/dev/null; then
    echo "ERROR: D-Bus no pudo iniciarse"
    exit 1
fi
echo "D-Bus OK"

# Iniciar Bluetooth daemon (no fallar si no hay adaptador)
echo "Iniciando bluetoothd..."
/usr/libexec/bluetooth/bluetoothd --noplugin=audio 2>/dev/null &
BT_PID=$!
sleep 2

if kill -0 $BT_PID 2>/dev/null; then
    echo "bluetoothd OK"
    HAS_BLUETOOTH=true
else
    echo "WARN: bluetoothd no pudo iniciarse (sin adaptador Bluetooth)"
    HAS_BLUETOOTH=false
fi

# Activar adaptador Bluetooth si existe
if [ "$HAS_BLUETOOTH" = true ] && command -v hciconfig &>/dev/null; then
    ADAPTER=$(hciconfig 2>/dev/null | grep -o 'hci[0-9]' | head -1)
    if [ -n "$ADAPTER" ]; then
        echo "Activando adaptador $ADAPTER..."
        hciconfig $ADAPTER up 2>/dev/null || true
        echo "Adaptador $ADAPTER activado"
    else
        echo "WARN: No se encontró adaptador Bluetooth (hci*)"
    fi
fi

echo "=== Bluetooth listo ==="

# Reverb (WebSocket) para el tiempo real de pedidos. Corre como www-data en
# segundo plano; si falla, la app sigue funcionando con el polling de respaldo.
echo "Iniciando Reverb (puerto 8080)..."
su -s /bin/sh www-data -c "php /var/www/artisan reverb:start --host=0.0.0.0 --port=8080 --debug" &

# Ejecutar comando principal (php-fpm)
exec "$@"
