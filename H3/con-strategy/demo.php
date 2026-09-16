<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

// Datos ilustrativos: diagnostico Bs 50, mano de obra Bs 150, repuestos Bs 200.
$datos = new DatosCobro(5000, 15000, 20000);
$caja = new ServicioCaja(new CobroReparacion());

function mostrarTotal(string $caso, ServicioCaja $caja, DatosCobro $datos): void
{
    echo $caso . ': Bs ' . number_format($caja->calcularTotal($datos) / 100, 2, '.', '') . "\n";
}

echo "STRATEGY: mismos datos, distinta politica en la misma caja.\n";
mostrarTotal('Reparacion', $caja, $datos);
$caja->cambiarPolitica(new CobroSoloDiagnostico());
mostrarTotal('Solo diagnostico', $caja, $datos);
$caja->cambiarPolitica(new CobroEnGarantia());
mostrarTotal('Garantia aprobada', $caja, $datos);
