<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

// El cliente solo necesita el contrato, sin preguntar que capas recibe.
function mostrarRecibo(string $titulo, IRecibo $recibo): void
{
    echo $titulo . "\n" . $recibo->generar() . "\n";
}

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenDeTrabajo(
    'OT-001', $cliente, $equipo, 'No enciende',
    new DateTimeImmutable('2026-09-14')
);
$base = new ReciboBasico($orden);

// Combinacion 1: base + detalle del equipo.
mostrarRecibo('COMBINACION 1: recibo con detalle del equipo',
    new ConDetalleEquipo($base, $orden->equipo)
);

// Combinacion 2: base + detalle del equipo + recomendaciones.
// Se compone en la llamada, sin crear una clase para esta combinacion.
mostrarRecibo('COMBINACION 2: recibo con detalle y recomendaciones',
    new ConRecomendaciones(
        new ConDetalleEquipo($base, $orden->equipo),
        'Evitar liquidos y mantener libres las rejillas de ventilacion.'
    )
);
