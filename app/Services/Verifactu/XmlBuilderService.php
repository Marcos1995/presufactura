<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Company;
use App\Models\Document;
use DOMDocument;

class XmlBuilderService
{
    public function buildAltaXml(Document $document, string $hash, string $timestamp, ?BillingRecord $previous = null): string
    {
        $document->loadMissing(['company.user', 'user', 'client', 'lineItems']);

        $issuer = $document->company;
        $user = $document->user;
        $client = $document->client;
        $software = config('verifactu.software');
        $nif = $this->normalizeNif($issuer?->tax_id ?: $user->tax_id);
        $invoiceType = $document->fiscalInvoiceType();

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'RegistroAlta');
        $dom->appendChild($root);

        $root->appendChild($dom->createElement('IDVersion', '1.0'));

        $idFactura = $dom->createElement('IDFactura');
        $idFactura->appendChild($dom->createElement('IDEmisorFactura', $nif));
        $idFactura->appendChild($dom->createElement('NumSerieFactura', $document->number));
        $idFactura->appendChild($dom->createElement('FechaExpedicionFactura', $document->issue_date->format('d-m-Y')));
        $root->appendChild($idFactura);

        $root->appendChild($dom->createElement('NombreRazonEmisor', htmlspecialchars($issuer?->legal_name ?: ($user->business_name ?: $user->name), ENT_XML1)));
        $root->appendChild($dom->createElement('TipoFactura', $invoiceType));

        if ($document->rectifies_document_id) {
            $document->loadMissing('rectifiesDocument');
            $original = $document->rectifiesDocument;
            if ($original) {
                $rectificadas = $dom->createElement('FacturasRectificadas');
                $idRect = $dom->createElement('IDFacturaRectificada');
                $idRect->appendChild($dom->createElement('IDEmisorFactura', $nif));
                $idRect->appendChild($dom->createElement('NumSerieFactura', $original->number));
                $idRect->appendChild($dom->createElement('FechaExpedicionFactura', $original->issue_date->format('d-m-Y')));
                $rectificadas->appendChild($idRect);
                $root->appendChild($rectificadas);
                $root->appendChild($dom->createElement('TipoRectificativa', 'I'));
            }
        }

        $root->appendChild($dom->createElement('DescripcionOperacion', 'Prestación de servicios'));

        $this->appendDestinatario($dom, $root, $client, $invoiceType);

        $desglose = $dom->createElement('Desglose');
        $claveRegimen = $this->claveRegimen($issuer);
        foreach ($this->groupLinesByVatRate($document) as $amounts) {
            $rate = (float) $amounts['vat_rate'];
            $detalle = $dom->createElement('DetalleDesglose');
            $detalle->appendChild($dom->createElement('ClaveRegimen', $claveRegimen));
            $detalle->appendChild($dom->createElement('CalificacionOperacion', $rate > 0 ? 'S1' : 'N1'));
            if ($rate > 0) {
                $detalle->appendChild($dom->createElement('TipoImpositivo', number_format($rate, 2, '.', '')));
                $detalle->appendChild($dom->createElement('BaseImponibleOimporteNoSujeto', number_format($amounts['base'], 2, '.', '')));
                $detalle->appendChild($dom->createElement('CuotaRepercutida', number_format($amounts['vat'], 2, '.', '')));
                if ($amounts['recargo'] > 0) {
                    $detalle->appendChild($dom->createElement('TipoRecargoEquivalencia', number_format($amounts['recargo_rate'], 2, '.', '')));
                    $detalle->appendChild($dom->createElement('CuotaRecargoEquivalencia', number_format($amounts['recargo'], 2, '.', '')));
                }
            } else {
                $detalle->appendChild($dom->createElement('BaseImponibleOimporteNoSujeto', number_format($amounts['base'], 2, '.', '')));
            }
            $desglose->appendChild($detalle);
        }
        $root->appendChild($desglose);

        $root->appendChild($dom->createElement('CuotaTotal', number_format((float) $document->vat_amount + (float) $document->recargo_amount, 2, '.', '')));
        $root->appendChild($dom->createElement('ImporteTotal', number_format((float) $document->total, 2, '.', '')));

        $this->appendEncadenamiento($dom, $root, $previous);
        $this->appendSistema($dom, $root, $software, $issuer, $user, $nif);

        $root->appendChild($dom->createElement('FechaHoraHusoGenRegistro', $timestamp));
        $root->appendChild($dom->createElement('TipoHuella', '01'));
        $root->appendChild($dom->createElement('Huella', $hash));

