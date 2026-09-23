# Parte 2 — La cirugía SOLID: detecciones

**Estudiante:** Richard Cuellar Rojas · Comedor "Sabor Andino"

| # | Principio | Dónde (esqueleto-A.cs) | Por qué es violación |
|---|---|---|---|
| 1 | **S — Responsabilidad Única (SRP)** | `GestorDePedidos.ProcesarPedido`, líneas 9–38 | Un solo método calcula el precio (11–27), guarda en BD (29–30), imprime el vale (32–34) y manda el correo (36–37): tiene **4 razones para cambiar** (tarifas, base de datos, formato del vale, canal de aviso). |
| 2 | **O — Abierto/Cerrado (OCP)** | `switch (tipoMenu)`, líneas 12–26 | Agregar un tipo de menú nuevo (p. ej. "dieta") o cambiar un precio obliga a **modificar y recompilar** `GestorDePedidos`; además el administrador no puede ajustar precios, porque viven en el código, y el `default` cobra 12 Bs en silencio si llega un tipo mal escrito. |
| 3 | **D — Inversión de Dependencias (DIP)** | `new BaseDeDatosComedor()` (línea 29) y `new CorreoUniversitario()` (línea 36) | El módulo de alto nivel (la regla del pedido) crea y depende de **clases concretas** de bajo nivel: no se puede cambiar la BD ni el canal de aviso, ni probar el gestor sin escribir en la BD real y sin mandar correos. |

## Cómo se cura cada una

| # | Cura |
|---|---|
| 1 · SRP | Separar en `CalculadoraDePrecios`, `RepositorioPedidos`, `ImpresoraDeVales` y el aviso; `GestorDePedidos` solo coordina. |
| 2 · OCP | Sacar los precios del `switch`: tabla de `Menu` con precio como **dato** (lo ajusta el administrador, ver `diagrama.md`), o polimorfismo por tipo si un día las reglas difieren. |
| 3 · DIP | Introducir las abstracciones `IRepositorioPedidos` e `INotificador` e **inyectarlas por constructor**. |

## La curada en código: **#3 DIP** → `refactor.cs`

Elegí DIP porque es la que **habilita las otras dos curas y la Parte 3**:

- Con `INotificador` inyectado, el aviso al estudiante (patrón Observer de `patron.md`) se conecta sin tocar el gestor: `AvisoAlEstudiante` usa esa misma interfaz.
- Con `IRepositorioPedidos`, el gestor se prueba con un repositorio en memoria (se demuestra en el `Demo` de `refactor.cs`).

En `refactor.cs` las violaciones #1 y #2 **quedan a propósito** (marcadas con comentarios) para que la cura de DIP se vea aislada, como pide la consigna: "UNA de las tres curada en código".
