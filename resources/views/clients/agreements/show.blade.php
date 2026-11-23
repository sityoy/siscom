@extends('layouts.client')

@section('page-title', 'Detail MoU')

@section('client-content')
<div class="card">
    <div class="card-header d-flex align-items-center flex-wrap">
        <h2 class="card-title font-weight-bold mr-3">{{ $agreement->title }}</h2>
        <a href="{{ route('client.agreements.pdf', $agreement->id) }}" class="btn btn-sm btn-success ml-auto">Unduh PDF</a>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-4">Nomor MoU</dt><dd class="col-sm-8">{{ $agreement->number }}</dd>
            <dt class="col-sm-4">Tempat dan Tanggal MoU</dt><dd class="col-sm-8">{{ $agreement->city }}, {{ $agreement->document_date->translatedFormat('d F Y') }}</dd>
            <dt class="col-sm-4">Invoice Sumber</dt><dd class="col-sm-8">{{ $agreement->invoice_reference }}</dd>
            <dt class="col-sm-4">Nilai Referensi Invoice (Bruto)</dt><dd class="col-sm-8">Rp {{ number_format((float) $agreement->invoice_total, 2, ',', '.') }}</dd>
            <dt class="col-sm-4">Masa Berlaku</dt><dd class="col-sm-8">{{ $agreement->starts_on->translatedFormat('d F Y') }} s.d. {{ $agreement->ends_on->translatedFormat('d F Y') }}</dd>
        </dl>
        <div class="row my-3">
            @foreach(['provider' => 'Pihak Pertama', 'client' => 'Pihak Kedua'] as $side => $label)
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-3 h-100">
                        <h3 class="h6">{{ $label }}</h3>
                        <strong>{{ $agreement->{$side.'_name'} }}</strong>
                        @if(filled($agreement->details[$side.'_address'] ?? null))
                            <div>{{ $agreement->details[$side.'_address'] }}</div>
                        @endif
                        <div class="mt-2">{{ $agreement->{$side.'_signer'} }} · {{ $agreement->{$side.'_title'} }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        @foreach($agreement->articles() as $heading => $body)
            <section class="border-top pt-3 my-3">
                <h3 class="h5">{{ $agreement->details ? 'Pasal ' : '' }}{{ $loop->iteration }}. {{ $heading }}</h3>
                <p class="mb-0 text-break">{!! nl2br(e($body)) !!}</p>
            </section>
        @endforeach
    </div>
    <div class="card-footer"><a class="btn btn-outline-secondary" href="{{ route('client.agreements.index') }}">Kembali</a></div>
</div>
@endsection
