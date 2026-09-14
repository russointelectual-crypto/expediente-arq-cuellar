<?php
declare(strict_types=1);

require_once __DIR__ . '/Aviso.php';

interface INotificador
{
    public function enviar(Aviso $aviso): void;
}
