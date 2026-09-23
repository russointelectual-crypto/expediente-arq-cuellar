# Parte 1 — El plano: diagrama de clases del comedor "Sabor Andino"

**Autor:** Richard Cuellar · Ingeniería de Sistemas · Arquitectura de Software
**Variante:** A — Comedor Universitario

Diagrama construido **desde los requerimientos** (no desde el esqueleto), con la receta de 4 pasos.

---

## Paso 1 — Sustantivos (candidatos a clase o atributo)

> El sistema registra **pedidos** de **menú**: cada **estudiante** pide uno o más menús de un **tipo** (estándar, vegetariano o beca), el pedido pasa por **estados** (solicitado → preparado → entregado / anulado), y el **comedor** tiene dos **roles**: el **cajero** registra pedidos y el **administrador** ajusta **precios** y anula pedidos. Cuando un pedido queda preparado, el estudiante debe recibir un **aviso**. A **fin de semana**, la administración pide un **reporte** de menús vendidos por tipo.

Sustantivos: sistema, pedido, menú, estudiante, cantidad ("uno o más"), tipo, estado, comedor, rol, cajero, administrador, precio, aviso, fin de semana, reporte.

## Paso 2 — Verbos (candidatos a método o relación)

| Verbo del requerimiento | Quién lo hace | Se convierte en |
|---|---|---|
| registra pedidos | Cajero | `Cajero.registrarPedido()` + asociación Cajero → Pedido |
| pide menús | Estudiante | asociación Estudiante → Pedido |
| pasa por estados | Pedido | `marcarPreparado()`, `marcarEntregado()`, `anular()` |
| ajusta precios | Administrador | `Administrador.ajustarPrecio()` → `Menu.ajustarPrecio()` |
| anula pedidos | Administrador | `Administrador.anularPedido()` → `Pedido.anular()` |
| recibir un aviso | Estudiante (vía observador) | `IObservadorPedido.estadoCambiado()` → **Observer** (ver `patron.md`) |
| pide un reporte | Administrador | `Administrador.solicitarReporte()` → `ReporteSemanal.generar()` |

## Paso 3 — Filtro (qué queda como clase y qué no)

| Candidato | Decisión | Motivo |
|---|---|---|
| Pedido, Estudiante, Menu, Cajero, Administrador, ReporteSemanal | **Clase** | Tienen datos propios y comportamiento |
| sistema, comedor | **Descartado** | Es el sistema completo, no una pieza de él |
| rol | **Generalización** → `Usuario` abstracto | Cajero y Administrador comparten identidad y login; difieren en permisos |
| tipo | **Enumeración** `TipoMenu` | Conjunto cerrado de valores: ESTANDAR, VEGETARIANO, BECA |
| estado | **Enumeración** `EstadoPedido` | SOLICITADO, PREPARADO, ENTREGADO, ANULADO |
| precio | **Atributo** de `Menu` | Lo ajusta el administrador, por lo tanto es un **dato**, no una constante del código |
| cantidad | **Atributo** de `Pedido` | "uno o más menús de un tipo" → `cantidad ≥ 1` |
| fin de semana | **Atributo** del reporte (`desde`, `hasta`) | Es el período del reporte, no una entidad |
| aviso | **Interfaz** `IObservadorPedido` + `AvisoAlEstudiante` | Es una reacción a un cambio de estado → patrón Observer |

## Paso 4 — Relaciones → diagrama

