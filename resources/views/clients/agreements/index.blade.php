@extends('layouts.client')

@section('page-title', 'MoU Kerja Sama')

@section('client-content')
<div class="card">
    <div class="card-header"><h2 class="card-title font-weight-bold">Daftar MoU</h2></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th scope="col">Nomor dan Judul</th><th scope="col">Invoice Sumber</th><th scope="col">Masa Berlaku</th><th scope="col">Aksi</th></tr></thead>
                <tbody>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td><strong>{{ $agreement->title }}</strong><small class="d-block text-muted">{{ $agreement->number }}</small></td>
                            <td>{{ $agreement->invoice_reference }}</td>
                            <td class="text-nowrap">{{ $agreement->starts_on->format('d/m/Y') }} s.d. {{ $agreement->ends_on->format('d/m/Y') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('client.agreements.show', $agreement->id) }}" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                <a href="{{ route('client.agreements.pdf', $agreement->id) }}" class="btn btn-sm btn-outline-success">Unduh PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada MoU final untuk akun ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination', ['paginator' => $agreements])
    </div>
</div>
@endsection
