<?php
declare(strict_types=1);

require_once __DIR__ . '/INotificador.php';
require_once __DIR__ . '/ApiWhatsAppSimulada.php';

class NotificadorWhatsApp implements INotificador
{
    public function __construct(private ApiWhatsAppSimulada $api) {}

    public function enviar(Aviso $aviso): void
    {
        $this->api->sendMessage([
            'to' => $aviso->destinatario,
            'body' => '[' . $aviso->tipo . '] ' . $aviso->mensaje,
            'sent_at' => $aviso->fecha->format(DateTimeInterface::ATOM)
        ]);
    }
}