```mermaid
---
title: "Comedor Sabor Andino — Diagrama de clases · Autor: Richard Cuellar"
---
classDiagram
    direction TB

    class Usuario {
        <<abstract>>
        #String nombre
        #String usuario
    }
    class Cajero {
        +registrarPedido(Estudiante e, Menu m, int cantidad) Pedido
    }
    class Administrador {
        +ajustarPrecio(Menu m, decimal nuevoPrecio) void
        +anularPedido(Pedido p) void
        +solicitarReporte(Date desde, Date hasta) ReporteSemanal
    }

    class Estudiante {
        -String codigo
        -String nombre
        -String correo
    }

    class Menu {
        -TipoMenu tipo
        -decimal precio
        +getPrecio() decimal
        +ajustarPrecio(decimal nuevoPrecio) void
    }

    class TipoMenu {
        <<enumeration>>
        ESTANDAR
        VEGETARIANO
        BECA
    }

    class Pedido {
        -int id
        -DateTime fecha
        -int cantidad
        -decimal precioUnitario
        -EstadoPedido estado
        -List~IObservadorPedido~ observadores
        +calcularTotal() decimal
        +marcarPreparado() void
        +marcarEntregado() void
        +anular() void
        +suscribir(IObservadorPedido o) void
        +desuscribir(IObservadorPedido o) void
        -notificar() void
    }

    class EstadoPedido {
        <<enumeration>>
        SOLICITADO
        PREPARADO
        ENTREGADO
        ANULADO
    }

    class IObservadorPedido {
        <<interface>>
        +estadoCambiado(Pedido p) void
    }
    class AvisoAlEstudiante {
        -INotificador canal
        +estadoCambiado(Pedido p) void
    }
    class INotificador {
        <<interface>>
        +enviar(String mensaje) void
    }
    class CorreoUniversitario {
        +enviar(String mensaje) void
    }

    class ReporteSemanal {
        -Date desde
        -Date hasta
        -Map~TipoMenu,int~ menusVendidosPorTipo
        +generar(List~Pedido~ pedidos) void
    }

    Usuario <|-- Cajero
    Usuario <|-- Administrador

    Estudiante "1" -- "0..*" Pedido : realiza
    Cajero "1" --> "0..*" Pedido : registra
    Pedido "0..*" --> "1" Menu : es de
    Menu --> TipoMenu
    Pedido --> EstadoPedido

    Administrador ..> Menu : ajusta precio
    Administrador ..> Pedido : anula
    Administrador ..> ReporteSemanal : solicita
    ReporteSemanal ..> Pedido : suma cantidad por tipo, sin ANULADOS

    Pedido o-- "0..*" IObservadorPedido : notifica
    IObservadorPedido <|.. AvisoAlEstudiante
    AvisoAlEstudiante ..> Estudiante : avisa
    AvisoAlEstudiante --> INotificador : usa
    INotificador <|.. CorreoUniversitario

    note for Pedido "SUJETO del patrón Observer (ver patron.md).<br>precioUnitario se congela al registrar:<br>si el admin ajusta el precio después,<br>los pedidos viejos no cambian su total."
    note for AvisoAlEstudiante "OBSERVADOR concreto: reacciona<br>solo cuando el estado pasa a PREPARADO.<br>Usa INotificador (la abstracción<br>curada en refactor.cs)."
```

### Complemento: ciclo de vida del pedido (justifica los métodos de `Pedido`)

```mermaid
---
title: "Estados de Pedido · Richard Cuellar"
---
stateDiagram-v2
    [*] --> SOLICITADO : Cajero.registrarPedido()
    SOLICITADO --> PREPARADO : marcarPreparado() → aviso al estudiante
    PREPARADO --> ENTREGADO : marcarEntregado()
    SOLICITADO --> ANULADO : Administrador.anularPedido()
    PREPARADO --> ANULADO : Administrador.anularPedido()
    ENTREGADO --> [*]
    ANULADO --> [*]
```

## Decisiones que conviene defender

- **`Menu` guarda el precio como dato** porque el administrador lo ajusta; en el esqueleto el precio vive en un `switch` (ver violación OCP en `detecciones.md`).
- **`Pedido.precioUnitario`** es una copia del precio al momento de registrar: el reporte semanal y los totales históricos no se alteran cuando el administrador cambia precios.
- **`Usuario` abstracto** modela "el comedor tiene dos roles" sin duplicar atributos en Cajero y Administrador.
- **El reporte suma `cantidad` (menús, no pedidos) por `TipoMenu` y excluye los `ANULADO`**: un pedido de 3 menús son 3 menús vendidos, y un pedido anulado no es una venta.
- **El aviso no es una llamada fija dentro de `Pedido`**: `Pedido` solo conoce la interfaz `IObservadorPedido`. Ese es el patrón de la Parte 3, y está dentro del diagrama (coherencia, Parte 5).
