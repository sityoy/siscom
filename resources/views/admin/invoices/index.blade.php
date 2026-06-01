@extends('layouts.admin')

@section('page-title', 'Invoices')

@section('admin-content')

<div class="card border-0 shadow-sm rounded-4">

    {{-- HEADER --}}
    <div class="card-header bg-white border-0 px-4 py-4">

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

            <div>

                <h3 class="fw-bold text-dark mb-1">

                    Invoice Management

                </h3>

                <p class="text-muted mb-0">

                    Total Invoice:
                    <span class="fw-semibold">

                        {{ $invoices->total() }}

                    </span>

                </p>

            </div>

            <div>

                <a href="{{ route('invoices.create') }}"
                   class="btn btn-primary rounded-pill px-4">

                    <i class="bi bi-plus-circle me-1"></i>

                    Tambah Invoice

                </a>

            </div>

        </div>

    </div>

    {{-- BODY --}}
    <div class="card-body px-0 pb-0">

        @if(session('success'))

            <div class="px-4 pt-2">

                <div class="alert alert-success alert-dismissible fade show rounded-3">

                    {{ session('success') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                </div>

            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="text-center" width="5%">
                            #
                        </th>

                        <th width="14%">
                            Invoice
                        </th>

                        <th width="16%">
                            Client
                        </th>

                        <th width="14%">
                            Project
                        </th>

                        <th width="28%">
                            Detail Item
                        </th>

                        <th width="12%">
                            Total
                        </th>

                        <th class="text-center" width="8%">
                            Status
                        </th>

                        <th width="10%">
                            Due Date
                        </th>

                        <th class="text-center" width="12%">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($invoices as $invoice)

                        <tr>

                            {{-- NO --}}
                            <td class="text-center fw-semibold">

                                {{ $loop->iteration }}

                            </td>

                            {{-- INVOICE --}}
                            <td>

                                <div class="fw-bold text-dark">

                                    {{ $invoice->invoice_number }}

                                </div>

                                <small class="text-muted">

                                    {{ $invoice->created_at->format('d M Y') }}

                                </small>

                            </td>

                            {{-- CLIENT --}}
                            <td>

                                <div class="fw-semibold text-dark">

                                    {{ $invoice->client->name }}

                                </div>

                                <small class="text-muted text-break">

                                    {{ $invoice->client->email }}

                                </small>

                            </td>

                            {{-- PROJECT --}}
                            <td>

                                @if($invoice->project)

                                    <div class="small fw-semibold text-dark">

                                        {{ $invoice->project->title }}

                                    </div>

                                @else

                                    <span class="text-muted">

                                        -

                                    </span>

                                @endif

                            </td>

                            {{-- DETAIL ITEM --}}
                            <td>

                                <div class="d-flex flex-column gap-2">

                                    @foreach($invoice->items as $item)

                                        <div class="border rounded-3 p-2 bg-light">

                                            <div class="fw-semibold text-dark small mb-1">

                                                {{ $item->description }}

                                            </div>

                                            <div class="small text-muted">

                                                Qty:
                                                <b>{{ $item->qty }}</b>

                                                •

                                                Harga:
                                                <b>

                                                    Rp {{ number_format($item->price,0,',','.') }}

                                                </b>

                                            </div>

                                            <div class="small text-muted">

                                                Durasi:
                                                <b>

                                                    @if($item->duration)

                                                    {{ $item->duration }}
                                                    {{ ucfirst($item->duration_type) }}

                                                @else

                                                    -

                                                @endif

                                                </b>

                                            </div>

                                            <div class="small text-secondary">

                                                @if($item->start_date && $item->end_date)

                                                    {{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}

                                                    -

                                                    {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}

                                                @else

                                                    -

                                                @endif

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </td>

                           {{-- TOTAL --}}

@php

    $cashbackAmount =
        ($invoice->grand_total * $invoice->cashback) / 100;

@endphp

<td>

    {{-- SUBTOTAL --}}
    <div class="small mb-2">

        <span class="text-muted">

            Subtotal

        </span>

        <br>

        <span class="fw-semibold text-dark">

            Rp {{ number_format($invoice->subtotal,0,',','.') }}

        </span>

    </div>

    {{-- PPN --}}
    <div class="small mb-2">

        <span class="text-muted">

            PPN ({{ $invoice->vat_percent }}%)

        </span>

        <br>

        <span class="fw-semibold text-primary">

            Rp {{ number_format($invoice->vat,0,',','.') }}

        </span>

    </div>
    {{-- BIAYA LAYANAN --}}
    <div class="small mb-2">

        <span class="text-muted">

            Biaya Layanan

        </span>

        <br>

        <span class="fw-semibold text-secondary">

            Rp {{ number_format($invoice->service_fee,0,',','.') }}

        </span>

    </div>

    <hr class="my-2">

    {{-- GRAND TOTAL --}}
    <div class="small mb-2">

        <span class="text-muted">

            Grand Total

        </span>

        <br>

        <span class="fw-bold text-primary">

            Rp {{ number_format($invoice->grand_total,0,',','.') }}

        </span>

    </div>

    {{-- CASHBACK --}}
    <div class="small mb-2">

        <span class="text-muted">

            Cashback {{ $invoice->cashback }}%

        </span>

        <br>

        <span class="fw-semibold text-success">

            Reward Rp {{ number_format($cashbackAmount,0,',','.') }}

        </span>

    </div>

    <hr class="my-2">

    {{-- TOTAL BAYAR --}}
   <div class="fw-bold text-success">

        Total Dibayar

        <br>

        Rp {{ number_format($invoice->grand_total,0,',','.') }}

    </div>

</td>


                            {{-- STATUS --}}
                            <td class="text-center">

                                @php

                                    $badgeClass = match($invoice->status) {
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'unpaid' => 'danger',
                                        default => 'secondary'
                                    };

                                @endphp

                                <span class="badge bg-{{ $badgeClass }} rounded-pill px-3 py-2">

                                    {{ ucfirst($invoice->status) }}

                                </span>

                            </td>

                            {{-- DUE DATE --}}
                            <td>

                                <div class="small fw-semibold text-dark">

                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}

                                </div>

                            </td>

                            {{-- AKSI --}}
                            <td>

                                <div class="d-grid gap-2">

                                    <a href="{{ route('invoices.pdf', $invoice->id) }}"
                                       class="btn btn-success btn-sm rounded-pill">

                                        <i class="bi bi-file-earmark-pdf me-1"></i>

                                        PDF

                                    </a>

                                    <a href="{{ route('invoices.edit', $invoice->id) }}"
                                       class="btn btn-warning btn-sm rounded-pill text-white">

                                        <i class="bi bi-pencil-square me-1"></i>

                                        Edit

                                    </a>

                                    <form action="{{ route('invoices.destroy', $invoice->id) }}"
                                          method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm rounded-pill w-100"
                                                onclick="return confirm('Yakin hapus invoice?')">

                                            <i class="bi bi-trash me-1"></i>

                                            Hapus

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center py-5 text-muted">

                                <i class="bi bi-receipt fs-1 d-block mb-3"></i>

                                Belum ada data invoice

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="px-4 py-4 border-top">

            <div class="d-flex justify-content-end">

                {{ $invoices->links() }}

            </div>

        </div>

    </div>

</div>

@endsection
