<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankDepositController extends Controller
{
    public function index()
    {
        return view('bank-deposits.index');
    }

    public function create()
    {
        return view('bank-deposits.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reference' => ['nullable','string','max:100','unique:bank_deposits,reference'],
            'bank_name' => ['required','string','max:255'],
            'account_number' => ['nullable','string','max:255'],
            'amount' => ['required','numeric','min:0.01'],
            'deposited_at' => ['nullable','date'],
            'method' => ['nullable','string','max:100'],
            'notes' => ['nullable','string'],
        ]);

        BankDeposit::create([
            'reference' => $data['reference'] ?? null,
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'] ?? null,
            'amount' => $data['amount'],
            'deposited_at' => isset($data['deposited_at']) ? Carbon::parse($data['deposited_at']) : now(),
            'method' => $data['method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('bank-deposits.index')->with('success', 'Depot bancaire enregistre.');
    }

    public function show(BankDeposit $bank_deposit)
    {
        return view('bank-deposits.show', ['deposit' => $bank_deposit]);
    }
}
