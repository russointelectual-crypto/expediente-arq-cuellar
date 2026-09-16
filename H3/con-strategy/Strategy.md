# Strategy — politicas de cobro del taller

## Aplicacion a mi variante

El H2 ya propone IPoliticaCobro y las politicas CobroReparacion, CobroSoloDiagnostico y CobroEnGarantia. Esta practica convierte esa decision en codigo PHP. Se conserva la idea de la clase: cada algoritmo cumple el mismo contrato y el contexto recibe la politica que debe aplicar.

| Pieza | Funcion |
| --- | --- |
| IPoliticaCobro | Contrato calcular(DatosCobro): int. |
| DatosCobro | Importes de diagnostico, mano de obra y repuestos en centavos. |
| CobroReparacion | Suma mano de obra y repuestos. |
| CobroSoloDiagnostico | Cobra solamente el diagnostico. |
| CobroEnGarantia | Devuelve cero cuando la cobertura total ya esta aprobada. |
| ServicioCaja | Contexto que delega el calculo a la politica seleccionada. |
| demo.php | Cambia la politica de una misma caja durante la ejecucion. |

## Supuestos del ejemplo

Los nombres de las politicas provienen del H2. Los importes y formulas son ilustrativos: diagnostico Bs 50, mano de obra Bs 150 y repuestos Bs 200. Para la reparacion se supone que el diagnostico esta incluido en la mano de obra. La garantia aprobada cubre todo el trabajo. Estas reglas no pretenden establecer las tarifas reales del taller.

El calculo usa enteros en centavos; solo la presentacion convierte a bolivianos. El constructor de DatosCobro rechaza importes negativos.

## Ejecutar desde la raiz

```bash
php H3/con-strategy/demo.php
```

