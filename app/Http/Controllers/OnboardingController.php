<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(int $step): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isProfileComplete() && $step > 3) {
            return redirect()->route('dashboard');
        }

        $expected = $user->onboardingStep();

        if ($expected === 0) {
            return redirect()->route('dashboard');
        }

        if ($step !== $expected) {
            return redirect()->route('onboarding.step', ['step' => $expected]);
        }

        abort_unless($step >= 1 && $step <= 3, 404);

        return view('onboarding.step'.$step, compact('user', 'step'));
    }

    public function store(Request $request, int $step): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($step >= 1 && $step <= 3, 404);

        match ($step) {
            1 => $this->storeStep1($request, $user),
            2 => $this->storeStep2($request, $user),
            3 => $this->storeStep3($request, $user),
        };

        if ($step === 3 || $user->fresh()->isProfileComplete()) {
            return redirect()->route('dashboard')->with('status', '¡Perfil configurado! Ya puedes crear documentos.');
        }

        return redirect()->route('onboarding.step', ['step' => $user->fresh()->onboardingStep() ?: ($step + 1)]);
    }

    private function storeStep1(Request $request, $user): void
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);
        $company = $user->ensureDefaultCompany();
        $company->update([
            'legal_name' => $data['business_name'],
            'tax_id' => $data['tax_id'],
            'address' => $data['address'],
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'phone' => $data['phone'] ?? $company->phone,
            'email' => $user->email,
        ]);
    }

    private function storeStep2(Request $request, $user): void
    {
        $data = $request->validate([
            'iban' => ['required', 'string', 'max:34'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);
        $user->update($data);
        $company = $user->ensureDefaultCompany();
        $company->update([
            'iban' => $data['iban'],
            'logo_path' => $data['logo_path'] ?? $company->logo_path,
        ]);
    }

    private function storeStep3(Request $request, $user): void
    {
        $validated = $request->validate([
            'default_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'default_due_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reminder_day_1' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_2' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_3' => ['required', 'integer', 'min:1', 'max:90'],
            'owner_reminder_day' => ['required', 'integer', 'min:1', 'max:90'],
        ], [
            'reminder_day_1.required' => 'Indica el día del primer recordatorio al cliente.',
            'reminder_day_2.required' => 'Indica el día del segundo recordatorio al cliente.',
            'reminder_day_3.required' => 'Indica el día del tercer recordatorio al cliente.',
            'owner_reminder_day.required' => 'Indica el día del recordatorio al autónomo.',
        ]);

        $data = [
            'default_vat_rate' => $validated['default_vat_rate'],
            'invoice_prefix' => $validated['invoice_prefix'],
            'quote_prefix' => $validated['quote_prefix'],
            'default_due_days' => (int) $validated['default_due_days'],
            'reminder_day_1' => (int) $validated['reminder_day_1'],
            'reminder_day_2' => (int) $validated['reminder_day_2'],
            'reminder_day_3' => (int) $validated['reminder_day_3'],
            'owner_reminder_day' => (int) $validated['owner_reminder_day'],
        ];

        if (Schema::hasColumn('users', 'onboarding_completed_at')) {
            $data['onboarding_completed_at'] = now();
        } else {
            $request->session()->put('onboarding_step3_done', true);
        }

        $user->update($data);
        $company = $user->ensureDefaultCompany();
        $company->update([
            'default_vat_rate' => $validated['default_vat_rate'],
            'invoice_prefix' => $validated['invoice_prefix'],
            'quote_prefix' => $validated['quote_prefix'],
            'default_due_days' => (int) $validated['default_due_days'],
        ]);
    }
}