        return $dom->saveXML() ?: '';
    }

    public function buildAnulacionXml(Document $document, string $hash, string $timestamp, ?string $previousHash, ?BillingRecord $previous = null): string
    {
        $document->loadMissing(['company.user', 'user']);
        $issuer = $document->company;
        $user = $document->user;
        $nif = $this->normalizeNif($issuer?->tax_id ?: $user->tax_id);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('RegistroAnulacion');
        $dom->appendChild($root);

        $root->appendChild($dom->createElement('IDVersion', '1.0'));

        $idFactura = $dom->createElement('IDFactura');
        $idFactura->appendChild($dom->createElement('IDEmisorFacturaAnulada', $nif));
        $idFactura->appendChild($dom->createElement('NumSerieFacturaAnulada', $document->number));
        $idFactura->appendChild($dom->createElement('FechaExpedicionFacturaAnulada', $document->issue_date->format('d-m-Y')));
        $root->appendChild($idFactura);

        $this->appendEncadenamiento($dom, $root, $previous);
        $this->appendSistema($dom, $root, config('verifactu.software'), $issuer, $user, $nif);

        $root->appendChild($dom->createElement('FechaHoraHusoGenRegistro', $timestamp));
        $root->appendChild($dom->createElement('TipoHuella', '01'));
        $root->appendChild($dom->createElement('Huella', $hash));

        return $dom->saveXML() ?: '';
    }

    private function appendEncadenamiento(DOMDocument $dom, \DOMElement $root, ?BillingRecord $previous): void
    {
        $encadenamiento = $dom->createElement('Encadenamiento');
        if (! $previous) {
            $encadenamiento->appendChild($dom->createElement('PrimerRegistro', 'S'));
        } else {
            $previous->loadMissing('document');
            $anterior = $dom->createElement('RegistroAnterior');
            $anterior->appendChild($dom->createElement(
                'IDEmisorFactura',
                $this->normalizeNif($previous->company?->tax_id ?: $previous->document?->user?->tax_id)
            ));
            $anterior->appendChild($dom->createElement('NumSerieFactura', (string) $previous->document?->number));
            $anterior->appendChild($dom->createElement(
                'FechaExpedicionFactura',
                $previous->document?->issue_date?->format('d-m-Y') ?? ''
            ));
            $anterior->appendChild($dom->createElement('Huella', $previous->hash_current));
            $encadenamiento->appendChild($anterior);
        }
        $root->appendChild($encadenamiento);
    }

    /**
     * @param  array<string, mixed>  $software
     */
    private function appendSistema(DOMDocument $dom, \DOMElement $root, array $software, ?Company $issuer, $user, string $nif): void
    {
        $sistema = $dom->createElement('SistemaInformatico');
        $sistema->appendChild($dom->createElement('NombreRazon', $software['name']));
        $sistema->appendChild($dom->createElement('NIF', $software['nif'] ?: $nif));
        $sistema->appendChild($dom->createElement('NombreSistemaInformatico', $software['name']));
        $sistema->appendChild($dom->createElement('IdSistemaInformatico', 'PF'));
        $sistema->appendChild($dom->createElement('Version', $software['version']));
        $sistema->appendChild($dom->createElement('NumeroInstalacion', (string) ($issuer?->id ?: $user->id)));
        $sistema->appendChild($dom->createElement('TipoUsoPosibleSoloVerifactu', 'S'));
        $sistema->appendChild($dom->createElement('TipoUsoPosibleMultiOT', 'N'));
        $sistema->appendChild($dom->createElement('IndicadorMultiplesOT', 'N'));
        $root->appendChild($sistema);
    }

    private function appendDestinatario(DOMDocument $dom, \DOMElement $root, Client $client, string $invoiceType): void
    {
        if (! $client->tax_id && $invoiceType === Document::KIND_F2) {
            return;
        }

        if (! $client->tax_id) {
            return;
        }

        $dest = $dom->createElement('Destinatarios');
        $destinatario = $dom->createElement('IDDestinatario');
        $destinatario->appendChild($dom->createElement('NombreRazon', htmlspecialchars($client->name, ENT_XML1)));

        if ($this->isSpanishTaxId($client->tax_id) && strtoupper((string) ($client->country ?: 'ES')) === 'ES') {
            $destinatario->appendChild($dom->createElement('NIF', $this->normalizeNif($client->tax_id)));
        } else {
            $idOtro = $dom->createElement('IDOtro');
            $idOtro->appendChild($dom->createElement('CodigoPais', strtoupper((string) ($client->country ?: 'ES'))));
            $idOtro->appendChild($dom->createElement('IDType', '06'));
            $idOtro->appendChild($dom->createElement('ID', $this->normalizeNif($client->tax_id)));
            $destinatario->appendChild($idOtro);
        }

        $dest->appendChild($destinatario);
        $root->appendChild($dest);
    }

    private function claveRegimen(?Company $issuer): string
    {
        return match ($issuer?->vat_regime) {
            Company::VAT_RECARGO => '18',
            Company::VAT_EXENTO => '01',
            default => '01',
        };
    }

    /** @return array<string, array{vat_rate: float, recargo_rate: float, base: float, vat: float, recargo: float}> */
    private function groupLinesByVatRate(Document $document): array
    {
        $groups = [];

        foreach ($document->lineItems as $line) {
            $vatRate = number_format((float) $line->vat_rate, 2, '.', '');
            $recargoRate = number_format((float) $line->recargo_rate, 2, '.', '');
            $key = $vatRate.'|'.$recargoRate;
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'vat_rate' => (float) $vatRate,
                    'recargo_rate' => (float) $recargoRate,
                    'base' => 0.0,
                    'vat' => 0.0,
                    'recargo' => 0.0,
                ];
            }
            $groups[$key]['base'] += (float) $line->line_subtotal;
            $groups[$key]['vat'] += (float) $line->line_vat;
            $groups[$key]['recargo'] += (float) $line->line_recargo;
        }

        return $groups;
    }

    private function isSpanishTaxId(?string $taxId): bool
    {
        $nif = $this->normalizeNif($taxId);

        return (bool) preg_match('/^([0-9]{8}[A-Z]|[XYZ][0-9]{7}[A-Z]|[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J])$/', $nif);
    }

    private function normalizeNif(?string $nif): string
    {
        return strtoupper(preg_replace('/[\s-]+/', '', (string) $nif) ?? '');
    }
}
