<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\ClientCredit;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ClientCreditsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'credited_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public int $page = 1;
    public array $clients = [];

    // Modal state + form fields
    public bool $showModal = false;
    public int $modalKey = 0;
    public ?int $client_id = null;
    public string $client_name = '';
    public string $amount = '';
    public ?string $credited_at = null;
    public string $method = '';
    public string $notes = '';

    protected $listeners = [
        'clientSelected' => 'setClient',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'credited_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->credited_at = now()->format('Y-m-d\\TH:i');
        $this->clients = Client::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function updatingSearch(): void { $this->resetPage(); }
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

    public function getCreditsProperty()
    {
        $query = ClientCredit::query()
            ->with('client')
            ->withSum('refunds as refunded_amount', 'amount')
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->whereHas('client', fn($c) => $c->where('name', 'like', "%{$s}%"))
                        ->orWhere('method', 'like', "%{$s}%")
                        ->orWhere('notes', 'like', "%{$s}%");
                });
            });

        if ($this->sortField === 'client') {
            $query->leftJoin('clients', 'clients.id', '=', 'client_credits.client_id')
                ->orderBy('clients.name', $this->sortDirection)
                ->select('client_credits.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required','integer','exists:clients,id'],
            'amount' => ['required','numeric','min:0.01'],
            'credited_at' => ['nullable','date'],
            'method' => ['nullable','string','max:100'],
            'notes' => ['nullable','string'],
        ];
    }

    private function resetForm(): void
    {
        $this->client_id = null;
        $this->client_name = '';
        $this->amount = '';
        $this->credited_at = now()->format('Y-m-d\\TH:i');
        $this->method = '';
        $this->notes = '';
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

    public function resetClient(): void
    {
        $this->client_id = null;
        $this->client_name = '';
    }

    public function updatedClientId($value): void
    {
        $id = (int) $value;
        $this->client_id = $id > 0 ? $id : null;
        $this->client_name = $this->client_id && isset($this->clients[$this->client_id])
            ? (string) $this->clients[$this->client_id]
            : '';
        $this->resetErrorBag('client_id');
    }

    #[On('clientSelected')]
    public function setClient(array $client): void
    {
        $this->client_id = isset($client['id']) ? (int) $client['id'] : null;
        $this->client_name = (string) ($client['name'] ?? '');
        if ($this->client_id && !array_key_exists($this->client_id, $this->clients)) {
            $this->clients = Client::orderBy('name')->pluck('name', 'id')->toArray();
        }
        $this->resetErrorBag('client_id');
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

    public function saveCredit(): void
    {
        $this->amount = $this->normalizeAmount($this->amount) ?? '';
        $data = $this->validate();

        \Log::info('client credit save start', ['data' => $data]);

        ClientCredit::create([
            'client_id' => $data['client_id'],
            'amount' => $data['amount'],
            'remaining_amount' => $data['amount'],
            'credited_at' => isset($data['credited_at']) ? Carbon::parse($data['credited_at']) : now(),
            'method' => $data['method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        \Log::info('client credit saved');

        session()->flash('success', 'Credit ajoute.');
        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.client-credits-table', [
            'credits' => $this->credits,
            'amountPreview' => $this->amountPreview,
        ]);
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

    private function formatNumber(float $num): string
    {
        return number_format($num, 0, ',', ' ');
    }
}
