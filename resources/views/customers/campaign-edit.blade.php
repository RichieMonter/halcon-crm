@extends('layouts.app')

@section('content')
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <a href="{{ route('customers.index') }}" class="text-sm font-medium text-indigo-700 hover:underline">← Volver a Gestión de clientes</a>
        <h1 class="mt-3 text-xl font-bold text-slate-900">Editar campaña</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $campaign->name }} · {{ $campaign->status() }}</p>
        @include('customers.campaign-form')
    </section>
@endsection
