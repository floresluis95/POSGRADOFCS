-- estudianteprograma.costomatricula era INT: una matrícula con centavos (p. ej. 350 con 15% de
-- descuento = 297.50) se guardaba como 298, mientras montoPagado (DECIMAL) guardaba 297.50.
-- Los reportes que usan costomatricula (Reporte General, Saldos, PDF) decían 298 y Pagos
-- recibidos / Por cuenta 297.50, y la matrícula salía como "no pagada" (297.50 < 298).
-- Con DECIMAL(10,2) caben los centavos.
ALTER TABLE estudianteprograma MODIFY costomatricula DECIMAL(10,2) NOT NULL;

-- Devuelve los centavos a las matrículas ya redondeadas: el sistema siempre guarda
-- costomatricula = montoPagado, así que una diferencia menor a 1 Bs. es solo el redondeo.
UPDATE estudianteprograma
SET costomatricula = montoPagado
WHERE costomatricula <> montoPagado AND ABS(costomatricula - montoPagado) < 1;
