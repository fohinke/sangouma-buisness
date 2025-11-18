<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $clients = Client::query()
            ->when($q, fn($b) => $b->where('name','like',"%$q%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        return view('clients.index', compact('clients','q'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        Client::create($request->validated());
        return redirect()->route('clients.index')->with('success','Client créé.');
    }

    public function show(Client $client)
    {
        $sales = $client->sales()->withSum('payments as paid_amount','amount')->latest('sold_at')->paginate(10);
        return view('clients.show', compact('client','sales'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.index')->with('success','Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client supprimé.');
    }

    public function export()
    {
        $rows = \App\Models\Client::withTrashed()->orderBy('name')->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clients.csv"',
        ];
        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputs($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($out, ['Nom','Téléphone','Email','Adresse','Statut'], ';');
            foreach ($rows as $c) {
                $status = $c->deleted_at ? 'Inactif' : 'Actif';
                fputcsv($out, [
                    $c->name,
                    $c->phone,
                    $c->email,
                    $c->address,
                    $status,
                ], ';');
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }
}
