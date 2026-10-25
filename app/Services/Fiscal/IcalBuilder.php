<?php

namespace App\Services\Fiscal;

class IcalBuilder
{
    /**
     * @param  list<array{model: string, name: string, period: string, due: string, url?: string}>  $events
     */
    public function build(array $events, string $calendarName = 'PresuFactura fiscal'): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PresuFactura//Fiscal//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($calendarName),
        ];

        foreach ($events as $event) {
            $due = str_replace('-', '', $event['due']);
            $uid = sha1($event['model'].$event['period'].$event['due']).'@presufactura.es';
            $summary = $event['name'].' — '.$event['period'];
            $desc = 'Orientativo. Presenta en la sede AEAT. '.$event['url'];
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART;VALUE=DATE:'.$due;
            $lines[] = 'DTEND;VALUE=DATE:'.$due;
            $lines[] = 'SUMMARY:'.$this->escape($summary);
            $lines[] = 'DESCRIPTION:'.$this->escape($desc);
            if (! empty($event['url'])) {
                $lines[] = 'URL:'.$event['url'];
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * One-shot .ics for a letter deadline.
     */
    public function singleDay(string $summary, string $dateYmd, string $description = '', ?string $url = null): string
    {
        return $this->build([
            [
                'model' => 'carta',
                'name' => $summary,
                'period' => 'Plazo',
                'due' => $dateYmd,
                'url' => $url ?? config('fiscal.aeat_calendar_url'),
            ],
        ], 'PresuFactura plazo');
    }

    private function escape(string $value): string
    {
        return str_replace(["\r", "\n", ',', ';'], ['', '\n', '\,', '\;'], $value);
    }
}
