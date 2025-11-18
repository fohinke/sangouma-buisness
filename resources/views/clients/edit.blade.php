@extends('layouts.master')
@section('title','Modifier client')
@section('content')
<div>
  <h1>Modifier client</h1>
  @include('components.flash')
  <form method="post" action="{{ route('clients.update',$client) }}" class="row g-3">@csrf @method('put')
    <div class="col-md-6">
      <label class="form-label">Nom</label>
      <input name="name" class="form-control" required value="{{ old('name',$client->name) }}">
      @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label class="form-label">Téléphone</label>
      <input name="phone" class="form-control" required value="{{ old('phone',$client->phone) }}">
      @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label class="form-label">Email (facultatif)</label>
      <input type="text" name="email" class="form-control" value="{{ old('email',$client->email) }}">
      @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6"><label class="form-label">Adresse</label><input name="address" class="form-control" value="{{ old('address',$client->address) }}"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes',$client->notes) }}</textarea></div>
    <div class="col-12"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a></div>
  </form>
  @if($errors->any())
    <div class="alert alert-danger mt-3">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
</div>
@endsection

