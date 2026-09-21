<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenDeTrabajo(
    'OT-DEC-001',
    $cliente,
    $equipo,
    'No enciende',
    new DateTimeImmutable('2026-09-14')
);

echo "DECORATOR: el recibo se arma agregando capas combinables.\n";
$base = new ReciboBasico($orden);
$conDetalle = new ConDetalleEquipo($base, $orden->equipo);
$completo = new ConRecomendaciones(
    $conDetalle,
    'Evitar liquidos y mantener libres las rejillas de ventilacion.'
);
echo $completo->generar();
