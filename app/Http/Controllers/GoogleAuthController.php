<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public static function enabled(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function redirect(): RedirectResponse
    {
        if (! self::enabled()) {
            return redirect()->route('login')
                ->with('error', 'El acceso con Google no está disponible ahora mismo.');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! self::enabled()) {
            return redirect()->route('login')
                ->with('error', 'El acceso con Google no está disponible ahora mismo.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->with('error', 'No se pudo completar el acceso con Google. Inténtalo de nuevo.');
        }

        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = trim((string) $googleUser->getName());

        if ($googleId === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')
                ->with('error', 'Google no compartió un email válido. Usa el registro con email o prueba otra cuenta.');
        }

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $isNew = false;
        $user = User::query()->where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => Str::password(32),
                    'email_verified_at' => now(),
                ]);
                $isNew = true;
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($isNew) {
            app(AnalyticsService::class)->record(AnalyticsEvent::REGISTRATION_COMPLETED);

            try {
                Mail::to($user)->send(new WelcomeMail($user));
            } catch (Throwable) {
                // no bloquear el alta si el email falla
            }

            return redirect()->route('onboarding.step', ['step' => 1])
                ->with('status', 'Cuenta creada con Google. Configura tu perfil para empezar.');
        }

        return redirect()->intended(route('dashboard'));
    }
};
