# Parcial 2 Variante B — Gimnasio Fuerza Andina

## P2.1 Elegir y justificar

### Situacion 1 — Observer

Aplico Observer porque el vencimiento de una membresia debe notificarse a varios interesados.
El modulo de socios publica el evento y cada suscriptor responde mediante el mismo contrato.
Sin este patron, agregar promociones u otro interesado obliga a modificar el modulo de socios y aumenta su acoplamiento.
No elijo Strategy porque aqui se avisa a varios receptores; no se selecciona un algoritmo de calculo.

### Situacion 2 — Strategy

Aplico Strategy para encapsular las tarifas de mañana, noche y fin de semana en clases con un contrato comun.
Cobros y cotizaciones pueden utilizar las mismas estrategias y elegir la correspondiente a la franja.
Sin este patron, los condicionales duplicados pueden quedar desactualizados y producir precios diferentes al cambiar las reglas.
No elijo Factory Method porque el problema principal es variar el calculo, no delegar la creacion de objetos.

### Situacion 3 — Adapter

Aplico Adapter para exponer un contrato de cobro propio del gimnasio y traducirlo al SDK del proveedor.
El adaptador convierte los montos a centavos y resuelve el token del cliente mediante una correspondencia con el proveedor.
Sin esta frontera, las llamadas y formatos externos se dispersan por el sistema y cambiar de pasarela exige modificar varios modulos.
No elijo Builder porque no se necesita construir un objeto por pasos, sino compatibilizar interfaces distintas.

