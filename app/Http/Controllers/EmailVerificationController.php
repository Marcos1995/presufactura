<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        try {
            Mail::to($request->user())->send(new WelcomeMail($request->user()));
        } catch (\Throwable) {
            // no bloquear verificación si el email falla
        }

        return redirect()->route('onboarding.step', ['step' => 1])
            ->with('status', 'Email verificado. Configura tu perfil para empezar.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Te hemos reenviado el enlace de verificación.');
    }
}
