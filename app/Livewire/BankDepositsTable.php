<?php

namespace App\Livewire;

use App\Models\BankDeposit;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BankDepositsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public ?string $from = null;
    public ?string $to = null;
    public string $sortField = 'deposited_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public int $page = 1;
    public bool $showModal = false;
    public int $modalKey = 0;

    // Form fields
    public ?string $reference = null;
    public string $bank_name = '';
    public ?string $account_number = null;
    public string $amount = '';
    public ?string $deposited_at = null;
    public ?string $method = null;
    public ?string $notes = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'from' => ['except' => null],
        'to' => ['except' => null],
        'sortField' => ['except' => 'deposited_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->deposited_at = now()->format('Y-m-d\\TH:i');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFrom(): void { $this->resetPage(); }
    public function updatingTo(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    private function baseQuery()
    {
        return BankDeposit::query()
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('reference', 'like', "%{$s}%")
                        ->orWhere('bank_name', 'like', "%{$s}%")
                        ->orWhere('account_number', 'like', "%{$s}%")
                        ->orWhere('method', 'like', "%{$s}%")
                        ->orWhere('notes', 'like', "%{$s}%");
                });
            })
            ->when($this->from, function ($q) {
                $from = Carbon::parse($this->from)->startOfDay();
                $q->where('deposited_at', '>=', $from);
            })
            ->when($this->to, function ($q) {
                $to = Carbon::parse($this->to)->endOfDay();
                $q->where('deposited_at', '<=', $to);
            });
    }

    public function getDepositsProperty()
    {
        $query = $this->baseQuery();

        $query->orderBy($this->sortField, $this->sortDirection)
            ->orderByDesc('id');

        return $query->paginate($this->perPage);
    }

    public function getTotalAmountProperty(): float
    {
        return (float) ($this->baseQuery()->sum('amount') ?? 0);
    }

    protected function rules(): array
    {
        return [
            'reference' => ['nullable','string','max:100','unique:bank_deposits,reference'],
            'bank_name' => ['required','string','max:255'],
            'account_number' => ['nullable','string','max:255'],
            'amount' => ['required','numeric','min:0.01'],
            'deposited_at' => ['nullable','date'],
            'method' => ['nullable','string','max:100'],
            'notes' => ['nullable','string'],
        ];
    }

    private function resetForm(): void
    {
        $this->reference = null;
        $this->bank_name = '';
        $this->account_number = null;
        $this->amount = '';
        $this->deposited_at = now()->format('Y-m-d\\TH:i');
        $this->method = null;
        $this->notes = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function saveDeposit(): void
    {
        $this->amount = $this->normalizeAmount($this->amount);
        $data = $this->validate();

        BankDeposit::create([
            'reference' => $data['reference'] ?? null,
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'] ?? null,
            'amount' => $data['amount'],
            'deposited_at' => isset($data['deposited_at']) ? Carbon::parse($data['deposited_at']) : now(),
            'method' => $data['method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        session()->flash('success', 'Depot bancaire enregistre.');
        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function updatedAmount($value): void
    {
        $normalized = $this->normalizeAmount($value);
        if ($normalized === null || $normalized === '') {
            $this->amount = '';
            return;
        }
        $num = round((float) $normalized, 0);
        $this->amount = $this->formatNumber($num);
    }

    public function formatAmount(): void
    {
        $normalized = $this->normalizeAmount($this->amount);
        if ($normalized === null || $normalized === '') {
            $this->amount = '';
            return;
        }
        $num = round((float) $normalized, 0);
        $this->amount = $this->formatNumber($num);
    }

    public function getAmountPreviewProperty(): string
    {
        $normalized = $this->normalizeAmount($this->amount);
        if ($normalized === null || $normalized === '') {
            return '';
        }
        $num = round((float) $normalized, 0);
        return $this->formatNumber($num).' GNF';
    }

    private function normalizeAmount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove spaces and non-number separators, keep digits, dots, commas, minus
        $clean = preg_replace('/[^0-9,.\-]/', '', $value);
        if ($clean === null) {
            return null;
        }
        $clean = str_replace(',', '.', str_replace(' ', '', $clean));

        // If multiple dots, keep the last as decimal separator
        $dotCount = substr_count($clean, '.');
        if ($dotCount > 1) {
            $parts = explode('.', $clean);
            $decimal = array_pop($parts);
            $clean = implode('', $parts).'.'.$decimal;
        }

        return $clean;
    }

    private function formatNumber(float $num): string
    {
        return number_format($num, 0, ',', ' ');
    }

    public function render()
    {
        return view('livewire.bank-deposits-table', [
            'deposits' => $this->deposits,
            'totalAmount' => $this->totalAmount,
            'amountPreview' => $this->amountPreview,
        ]);
    }
}
