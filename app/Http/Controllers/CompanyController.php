<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LegalConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $company = $user->companies()->create(array_merge($data, [
            'email' => $data['email'] ?? $user->email,
            'is_default' => $user->companies()->count() === 0,
            'is_active' => true,
        ]));

        $user->setCurrentCompany($company);

        if ($request->boolean('accept_terms')) {
            LegalConsent::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'document_key' => 'terms',
                'version' => '2026-09',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
            ]);
            $company->update([
                'legal_terms_version' => '2026-09',
                'legal_terms_accepted_at' => now(),
            ]);
        }

        return redirect()->route('settings.index')->with('status', 'Empresa creada. Estás trabajando con '.$company->legal_name.'.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);
        $company->update($this->validated($request));
        $company->syncLegacyUserFields();

        return redirect()->route('settings.index')->with('status', 'Empresa actualizada.');
    }

    public function switch(Company $company): RedirectResponse
    {
        $this->authorize('view', $company);
        $requestUser = auth()->user();
        $requestUser->setCurrentCompany($company);

        return back()->with('status', 'Empresa activa: '.$company->legal_name);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'province' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:34'],
            'vat_regime' => ['required', 'in:general,recargo,exento'],
            'default_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_recargo_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_due_days' => ['required', 'integer', 'min:1', 'max:365'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'invoice_footer' => ['nullable', 'string', 'max:2000'],
        ], [
            'legal_name.required' => 'La razón social es obligatoria.',
            'tax_id.required' => 'El NIF/CIF es obligatorio.',
        ]);
    }
}
