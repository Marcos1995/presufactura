<?php

namespace Tests\Feature;

use App\Mail\BugReportMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BugReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_report_a_bug(): void
    {
        Mail::fake();
        $user = User::factory()->onboarded()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Reportar un fallo')
            ->assertSee('facturas@presufactura.es');

        $this->actingAs($user)
            ->from('/dashboard')
            ->post(route('bug-reports.store'), [
                'description' => 'Al descargar el PDF de la factura no se ve el QR.',
                'page_url' => url('/facturas/1'),
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('bug_reports', [
            'user_id' => $user->id,
            'description' => 'Al descargar el PDF de la factura no se ve el QR.',
        ]);

        Mail::assertSent(BugReportMail::class, function (BugReportMail $mail) use ($user) {
            return $mail->hasTo(config('mail.support_address'))
                && $mail->report->user_id === $user->id;
        });
    }

    public function test_guest_cannot_report_a_bug(): void
    {
        $this->post(route('bug-reports.store'), [
            'description' => 'Algo ha fallado en la web de prueba.',
        ])->assertRedirect(route('login'));
    }
}
