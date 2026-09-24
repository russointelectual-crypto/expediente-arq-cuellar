# h3/con-singleton — Por qué mi caso NO usa Singleton

**Decisión:** no implemento Singleton. Revisé los cuatro lugares del taller donde "parece" hacer falta una instancia única y en ninguno el patrón resuelve el problema real; en dos de ellos, además, lo empeora.

## 1. El candidato más tentador: el número de recibo correlativo

Cada equipo recibe un número único (RN1) y el taller tiene **dos secretarias** (y técnicos y gerente que también registran). La tentación es un `GeneradorDeRecibos::instancia()->siguiente()`.

El problema: en PHP cada petición HTTP corre en **su propio proceso**, con su propia memoria (arquitectura *share-nothing*). El Singleton es único **dentro de una petición**, no en todo el sistema. Lo probé simulando dos secretarias que registran al mismo tiempo:

```php
final class GeneradorDeRecibos {
    private static ?GeneradorDeRecibos $instancia = null;
    private int $ultimo = 100;                     // "leído de la BD" al arrancar
    private function __construct() {}
    public static function instancia(): self { return self::$instancia ??= new self(); }
    public function siguiente(): string { return sprintf('R-%06d', ++$this->ultimo); }
}
```

```text
$ php experimento.php "Petición de ana" & php experimento.php "Petición de rosa"
Petición de rosa obtiene R-000101
Petición de ana obtiene R-000101        ← número DUPLICADO
```

Dos "únicos" generadores, dos recibos iguales: exactamente lo que la regla prohíbe. Lo que garantiza la unicidad es la **base de datos**: una columna `AUTO_INCREMENT` (o una tabla de secuencia actualizada dentro de una transacción) con restricción `UNIQUE` sobre `numero_recibo`. Eso funciona aunque haya 2 o 20 usuarios registrando a la vez.

## 2. La conexión a MySQL

Lo que se necesita es **una conexión por petición**, y eso ya lo logra el punto de composición (el `index.php` que arma los objetos): crea un `PDO` una vez y se lo pasa por constructor a los repositorios. Un `Conexion::instancia()` llamado desde cualquier clase sería volver al problema **P5 del H2**: una dependencia escondida, que rompe el **DIP** que apliqué y que me impide probar los servicios con un repositorio en memoria (como hacen todas las demos de este laboratorio).

## 3. La configuración del taller

Plazo máximo de 6 meses, costo de diagnóstico sugerido, datos que salen en el recibo. Se leen una vez al arrancar la petición y se inyectan como un objeto de solo lectura (`ConfiguracionTaller` con propiedades `readonly`). No hace falta impedir que exista otra instancia: basta con no crear otra.

## 4. El usuario que inició sesión / la bandeja de avisos

Cada usuario tiene **su** sesión (`$_SESSION`) y la bandeja de avisos vive en la base de datos, porque la tienen que ver **las dos secretarias** desde computadoras distintas. Un objeto global en memoria no llega ni a la otra computadora ni a la siguiente petición.

## Conclusión

"Que haya una sola" es una necesidad de **ciclo de vida**, y en mi sistema la resuelven dos cosas que ya están en el diseño: el **punto de composición** (crear una vez e inyectar) y la **base de datos** (unicidad real entre usuarios). El Singleton agregaría estado global y dependencias ocultas sin garantizar nada de lo que el taller necesita.

**Cuándo lo reconsideraría:** si parte del sistema pasara a ser un proceso de larga vida (un *worker* PHP CLI atendiendo una cola, por ejemplo). Aun así, preferiría que un contenedor de dependencias administre esa instancia compartida antes que un `static` escondido en la clase.
