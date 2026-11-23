@extends('layouts.admin')

@section('page-title', 'MoU Kerja Sama')

@section('admin-content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <h2 class="card-title font-weight-bold mr-3">Daftar MoU</h2>
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-primary ml-auto">Pilih Invoice untuk Membuat MoU</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Nomor MoU</th><th>Kerja Sama dan Client</th><th>Invoice Sumber</th><th>Masa Berlaku</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td>{{ $agreement->number }}</td>
                            <td><strong>{{ $agreement->title }}</strong><small class="text-muted d-block">{{ $agreement->client_name }}</small></td>
                            <td>{{ $agreement->invoice_reference }}</td>
                            <td class="text-nowrap">{{ $agreement->starts_on->format('d/m/Y') }} – {{ $agreement->ends_on->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $agreement->finalized_at ? 'success' : 'warning' }}">{{ $agreement->finalized_at ? 'Final' : 'Draf' }}</span></td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('agreements.show', $agreement) }}">Buka</a>
                                <a class="btn btn-sm btn-outline-success" href="{{ route('agreements.pdf', $agreement) }}">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada MoU. Buka daftar invoice untuk membuat draf pertama.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination', ['paginator' => $agreements])
    </div>
</div>
@endsection
