# H2 — Diagrama de clases con SOLID aplicado (antes / después)

**Estudiante:** Richard Cuellar Rojas · **Variante 6:** Taller y soporte técnico

---

## 1. ANTES — mi diagrama del H1, con sus problemas a la vista

Es el mismo diagrama del H1, sin retoques. Solo le agregué notas donde están los problemas (P1 a P6).

```mermaid
classDiagram
    direction LR

    class Usuario {
        -int id
        -string nombreCompleto
        -string usuario
        -string contrasenaHash
        -string cargo
        -bool activo
        +iniciarSesion(usuario, contrasena) bool
        +cambiarContrasena(actual, nueva) void
        +registrarOrden(datos) OrdenDeTrabajo
        +diagnosticar(orden, detalle) void
        +registrarSolucion(orden, datos) void
        +entregarEquipo(orden) void
        +venderProductos(items) Venta
        +registrarGasto(datos) Gasto
        +crearUsuario(datos) Usuario
        +verReporte(tipo, desde, hasta) array
    }
    class Gerente
    class Secretaria
    class Tecnico {
        -string especialidad
    }
    Usuario <|-- Gerente
    Usuario <|-- Secretaria
    Usuario <|-- Tecnico

    class Cliente {
        -int id
        -string nombreCompleto
        -string celular1
        -string celular2
        -string carnet
        +buscar(texto) Cliente[]
    }
    class Equipo {
        -int id
        -string tipo
        -string marca
        -string modelo
        -string procesador
        -int ramGB
        -int hddGB
        -int ssdGB
        -string bateria
        +validar() bool
    }
    class OrdenDeTrabajo {
        -string numeroRecibo
        -Date fechaIngreso
        -string[] motivos
        -string estadoFisico
        -string[] fotos
        -string estado
        -string diagnostico
        -string tipoFalla
        -float costoDiagnostico
        -string trabajosRealizados
        -float costoManoObra
        -int garantiaDias
        -Date fechaAlta
        -Date fechaEntrega
        +registrar() void
        +cambiarEstado(nuevoEstado) void
        +agregarRepuesto(producto, cantidad) void
        +calcularTotal() float
        +cobrar(metodo, monto) Pago
        +notificarSecretaria() void
        +imprimirRecibo() void
        +guardar() void
        +buscarPorRecibo(numero) OrdenDeTrabajo
    }
    class DetalleRepuesto {
        -int cantidad
        -float precioUnitario
    }
    class Producto {
        -string codigo
        -string nombre
        -string categoria
        -float costoCompra
        -float precioVenta
        -int stock
        +descontarStock(cantidad) void
    }
    class Venta {
        -int id
        -Date fecha
        -float total
        -string metodoPago
        +calcularTotal() float
    }
    class DetalleVenta {
        -int cantidad
        -float precioUnitario
    }
    class Pago {
        -int id
        -Date fecha
        -float monto
        -string metodo
        -string concepto
        -string referenciaQR
        +procesar() bool
    }
    class Gasto {
        -int id
        -Date fecha
        -string categoria
        -float monto
        -string descripcion
    }
    class Entrega {
        -Date fecha
        -string verificacion
        -string nroCarnet
        -bool firmado
    }
    class Notificacion {
        -int id
        -Date fecha
        -string mensaje
        -bool leida
    }
    class RegistroAuditoria {
        -Date fecha
        -string accion
        -string entidad
        -string detalle
    }
    class Reporte {
        +trabajosPorTecnico(tecnico, desde, hasta) array
        +equiposRegistrados(periodo) array
        +cierreDeCaja(fecha) array
        +balance(desde, hasta) array
    }

    Cliente "1" --> "*" OrdenDeTrabajo : deja
    OrdenDeTrabajo "1" *-- "1" Equipo : recibe
    Usuario "1" --> "*" OrdenDeTrabajo : registra
    Tecnico "0..1" --> "*" OrdenDeTrabajo : atiende
    OrdenDeTrabajo "1" *-- "*" DetalleRepuesto : usa
    DetalleRepuesto "*" --> "1" Producto
    Venta "1" *-- "1..*" DetalleVenta
    DetalleVenta "*" --> "1" Producto
    OrdenDeTrabajo "1" --> "0..*" Pago : se cobra
    Venta "1" --> "1" Pago
    Usuario "1" --> "*" Venta : vende
    Usuario "1" --> "*" Pago : cobra
    Usuario "1" --> "*" Gasto : registra
    OrdenDeTrabajo "1" --> "0..1" Entrega
    OrdenDeTrabajo "1" --> "*" Notificacion : genera
    Usuario "1" --> "*" RegistroAuditoria : deja rastro
    Reporte ..> OrdenDeTrabajo : lee
    Reporte ..> Pago : lee
    Reporte ..> Gasto : lee

    note for OrdenDeTrabajo "P1 CLASE GORDA: datos + estados + cálculo + cobro + avisos + impresión + SQL<br/>P2 cambiarEstado() es un switch gigante<br/>P5 new ConexionMySQL() / new Notificacion() / new ApiQrBanco() adentro"
    note for Equipo "P3 validar() hace switch(tipo): LAPTOP / PC"
    note for Usuario "P4 Tecnico y Secretaria heredan crearUsuario(), venderProductos()... y lanzan 'No permitido'<br/>P4 puede(accion) es un switch(cargo)"
    note for Producto "P6 cualquiera que usa Producto ve costoCompra, incluida la secretaria"
    note for Pago "P3 procesar() hace switch(metodo): EFECTIVO / QR"
```


