@php
  $val = strtolower((string) ($value ?? ''));
  $classMap = [
    'en_attente' => 'secondary',
    'partiellement_payee' => 'warning',
    'payee' => 'success',
    'livree' => 'info',
    'en_cours' => 'primary',
    'en_retard' => 'danger',
  ];
  $labelMap = [
    'en_attente' => 'En attente',
    'partiellement_payee' => 'Partiellement payée',
    'payee' => 'Payée',
    'livree' => 'Livrée',
    'en_cours' => 'En cours',
    'en_retard' => 'En retard',
  ];
  $class = $classMap[$val] ?? 'secondary';
  $label = $labelMap[$val] ?? ucfirst($val);
@endphp
<span class="badge bg-{{ $class }}">
  @if($val === 'en_retard')
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
  @endif
  {{ $label }}
</span>

