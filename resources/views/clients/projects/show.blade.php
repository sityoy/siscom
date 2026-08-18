@extends('layouts.client')

@section('page-title', 'Detail Project')

@section('client-content')

<div class="row">

    {{-- PROJECT DETAIL --}}
    <div class="col-lg-8 mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex
                            justify-content-between
                            align-items-start">

                    <div>

                        <h3 class="font-weight-bold">

                            {{ $project->title }}

                        </h3>

                        <small class="text-muted">

                            Dibuat:
                            {{ $project->created_at->format('d M Y') }}

                        </small>

                    </div>

                    <div>

                        @if($project->status == 'completed')

                            <span class="badge bg-success">

                                Completed

                            </span>

                        @elseif($project->status == 'pending')

                            <span class="badge bg-warning">

                                Pending

                            </span>

                        @else

                            <span class="badge bg-primary">

                                Progress

                            </span>

                        @endif

                    </div>

                </div>

                <hr>

                <h5 class="font-weight-bold mb-3">

                    Deskripsi Project

                </h5>

                <p class="text-muted">

                    {{ $project->description ?? '-' }}

                </p>

                {{-- PROGRESS --}}
                <div class="mt-4">

                    <div class="d-flex
                                justify-content-between
                                mb-2">

                        <strong>

                            Progress

                        </strong>

                        <strong class="text-primary">

                            {{ $project->progress ?? 0 }}%

                        </strong>

                    </div>

                    <div class="progress"
                         style="height:12px;
                                border-radius:20px;">

                        <div class="progress-bar bg-primary"
                             style="
                                width:
                                {{ $project->progress ?? 0 }}%;
                             ">

                        </div>

                    </div>

                </div>

                {{-- DEADLINE --}}
                <div class="mt-4">

                    <small class="text-muted d-block">

                        Deadline

                    </small>

                    <strong>

                        {{ $project->deadline
                            ? \Carbon\Carbon::parse($project->deadline)->format('d M Y')
                            : '-' }}

                    </strong>

                </div>

                <div class="mt-4">

                    <small class="text-muted d-block">
                        Denda Keterlambatan Pembayaran
                    </small>

                    @if($project->late_fee_active)

                        <strong class="text-danger">
                            Rp {{ number_format($project->late_fee_per_month,0,',','.') }}
                            per 30 hari
                        </strong>

                    @else

                        <strong class="text-muted">Tidak aktif</strong>

                    @endif

                </div>

            </div>

        </div>

    </div>

    {{-- SIDEBAR --}}
    <div class="col-lg-4">

        {{-- FILES --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0 font-weight-bold">

                    Project Files

                </h5>

            </div>

            <div class="card-body">

                @forelse($project->files as $file)

                    <div class="d-flex
                                justify-content-between
                                align-items-center
                                mb-3">

                        <div>

                            <strong>

                                {{ $file->file_name }}

                            </strong>

                        </div>

                        <a href="{{ asset('storage/' . $file->file_path) }}"
                           target="_blank"
                           class="btn btn-primary btn-sm">

                            Download

                        </a>

                    </div>

                @empty

                    <p class="text-muted mb-0">

                        Belum ada file.

                    </p>

                @endforelse

            </div>

        </div>

        {{-- INVOICES --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0 font-weight-bold">

                    Invoice Project

                </h5>

            </div>

            <div class="card-body">

                @forelse($project->invoices as $invoice)

                    <div class="mb-3">

                        <strong>

                            {{ $invoice->invoice_number }}

                        </strong>

                        <br>

                        <small class="text-success">

                            Rp {{ number_format($invoice->total_due,0,',','.') }}

                            @if($invoice->late_fee_amount > 0)

                                <span class="text-danger">
                                    (Denda Rp {{ number_format($invoice->late_fee_amount,0,',','.') }})
                                </span>

                            @endif

                        </small>

                    </div>

                @empty

                    <p class="text-muted mb-0">

                        Belum ada invoice.

                    </p>

                @endforelse

            </div>

        </div>

    </div>

</div>

@endsection