### Diagnóstico

| # | Problema | Dónde duele en el taller | Principio violado |
|---|---|---|---|
| P1 | `OrdenDeTrabajo` hace de todo: guarda datos, cambia estados, calcula el total, cobra, avisa, imprime y escribe en MySQL. | Si cambia el formato del recibo impreso hay que abrir la misma clase que controla los estados y el dinero. | **SRP** |
| P2 | `cambiarEstado()` con `switch` sobre el estado. | Cada estado nuevo (por ejemplo, "esperando repuesto") obliga a editar el `switch` y arriesga romper los demás. | **OCP** |
| P3 | `switch(tipo)` en `Equipo.validar()` y `switch(metodo)` en `Pago.procesar()`. | Un tipo de equipo nuevo (All-in-one) o un segundo banco para QR obliga a tocar clases que ya funcionan. | **OCP** |
| P4 | `Tecnico` y `Secretaria` heredan métodos que no pueden usar y los anulan con `throw`. | No puedo usar un `Tecnico` donde se espera un `Usuario` sin que explote. Y cuando el gerente **cambia el cargo** de alguien, el objeto no puede cambiar de clase: habría que borrar el usuario y perder su historial. | **LSP** (y ISP) |
| P5 | `new ConexionMySQL()`, `new ApiQrBanco()`, `new Notificacion()` dentro de las clases de negocio. | La lógica del taller queda atada a MySQL y a un banco concreto; no se puede probar sin base de datos ni cambiar de banco. | **DIP** |
| P6 | Una sola clase `Producto` con `costoCompra` para todos los que la usan. | La pantalla de la secretaria tiene a mano el costo de compra aunque la regla RN13 dice que solo ve el precio de venta. | **ISP** |

---

## 2. DESPUÉS — SOLID aplicado

Es un solo modelo, pero lo dibujo en **dos vistas** para que se pueda leer: (A) dominio y seguridad, (B) servicios y contratos.

### Vista A — Dominio y seguridad

