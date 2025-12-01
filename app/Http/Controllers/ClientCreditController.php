<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCredit;
use App\Models\ClientCreditRefund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientCreditController extends Controller
{
    public function index()
    {
        return view('client-credits.index');
    }

    public function create()
    {
        $clients = Client::orderBy('name')->pluck('name', 'id');
        return view('client-credits.create', compact('clients'));
    }

    public function show(ClientCredit $client_credit)
    {
        $credit = $client_credit->load([
            'client',
            'refunds' => fn($q) => $q->orderByDesc('refunded_at')->orderByDesc('created_at'),
        ]);

        $refunded = (float) $credit->refunds->sum('amount');
        $remaining = max(0, (float) $credit->amount - $refunded);

        return view('client-credits.show', compact('credit', 'refunded', 'remaining'));
    }

    public function addRefund(Request $request, ClientCredit $client_credit)
    {
        $request->merge([
            'amount' => $this->normalizeAmount($request->input('amount')),
        ]);

        $data = $request->validate([
            'amount' => ['required','numeric','min:0.01'],
            'refunded_at' => ['nullable','date'],
            'method' => ['nullable','string','max:100'],
            'notes' => ['nullable','string'],
        ]);

        DB::transaction(function () use ($data, $client_credit) {
            $credit = ClientCredit::lockForUpdate()->findOrFail($client_credit->id);
            $alreadyRefunded = (float) $credit->refunds()->sum('amount');
            $remaining = max(0, (float) $credit->amount - $alreadyRefunded);
            if ($data['amount'] > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Montant superieur au reste a rembourser (reste '.number_format($remaining, 2, ',', ' ').' GNF).',
                ]);
            }

            ClientCreditRefund::create([
                'client_credit_id' => $credit->id,
                'amount' => $data['amount'],
                'refunded_at' => isset($data['refunded_at']) ? Carbon::parse($data['refunded_at']) : now(),
                'method' => $data['method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $credit->remaining_amount = $remaining - $data['amount'];
            $credit->save();
        });

        return redirect()
            ->route('client-credits.show', $client_credit->id)
            ->with('success', 'Remboursement enregistre.');
    }

    public function store(Request $request)
    {
        $request->merge([
            'amount' => $this->normalizeAmount($request->input('amount')),
        ]);

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'credited_at' => 'nullable|date',
            'method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            ClientCredit::create([
                'client_id' => $data['client_id'],
                'amount' => $data['amount'],
                'remaining_amount' => $data['amount'],
                'credited_at' => isset($data['credited_at']) ? Carbon::parse($data['credited_at']) : now(),
                'method' => $data['method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('client-credits.index')->with('success', 'Credit enregistre.');
    }

    private function normalizeAmount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', $value);
        if ($clean === null) {
            return null;
        }
        $clean = str_replace(',', '.', str_replace(' ', '', $clean));

        $dotCount = substr_count($clean, '.');
        if ($dotCount > 1) {
            $parts = explode('.', $clean);
            $decimal = array_pop($parts);
            $clean = implode('', $parts).'.'.$decimal;
        }

        return $clean;
    }
}
