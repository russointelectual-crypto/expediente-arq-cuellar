<?php
declare(strict_types=1);

/**
 * con-factory — la base + FACTORY METHOD para crear el equipo según su tipo.
 * Ejecutar:  php h3/con-factory/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'OrdenDeTrabajo',
          'RecepcionDeEquipo', 'RecepcionLaptop', 'RecepcionPcEscritorio'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}

// El formulario de recepción tiene un <select name="tipo">. Cada opción apunta a SU creador.
// Agregar un tipo nuevo = una clase Equipo + una clase Recepcion + una línea aquí. No hay if/switch.
$recepciones = [
    'laptop' => new RecepcionLaptop(),
    'pc'     => new RecepcionPcEscritorio(),
];

function recepcionPara(array $recepciones, string $tipo): RecepcionDeEquipo
{
    return $recepciones[$tipo] ?? throw new InvalidArgumentException("El taller no recibe equipos de tipo '{$tipo}'.");
}

$hoy   = new DateTimeImmutable('2026-09-10 09:00');
$juan  = new Cliente('Juan Carlos Mamani Quispe', '71234567', '65432109');
$rosa  = new Cliente('Rosa Elena Gutiérrez Paz', '69876543');

// Lo que llegaría por $_POST desde el formulario de la secretaria.
$formularios = [
    ['R-000201', $juan, [
        'tipo' => 'laptop', 'marca' => 'Asus', 'modelo' => 'VivoBook X515', 'procesador' => 'Core i3-1115G4',
        'ram' => '4', 'hdd' => '1000', 'bateria' => Laptop::BATERIA_EXTERNA,
        'motivos' => ['UPGRADE_SSD', 'UPGRADE_RAM'], 'fotos' => ['fotos/R-000201-1.jpg'], 'preAutorizada' => true,
        'descripcion' => 'Quiere pasar a SSD y subir a 8 GB',
    ]],
    ['R-000202', $rosa, [
        'tipo' => 'pc', 'marca' => 'HP', 'modelo' => 'ProDesk 400 G6', 'procesador' => 'Core i5-9500',
        'ram' => '8', 'ssd' => '256',
        'motivos' => ['FALLA_SOFTWARE'], 'fotos' => ['fotos/R-000202-1.jpg'], 'observaciones' => ['Tapa lateral rayada'],
        'descripcion' => 'Se reinicia sola al abrir Windows',
    ]],
];

echo "== Recepción con Factory Method ==" . PHP_EOL;
foreach ($formularios as [$recibo, $cliente, $form]) {
    $orden = recepcionPara($recepciones, $form['tipo'])->recibir($recibo, $cliente, $form, 'ana', $hoy);
    echo $orden->resumen() . PHP_EOL;
    echo '    creado por ' . get_class(recepcionPara($recepciones, $form['tipo'])) . ' → ' . get_class($orden->equipo) . PHP_EOL;
    foreach ($orden->equipo->notasDeRecepcion() as $nota) {
        echo "    nota: {$nota}" . PHP_EOL;
    }
}

echo PHP_EOL . "== Lo que el creador no deja pasar ==" . PHP_EOL;
$malos = [
    'Laptop sin indicar la batería' => ['laptop', ['marca' => 'Dell', 'modelo' => 'Inspiron 3511', 'procesador' => 'Core i5', 'ram' => '8',
        'motivos' => ['NO_ENCIENDE'], 'fotos' => ['f.jpg']]],
    'Tipo que el taller no recibe'  => ['tablet', []],
];
foreach ($malos as $caso => [$tipo, $form]) {
    try {
        recepcionPara($recepciones, $tipo)->recibir('R-000299', $juan, $form, 'ana', $hoy);
        echo "[!] {$caso}: se aceptó" . PHP_EOL;
    } catch (Throwable $e) {
        echo "[rechazado] {$caso}: {$e->getMessage()}" . PHP_EOL;
    }
}
