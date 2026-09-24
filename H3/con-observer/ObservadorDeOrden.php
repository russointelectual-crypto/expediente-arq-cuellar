<?php
declare(strict_types=1);

/** OBSERVER — el contrato de cualquiera que quiera enterarse de lo que pasa con una orden. */
interface ObservadorDeOrden
{
    public function alOcurrir(EventoOrden $evento): void;
}
