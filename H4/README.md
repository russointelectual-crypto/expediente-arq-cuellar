# H4 — decisión arquitectónica del sistema de órdenes y cobros

## C4 nivel 1 — contexto

El sistema registra la recepción, diagnóstico, reparación, entrega y cobro de equipos en un taller de soporte técnico. El pago puede realizarse por QR o en efectivo. El proveedor QR y el servicio de mensajería son sistemas externos; el efectivo se registra dentro de la caja del taller.

```mermaid
flowchart TB
    cliente["Cliente · Persona"]
    recepcion["Recepcionista / cajero · Persona"]
    tecnico["Técnico · Persona"]
    gerente["Gerente del taller · Persona"]
    sistema["Sistema de órdenes y cobros · Sistema de software"]
    proveedorQr["Proveedor bancario de pagos QR · Sistema externo"]
    mensajeria["WhatsApp · Sistema externo"]

    cliente -->|Entrega equipo y recibe estado/cobro| recepcion
    recepcion -->|Registra orden, entrega y pago| sistema
    tecnico -->|Registra diagnóstico, reparación y estado| sistema
    gerente -->|Consulta operaciones y reportes| sistema
    sistema -->|Solicita validación del pago QR| proveedorQr
    proveedorQr -->|Devuelve confirmación| sistema
    sistema -->|Solicita aviso de equipo listo| mensajeria
    mensajeria -->|Envía notificación| cliente
```

## C4 nivel 2 — contenedores lógicos

Para el tamaño del taller se propone un monolito PHP. Los contenedores siguientes son módulos lógicos de esa aplicación, no contenedores Docker. La ubicación de los patrones se marca dentro del módulo que los usa.

```mermaid
flowchart TB
    personal["Recepcionista, técnico y gerente · Personas"]
    cliente["Cliente · Persona"]
    subgraph sistema["Sistema de órdenes y cobros · Aplicación PHP"]
        app["Módulo de órdenes · PHP · Observer: OrdenObservable publica cambios y activa avisos"]
        caja["Módulo de caja · PHP · Strategy: CobroQR o CobroEfectivo"]
        datos[("Base de datos · MySQL propuesto · clientes, órdenes, estados y pagos")]
    end
    proveedorQr["Proveedor QR bancario · Sistema externo"]
    mensajeria["WhatsApp · Sistema externo"]

    personal -->|HTTPS / interfaz web| app
    app -->|SQL| datos
    app -->|Solicita cálculo y confirmación| caja
    caja -->|HTTPS / QR| proveedorQr
    app -->|Aviso de equipo listo| mensajeria
    mensajeria -->|Mensaje| cliente
    caja -->|Total y comprobante| app
```