```mermaid
classDiagram
    direction TB

    class Usuario {
        -int id
        -string nombreCompleto
        -string usuario
        -string contrasenaHash
        -bool activo
        +cambiarContrasena(actual, nueva) void
        +asignarCargo(PoliticaDeCargo cargo) void
        +puede(Accion accion, contexto) bool
    }
    class PoliticaDeCargo {
        <<interface>>
        +nombre() string
        +puede(Accion accion, contexto) bool
    }
    class CargoGerente {
        +puede(Accion accion, contexto) bool
    }
    class CargoSecretaria {
        +puede(Accion accion, contexto) bool
    }
    class CargoTecnico {
        +puede(Accion accion, contexto) bool
    }
    Usuario o--> "1" PoliticaDeCargo : cargo
    PoliticaDeCargo <|.. CargoGerente
    PoliticaDeCargo <|.. CargoSecretaria
    PoliticaDeCargo <|.. CargoTecnico

    class Cliente {
        -string nombreCompleto
        -string celular1
        -string celular2
        -string carnet
        +coincideCon(texto) bool
    }
    class Equipo {
        <<abstract>>
        -string marca
        -string modelo
        -string procesador
        -int ramGB
        -int hddGB
        -int ssdGB
        +tipo()* string
        +notasDeRecepcion() string[]
    }
    class Laptop {
        -string bateria
        +tipo() string
        +notasDeRecepcion() string[]
    }
    class PcEscritorio {
        +tipo() string
    }
    Equipo <|-- Laptop
    Equipo <|-- PcEscritorio

    class EstadoOrden {
        <<enumeration>>
        RECIBIDA
        EN_DIAGNOSTICO
        ESPERANDO_AUTORIZACION
        EN_REPARACION
        SOLUCIONADA
        SIN_SOLUCION
        ENTREGADA
        +puedePasarA(EstadoOrden destino) bool
    }
    class OrdenDeTrabajo {
        -string numeroRecibo
        -Date fechaIngreso
        -string[] motivos
        -string[] observacionesFisicas
        -string[] fotos
        -bool preAutorizada
        +tomar(tecnico) void
        +registrarDiagnostico(Diagnostico d) void
        +registrarRespuestaCliente(bool autoriza) void
        +registrarSolucion(InformeSolucion i) void
        +cerrarSinSolucion(motivo) void
        +entregar(Entrega e) void
        +diasEnTaller(hoy) int
    }
    class Diagnostico {
        -string detalle
        -string tipoTrabajo
        -float presupuesto
        -float costoDiagnostico
    }
    class InformeSolucion {
        -string[] trabajosRealizados
        -float manoDeObra
        -int garantiaDias
    }
    class RepuestoUsado {
        -string codigoProducto
        -int cantidad
        -float precioUnitario
    }
    class Entrega {
        -Date fecha
        -string verificacion
        -string nroCarnet
        -string entregadoPor
    }

    Cliente "1" --> "*" OrdenDeTrabajo : deja
    OrdenDeTrabajo "*" --> "1" Equipo : sobre
    OrdenDeTrabajo --> EstadoOrden : estado
    OrdenDeTrabajo "1" *-- "0..1" Diagnostico
    OrdenDeTrabajo "1" *-- "0..1" InformeSolucion
    InformeSolucion "1" *-- "*" RepuestoUsado
    OrdenDeTrabajo "1" *-- "0..1" Entrega
    OrdenDeTrabajo "*" --> "1" Usuario : registradaPor
    OrdenDeTrabajo "*" --> "0..1" Usuario : tecnico
```

### Vista B — Servicios y contratos (las flechas apuntan a interfaces)

