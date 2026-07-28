<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use ZipArchive;

class DataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_personal_data_as_zip(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive no disponible.');
        }

        Cache::flush();

        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $this->createDocument($user, $client);

        $response = $this->actingAs($user)->post(route('settings.export'));

        $response->assertOk();
        $response->assertDownload('presufactura-datos-'.now()->format('Y-m-d').'.zip');
    }

    public function test_data_export_is_rate_limited_to_once_per_day(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive no disponible.');
        }

        Cache::flush();

        $user = User::factory()->onboarded()->create();

        $this->actingAs($user)->post(route('settings.export'))->assertOk();
        $this->actingAs($user)->post(route('settings.export'))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('error');
    }
}
