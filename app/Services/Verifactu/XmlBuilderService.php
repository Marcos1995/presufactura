<?php

namespace App\Services\Verifactu;

use App\Models\Document;
use DOMDocument;

class XmlBuilderService
{
    public function buildAltaXml(Document $document, string $hash, string $timestamp): string
    {
        $document->loadMissing(['user', 'client', 'lineItems']);

        $user = $document->user;
        $client = $document->client;
        $software = config('verifactu.software');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'RegistroAlta');
        $dom->appendChild($root);

        $root->appendChild($dom->createElement('IDVersion', '1.0'));

        $idFactura = $dom->createElement('IDFactura');
        $idFactura->appendChild($dom->createElement('IDEmisorFactura', $this->normalizeNif($user->tax_id)));
        $idFactura->appendChild($dom->createElement('NumSerieFactura', $document->number));
        $idFactura->appendChild($dom->createElement('FechaExpedicionFactura', $document->issue_date->format('d-m-Y')));
        $root->appendChild($idFactura);

        $root->appendChild($dom->createElement('NombreRazonEmisor', htmlspecialchars($user->business_name ?: $user->name, ENT_XML1)));
        $root->appendChild($dom->createElement('TipoFactura', $document->rectifies_document_id ? 'R1' : 'F1'));
        $root->appendChild($dom->createElement('DescripcionOperacion', 'Prestación de servicios'));

        if ($client->tax_id) {
            $dest = $dom->createElement('Destinatarios');
            $destinatario = $dom->createElement('IDDestinatario');
            $destinatario->appendChild($dom->createElement('NombreRazon', htmlspecialchars($client->name, ENT_XML1)));
            $destinatario->appendChild($dom->createElement('NIF', $this->normalizeNif($client->tax_id)));
            $dest->appendChild($destinatario);
            $root->appendChild($dest);
        }

        $desglose = $dom->createElement('Desglose');
        foreach ($this->groupLinesByVatRate($document) as $rate => $amounts) {
            $detalle = $dom->createElement('DetalleDesglose');
            $detalle->appendChild($dom->createElement('ClaveRegimen', '01'));
            $detalle->appendChild($dom->createElement('CalificacionOperacion', 'S1'));
            $detalle->appendChild($dom->createElement('TipoImpositivo', number_format((float) $rate, 2, '.', '')));
            $detalle->appendChild($dom->createElement('BaseImponibleOimporteNoSujeto', number_format($amounts['base'], 2, '.', '')));
            $detalle->appendChild($dom->createElement('CuotaRepercutida', number_format($amounts['vat'], 2, '.', '')));
            $desglose->appendChild($detalle);
        }
        $root->appendChild($desglose);

        $root->appendChild($dom->createElement('CuotaTotal', number_format((float) $document->vat_amount, 2, '.', '')));
        $root->appendChild($dom->createElement('ImporteTotal', number_format((float) $document->total, 2, '.', '')));

        $encadenamiento = $dom->createElement('Encadenamiento');
        $encadenamiento->appendChild($dom->createElement('Huella', $hash));
        $root->appendChild($encadenamiento);

        $sistema = $dom->createElement('SistemaInformatico');
        $sistema->appendChild($dom->createElement('NombreRazon', $software['name']));
        $sistema->appendChild($dom->createElement('NIF', $software['nif'] ?: $this->normalizeNif($user->tax_id)));
        $sistema->appendChild($dom->createElement('NombreSistemaInformatico', $software['name']));
        $sistema->appendChild($dom->createElement('IdSistemaInformatico', 'PF'));
        $sistema->appendChild($dom->createElement('Version', $software['version']));
        $sistema->appendChild($dom->createElement('NumeroInstalacion', (string) $user->id));
        $sistema->appendChild($dom->createElement('TipoUsoPosibleSoloVerifactu', 'S'));
        $sistema->appendChild($dom->createElement('TipoUsoPosibleMultiOT', 'N'));
        $sistema->appendChild($dom->createElement('IndicadorMultiplesOT', 'N'));
        $root->appendChild($sistema);

        $root->appendChild($dom->createElement('FechaHoraHusoGenRegistro', $timestamp));

        return $dom->saveXML() ?: '';
    }

    public function buildAnulacionXml(Document $document, string $hash, string $timestamp, ?string $previousHash): string
    {
        $document->loadMissing(['user']);
        $user = $document->user;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('RegistroAnulacion');
        $dom->appendChild($root);

        $root->appendChild($dom->createElement('IDVersion', '1.0'));

        $idFactura = $dom->createElement('IDFactura');
        $idFactura->appendChild($dom->createElement('IDEmisorFactura', $this->normalizeNif($user->tax_id)));
        $idFactura->appendChild($dom->createElement('NumSerieFactura', $document->number));
        $idFactura->appendChild($dom->createElement('FechaExpedicionFactura', $document->issue_date->format('d-m-Y')));
        $root->appendChild($idFactura);

        $encadenamiento = $dom->createElement('Encadenamiento');
        $encadenamiento->appendChild($dom->createElement('Huella', $hash));
        if ($previousHash) {
            $encadenamiento->appendChild($dom->createElement('HuellaAnterior', $previousHash));
        }
        $root->appendChild($encadenamiento);

        $root->appendChild($dom->createElement('FechaHoraHusoGenRegistro', $timestamp));

        return $dom->saveXML() ?: '';
    }

    /** @return array<string, array{base: float, vat: float}> */
    private function groupLinesByVatRate(Document $document): array
    {
        $groups = [];

        foreach ($document->lineItems as $line) {
            $rate = number_format((float) $line->vat_rate, 2, '.', '');
            if (! isset($groups[$rate])) {
                $groups[$rate] = ['base' => 0.0, 'vat' => 0.0];
            }
            $groups[$rate]['base'] += (float) $line->line_subtotal;
            $groups[$rate]['vat'] += (float) $line->line_vat;
        }

        return $groups;
    }

    private function normalizeNif(?string $nif): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $nif));
    }
}
