<?php
declare(strict_types=1);

// Proveedor de prueba con una interfaz distinta al contrato de nuestro taller.
// No usa Internet, credenciales ni envia mensajes reales.
class ApiWhatsAppSimulada
{
    private array $mensajes = [];

    public function sendMessage(array $payload): void
    {
        $this->mensajes[] = $payload;
    }

    public function mensajesRegistrados(): array
    {
        return $this->mensajes;
    }
}