```mermaid
classDiagram
    direction LR

    class ServicioRecepcion {
        +registrarOrden(datos, Usuario u) OrdenDeTrabajo
        +buscar(CriterioBusqueda c) OrdenDeTrabajo[]
    }
    class ServicioTaller {
        +tomar(numeroRecibo, Usuario tecnico) void
        +diagnosticar(numeroRecibo, Diagnostico d, Usuario tecnico) void
        +darDeAlta(numeroRecibo, InformeSolucion i, Usuario tecnico) void
    }
    class ServicioCaja {
        +cobrarYEntregar(numeroRecibo, Entrega e, MetodoDePago m, Usuario u) void
        +venderProductos(items, MetodoDePago m, Usuario u) void
        +registrarGasto(Gasto g, Usuario u) void
    }
    class CalculadoraDeCobro {
        +cobroDe(OrdenDeTrabajo o) float
    }
    class GeneradorDeRecibo {
        +reciboDeIngreso(OrdenDeTrabajo o) string
        +reciboDeEntrega(OrdenDeTrabajo o) string
    }

    class RepositorioOrdenes {
        <<interface>>
        +guardar(OrdenDeTrabajo o) void
        +porRecibo(numero) OrdenDeTrabajo
        +buscar(CriterioBusqueda c) OrdenDeTrabajo[]
    }
    class Notificador {
        <<interface>>
        +avisar(OrdenDeTrabajo o, string evento) void
    }
    class MetodoDePago {
        <<interface>>
        +cobrar(float monto, string concepto) ComprobantePago
    }
    class RegistroDeAuditoria {
        <<interface>>
        +registrar(Usuario u, string accion, string detalle) void
    }
    class CatalogoDeVenta {
        <<interface>>
        +buscar(texto) ProductoEnVenta[]
        +darDeAlta(datosProducto) void
        +descontarStock(codigo, cantidad) void
    }
    class AdministracionDeInventario {
        <<interface>>
        +costoDeCompra(codigo) float
        +cambiarPrecio(codigo, nuevoPrecio) void
        +editar(codigo, datos) void
        +eliminar(codigo) void
        +capitalInvertido() float
    }

    class RepositorioOrdenesMySQL
    class BandejaDeAvisos
    class PagoEfectivo
    class PagoQR
    class BitacoraMySQL
    class ServicioInventario

    ServicioRecepcion --> RepositorioOrdenes
    ServicioRecepcion --> RegistroDeAuditoria
    ServicioTaller --> RepositorioOrdenes
    ServicioTaller --> Notificador
    ServicioTaller --> CatalogoDeVenta : descuenta repuestos
    ServicioTaller --> RegistroDeAuditoria
    ServicioCaja --> RepositorioOrdenes
    ServicioCaja --> CalculadoraDeCobro
    ServicioCaja --> MetodoDePago
    ServicioCaja --> CatalogoDeVenta
    ServicioCaja --> GeneradorDeRecibo
    ServicioCaja --> RegistroDeAuditoria

    RepositorioOrdenes <|.. RepositorioOrdenesMySQL
    Notificador <|.. BandejaDeAvisos
    MetodoDePago <|.. PagoEfectivo
    MetodoDePago <|.. PagoQR
    RegistroDeAuditoria <|.. BitacoraMySQL
    CatalogoDeVenta <|.. ServicioInventario
    AdministracionDeInventario <|.. ServicioInventario
```

> Pantalla de la secretaria → depende solo de `CatalogoDeVenta`. Pantalla del gerente → depende de `AdministracionDeInventario`. Además, cada servicio consulta `usuario.puede(accion)` antes de actuar: la regla se valida en el servidor, no solo ocultando botones.

---

## 3. Qué cambié y qué principio me pidió cada cambio

1. **SRP** — Partí la `OrdenDeTrabajo` gorda: ahora solo guarda sus datos y sus reglas de estado; el cobro pasó a `CalculadoraDeCobro`, la persistencia a `RepositorioOrdenes`, los avisos a `Notificador`, la impresión a `GeneradorDeRecibo`, y el diagnóstico, la solución y la entrega son clases propias.
2. **OCP** — El `switch(tipo)` de `Equipo` se volvió la jerarquía `Laptop` / `PcEscritorio` (la regla de la batería vive en `Laptop`); el `switch` de estados, una tabla de transiciones en `EstadoOrden`; el `switch(metodo)` de pago, la interfaz `MetodoDePago`. Un tipo, estado o banco nuevo es una clase o una fila nueva, no un `case` más.
3. **LSP** — `Tecnico` y `Secretaria` ya no heredan métodos que anulan con `throw`: `Usuario` **tiene** un cargo (`PoliticaDeCargo`) y cualquier cargo cumple el contrato completo. De paso, el gerente puede cambiar el cargo de alguien sin borrar al usuario ni su historial.
4. **ISP** — El inventario se partió en `CatalogoDeVenta` (precio de venta, alta, stock: secretaria y técnico) y `AdministracionDeInventario` (costo, rebajas, editar, eliminar: gerente); quien no debe ver el costo ni siquiera depende de un método que lo devuelva.
5. **DIP** — Los `new ConexionMySQL()`, `new ApiQrBanco()` y `new Notificacion()` incrustados desaparecieron: los servicios reciben interfaces por constructor y MySQL, el banco y la bandeja quedan como detalles intercambiables (y probables sin base de datos).

