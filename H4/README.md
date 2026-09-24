# H4 (parte B) — Documentación de la decisión: C4 + ADR

**Autor:** Richard Cuellar Rojas · **Variante 6:** Taller y soporte técnico


---

## C4 — Nivel 1: Contexto

Mi sistema como una sola caja, quién lo usa y con qué sistemas externos habla.

```mermaid
flowchart TB
    gerente["<b>Gerente</b><br/>[Persona · 1 o 2]<br/>Asigna cargos, administra inventario y precios,<br/>ve ingresos, egresos y la bitácora"]
    secretaria["<b>Secretaria</b><br/>[Persona · hasta 2]<br/>Registra equipos, vende accesorios,<br/>registra gastos, entrega y cobra"]
    tecnico["<b>Técnico</b><br/>[Persona · 2 o más]<br/>Toma órdenes por recibo, diagnostica,<br/>repara y da de alta"]
    cliente["<b>Cliente</b><br/>[Persona externa]<br/>Deja y recoge sus equipos;<br/>no usa el sistema"]

    sistema["<b>Sistema de Gestión del Taller</b><br/>[Sistema de software]<br/>Órdenes de trabajo, inventario, caja,<br/>avisos, reportes y bitácora"]

    banco["<b>API de cobro QR del banco</b><br/>[Sistema externo]<br/>Genera el QR y confirma el pago"]
    impresora["<b>Impresora de recibos</b><br/>[Dispositivo externo]<br/>Recibo de ingreso y de entrega"]

    gerente -->|"Administra y consulta reportes<br/>[HTTPS]"| sistema
    secretaria -->|"Registra, busca, entrega y cobra<br/>[HTTPS]"| sistema
    tecnico -->|"Registra diagnóstico y solución<br/>[HTTPS]"| sistema
    sistema -->|"Avisos: equipo listo, sin solución,<br/>plazo de 6 meses"| secretaria
    sistema -->|"Solicita QR y consulta el pago<br/>[HTTPS / JSON]"| banco
    sistema -->|"Envía recibos a imprimir"| impresora

    secretaria -.->|"Llama: equipo listo y monto"| cliente
    tecnico -.->|"Llama para que autorice el trabajo"| cliente
    cliente -.->|"Paga el QR desde su app"| banco
    impresora -.->|"Recibo que el cliente firma"| cliente

    classDef persona fill:#08427b,stroke:#052e56,color:#ffffff
    classDef externa fill:#6b6b6b,stroke:#4d4d4d,color:#ffffff
    classDef sistema fill:#1168bd,stroke:#0b4884,color:#ffffff
    classDef externo fill:#999999,stroke:#6b6b6b,color:#ffffff
    class gerente,secretaria,tecnico persona
    class cliente externa
    class sistema sistema
    class banco,impresora externo
```

---

## C4 — Nivel 2: Contenedores

Qué se despliega y dónde viven los dos patrones de la fusión (marcados con ★).

```mermaid
flowchart TB
    gerente["<b>Gerente</b><br/>[Persona]"]
    secretaria["<b>Secretaria</b><br/>[Persona]"]
    tecnico["<b>Técnico</b><br/>[Persona]"]

    subgraph limite["Sistema de Gestión del Taller"]
        direction TB
        web["<b>Aplicación web</b><br/>[Contenedor: PHP 8 + HTML/CSS/JS, servidor Apache]<br/>Pantallas por cargo: mostrador, taller, caja, gerencia.<br/>La bandeja de avisos se consulta cada 30 s"]

        subgraph nucleo["Núcleo de negocio — monolito modular PHP (mismo despliegue que la web)"]
            direction LR
            seg["<b>Seguridad</b><br/>login, cargos (PoliticaDeCargo),<br/>contraseñas<br/><i>BitacoraDeAuditoria ★ Observer</i>"]
            ord["<b>Órdenes de trabajo</b><br/>recepción → entrega<br/><i>★ OBSERVER: OrdenDeTrabajo<br/>publica EventoOrden</i><br/><i>★ STRATEGY: CalculadoraDeCobro<br/>+ ReglaDeCobro</i>"]
            avi["<b>Avisos</b><br/><i>BandejaDeAvisos</i><br/><i>★ Observer que usa la Strategy<br/>(el punto de fusión)</i>"]
            caj["<b>Caja</b><br/>Mostrador: cobra con la<br/><i>★ misma Strategy</i><br/>AdaptadorPagoQr (Adapter)"]
            inv["<b>Inventario</b><br/>repuestos y accesorios"]
            rep["<b>Reportes</b><br/>solo lectura"]
        end

        cron["<b>Revisor de plazos</b><br/>[Contenedor: PHP CLI + cron diario 07:00]<br/>Llama a revisarPlazo() de cada orden abierta:<br/><i>★ publica PLAZO_POR_VENCER / VENCIDO</i>"]
        db[("<b>Base de datos</b><br/>[Contenedor: MySQL 8]<br/>órdenes, clientes, inventario, caja,<br/>avisos, bitácora, usuarios")]
        fotos[("<b>Almacén de archivos</b><br/>[Contenedor: disco del servidor]<br/>fotos de equipos, copias de carnet,<br/>recibos en PDF")]
    end

    banco["<b>API de cobro QR del banco</b><br/>[Sistema externo]"]
    impresora["<b>Impresora de recibos</b><br/>[Dispositivo externo]"]

    gerente -->|"HTTPS"| web
    secretaria -->|"HTTPS"| web
    tecnico -->|"HTTPS"| web
    web -->|"llamadas en proceso"| nucleo
    ord -->|"EventoOrden"| avi
    ord -->|"EventoOrden"| seg
    caj -->|"cobroDe(orden)"| ord
    cron -->|"revisarPlazo(hoy)"| ord
    nucleo -->|"SQL / PDO"| db
    cron -->|"SQL / PDO"| db
    nucleo -->|"lee / escribe"| fotos
    caj -->|"HTTPS / JSON"| banco
    web -->|"PDF / impresión"| impresora

    classDef persona fill:#08427b,stroke:#052e56,color:#ffffff
    classDef contenedor fill:#1168bd,stroke:#0b4884,color:#ffffff
    classDef modulo fill:#438dd5,stroke:#2e6295,color:#ffffff
    classDef fusion fill:#1168bd,stroke:#f2a900,stroke-width:4px,color:#ffffff
    classDef externo fill:#999999,stroke:#6b6b6b,color:#ffffff
    class gerente,secretaria,tecnico persona
    class web,cron,db,fotos contenedor
    class seg,inv,rep,caj modulo
    class ord,avi fusion
    class banco,impresora externo
    style limite fill:#f7fbff,stroke:#0b4884,stroke-width:2px,stroke-dasharray:6 4
    style nucleo fill:#e8f1fb,stroke:#1168bd,stroke-dasharray:4 3
```

### Dónde viven los dos patrones

| Patrón | Contenedor | Módulo | Clases |
|---|---|---|---|
| **Observer** — sujeto | Aplicación web (núcleo) y Revisor de plazos | Órdenes de trabajo | `OrdenDeTrabajo`, `EventoOrden` |
| **Observer** — observadores | Aplicación web (núcleo) | Avisos y Seguridad | `BandejaDeAvisos`, `BitacoraDeAuditoria` (persisten en MySQL) |
| **Strategy** | Aplicación web (núcleo) | Órdenes de trabajo | `CalculadoraDeCobro`, `ReglaDeCobro` y sus 3 reglas |
| **Fusión** | Aplicación web (núcleo) | Avisos + Caja | `BandejaDeAvisos` y `Mostrador` usan la **misma** `CalculadoraDeCobro` |

