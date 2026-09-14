<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';
require_once __DIR__ . '/NotificadorWhatsApp.php';

// El cliente usa exclusivamente el contrato del taller.
function avisarEquipoListo(OrdenDeTrabajo $orden, INotificador $notificador): void
{
    if ($orden->estadoActual() !== 'lista') {
        throw new DomainException('Solo se avisa equipo listo cuando la orden esta lista.');
    }
    $notificador->enviar(new Aviso('EQUIPO_LISTO', $orden->cliente->celularPrincipal,
        'Su equipo del recibo ' . $orden->numeroRecibo . ' esta listo para recoger.',
        new DateTimeImmutable('2026-09-17T10:00:00-04:00')));
}

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenDeTrabajo('OT-A-001', $cliente, $equipo, 'No enciende', new DateTimeImmutable('2026-09-14'));
$orden->aplicar('DIAGNOSTICO_LISTO');
$orden->aplicar('CLIENTE_AUTORIZA');
$orden->aplicar('REPARACION_TERMINADA');
$api = new ApiWhatsAppSimulada();
$notificador = new NotificadorWhatsApp($api);
avisarEquipoListo($orden, $notificador);
echo "ADAPTER: envio simulado, sin mensajes reales.\n";
echo json_encode($api->mensajesRegistrados(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
