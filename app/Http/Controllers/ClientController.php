<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = auth()->user()->clients()->orderBy('name')->get();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        auth()->user()->clients()->create($data);

        return redirect()->route('clients.index')->with('status', 'Cliente creado.');
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);

        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $client->update($this->validated($request));

        return redirect()->route('clients.index')->with('status', 'Cliente actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        if ($client->documents()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con documentos asociados.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email válido.',
        ]);
    }

    private function authorizeClient(Client $client): void
    {
        abort_unless($client->user_id === auth()->id(), 403);
    }
}
