@extends('layouts.client')

@section('page-title', 'Invoice Saya')

@section('client-content')

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0">

        <h4 class="mb-0 font-weight-bold">

            Daftar Invoice Saya

        </h4>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Invoice</th>
                        <th>Project</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>PDF</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($invoices as $invoice)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <div class="font-weight-bold">

                                    {{ $invoice->invoice_number }}

                                </div>

                                <small class="text-muted">

                                    {{ $invoice->created_at->format('d M Y') }}

                                </small>

                            </td>

                            <td>

                                {{ $invoice->project?->title ?? '-' }}

                            </td>

                            <td class="text-success font-weight-bold">

                                Rp {{ number_format($invoice->total_due,0,',','.') }}

                                @if($invoice->late_fee_amount > 0)

                                    <small class="d-block text-danger">
                                        Termasuk denda Rp {{ number_format($invoice->late_fee_amount,0,',','.') }}
                                    </small>

                                @endif

                            </td>

                            <td>

                                @if($invoice->status == 'paid')

                                    <span class="badge bg-success">

                                        Paid

                                    </span>

                                @elseif($invoice->status == 'partial')

                                    <span class="badge bg-warning">

                                        Partial

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Unpaid

                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('client.invoices.pdf', $invoice->id) }}"
                                   class="btn btn-success btn-sm">

                                    Download PDF

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-5">

                                Belum ada invoice 😄🔥

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">

            {{ $invoices->links() }}

        </div>

    </div>

</div>

@endsection
