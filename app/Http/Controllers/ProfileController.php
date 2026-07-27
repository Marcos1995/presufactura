<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('settings.index', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:34'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'default_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'default_due_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reminder_day_1' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_2' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_3' => ['required', 'integer', 'min:1', 'max:90'],
            'owner_reminder_day' => ['required', 'integer', 'min:1', 'max:90'],
        ], [
            'business_name.required' => 'El nombre comercial es obligatorio.',
            'tax_id.required' => 'El NIF/CIF es obligatorio.',
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $user->update($data);

        return redirect()->route('settings.index')->with('status', 'Configuración guardada.');
    }
}
