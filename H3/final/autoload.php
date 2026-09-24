<?php
declare(strict_types=1);

// Carga las clases del módulo final (sin Composer: un archivo por clase, mismo nombre).
spl_autoload_register(function (string $clase): void {
    $archivo = __DIR__ . "/{$clase}.php";
    if (is_file($archivo)) {
        require_once $archivo;
    }
});
