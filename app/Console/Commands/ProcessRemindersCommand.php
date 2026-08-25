<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Reminder;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessRemindersCommand extends Command
{
    protected $signature = 'presufactura:process-reminders';

    protected $description = 'Marca facturas vencidas y envía recordatorios de cobro';

    public function __construct(
        private EmailService $emailService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $this->expireOverdueDocuments();
            $this->sendReminders();

            $this->info('Recordatorios procesados.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('Error procesando recordatorios: '.$e->getMessage());
            $this->notifyAdmin($e);

            return self::FAILURE;
        }
    }

    private function notifyAdmin(\Throwable $e): void
    {
        $to = config('mail.from.address');
        if (! filled($to)) {
            return;
        }

        try {
            Mail::raw(
                'Falló presufactura:process-reminders en '.config('app.url')."\n\n".$e->getMessage(),
                fn ($message) => $message->to($to)->subject('PresuFactura: error en recordatorios')
            );
        } catch (\Throwable $mailError) {
            Log::error('No se pudo enviar alerta admin de recordatorios', [
                'error' => $mailError->getMessage(),
            ]);
        }
    }

    private function expireOverdueDocuments(): void
    {
        Document::query()
            ->where('type', Document::TYPE_INVOICE)
            ->where('status', Document::STATUS_SENT)
            ->whereDate('due_date', '<', now()->startOfDay())
            ->update(['status' => Document::STATUS_EXPIRED]);

        Document::query()
            ->where('type', Document::TYPE_QUOTE)
            ->where('status', Document::STATUS_SENT)
            ->whereDate('valid_until', '<', now()->startOfDay())
            ->update(['status' => Document::STATUS_EXPIRED]);
    }

    private function sendReminders(): void
    {
        $documents = Document::query()
            ->with(['user', 'client', 'reminders'])
            ->where('type', Document::TYPE_INVOICE)
            ->whereIn('status', [Document::STATUS_SENT, Document::STATUS_EXPIRED])
            ->whereDate('due_date', '<', now()->startOfDay())
            ->get();

        foreach ($documents as $document) {
            $daysOverdue = (int) $document->due_date->startOfDay()->diffInDays(now()->startOfDay());
            $user = $document->user;

            $this->maybeSendClientReminder($document, $daysOverdue, Reminder::TYPE_CLIENT_DAY_3, $user->reminder_day_1);
            $this->maybeSendClientReminder($document, $daysOverdue, Reminder::TYPE_CLIENT_DAY_7, $user->reminder_day_2);
            $this->maybeSendClientReminder($document, $daysOverdue, Reminder::TYPE_CLIENT_DAY_14, $user->reminder_day_3);
            $this->maybeSendOwnerReminder($document, $daysOverdue, $user->owner_reminder_day);
        }
    }

    private function maybeSendClientReminder(Document $document, int $daysOverdue, string $type, int $targetDay): void
    {
        if ($daysOverdue !== $targetDay) {
            return;
        }

        if ($document->reminders->contains('type', $type)) {
            return;
        }

        $this->emailService->sendClientReminder($document, $daysOverdue);
        $this->emailService->recordReminderSent($document, $type, $document->client->email);
        $this->line("Recordatorio cliente {$type} enviado: {$document->number}");
    }

    private function maybeSendOwnerReminder(Document $document, int $daysOverdue, int $targetDay): void
    {
        if ($daysOverdue !== $targetDay) {
            return;
        }

        if ($document->reminders->contains('type', Reminder::TYPE_OWNER_DAY_10)) {
            return;
        }

        $this->emailService->sendOwnerReminder($document, $daysOverdue);
        $this->emailService->recordReminderSent($document, Reminder::TYPE_OWNER_DAY_10, $document->user->email);
        $this->line("Recordatorio autónomo enviado: {$document->number}");
    }
}
