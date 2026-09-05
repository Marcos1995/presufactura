<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = auth()->user()->currentCompany()->clients()->orderBy('name')->get();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = auth()->user()->currentCompany();
        $data = $this->validated($request);
        $company->clients()->create(array_merge([
            'user_id' => auth()->id(),
            'person_type' => 'company',
            'country' => 'ES',
            'is_active' => true,
        ], $data, [
            'is_active' => $request->boolean('is_active', true),
            'person_type' => $data['person_type'] ?? 'company',
            'country' => $data['country'] ?? 'ES',
        ]));

        return redirect()->route('clients.index')->with('status', 'Cliente creado.');
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);
        $client->update($this->validated($request));

        return redirect()->route('clients.index')->with('status', 'Cliente actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        if ($client->documents()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con documentos asociados.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'person_type' => ['nullable', Rule::in(['person', 'company'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email válido.',
        ]);
    }
}
