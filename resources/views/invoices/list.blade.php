@php
    $listRoute = $adminView ? 'invoices.index' : 'client.invoices';
    $pdfRoute = $adminView ? 'invoices.pdf' : 'client.invoices.pdf';
    $types = [
        '' => ['Semua', 'secondary'],
        'project' => ['Jasa/Sewa', 'primary'],
        'general' => ['Umum', 'success'],
        'spj' => ['SPJ/Rekanan', 'warning'],
        'renewal' => ['Perpanjangan Paket', 'info'],
    ];
    $rupiah = static fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 px-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">{{ $adminView ? 'Invoice Management' : 'Invoice Saya' }}</h3>
                <p class="text-muted mb-0">Total Invoice: <strong>{{ $invoices->total() }}</strong></p>
            </div>
            @if($adminView)
                <div class="btn-group">
                    <a href="{{ route('invoices.create', ['type' => 'general']) }}" class="btn btn-primary rounded-left px-3">
                        <i class="fas fa-plus-circle mr-1"></i> Invoice Umum
                    </a>
                    <a href="{{ route('invoices.create', ['type' => 'spj']) }}" class="btn btn-warning rounded-right px-3">
                        <i class="fas fa-file-contract mr-1"></i> Invoice SPJ
                    </a>
                </div>
            @endif
        </div>
    </div>

    <nav class="px-4 mb-3 d-flex flex-wrap gap-2" aria-label="Filter jenis invoice">
        @foreach($types as $type => [$label, $color])
            @php($selected = request('type', '') === $type)
            <a href="{{ route($listRoute, array_filter(['type' => $type, 'per_page' => request('per_page')])) }}"
               class="btn btn-{{ $color }} btn-sm mr-1 mb-1{{ $selected ? ' active' : '' }}"
               @if($selected) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col">Invoice</th>
                    <th scope="col">Klien</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Proyek</th>
                    <th scope="col">Detail Item</th>
                    <th scope="col">Total</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col">Jatuh Tempo</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="text-center">{{ $invoices->firstItem() + $loop->index }}</td>
                        <td>
                            <strong class="text-dark">{{ $invoice->invoice_number }}</strong>
                            <small class="d-block text-muted">{{ ($invoice->invoice_date ?? $invoice->created_at)?->format('d M Y') ?? '—' }}</small>
                        </td>
                        <td>
                            <strong>{{ $invoice->client?->name ?? '—' }}</strong>
                            <small class="text-muted text-break d-block">{{ $invoice->client?->email }}</small>
                        </td>
                        <td>
                            <span class="badge badge-{{ $types[$invoice->invoice_type][1] ?? 'secondary' }}">{{ $invoice->type_label }}</span>
                        </td>
                        <td>{{ $invoice->project?->title ?? '—' }}</td>
                        <td>
                            @forelse($invoice->items as $item)
                                <div class="border rounded p-2 mb-1 bg-light small">
                                    <strong class="d-block text-dark">{{ $item->description }}</strong>
                                    <div class="text-muted">{{ $item->type_label }} · {{ $item->qty }} {{ $item->unit ?: 'Paket' }} × {{ $rupiah($item->price) }}</div>
                                    @if($item->specification)
                                        <div>Spesifikasi: {{ \Illuminate\Support\Str::limit($item->specification, 100) }}</div>
                                    @endif
                                    @if($item->duration)
                                        <div>Durasi: {{ $item->duration }} {{ ucfirst($item->duration_type ?? '') }}</div>
                                    @endif
                                    @if($item->start_date && $item->end_date)
                                        <div class="text-muted">{{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</div>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="small mb-2">Subtotal <strong class="d-block">{{ $rupiah($invoice->subtotal) }}</strong></div>
                            <div class="small mb-2">PPN ({{ number_format((float) $invoice->vat_percent, 0) }}%) <strong class="d-block">{{ $rupiah($invoice->vat) }}</strong></div>
                            <div class="small mb-2">Biaya Layanan <strong class="d-block">{{ $rupiah($invoice->service_fee) }}</strong></div>
                            <hr class="my-2">
                            <div class="small mb-2">Grand Total <strong class="d-block text-primary">{{ $rupiah($invoice->grand_total) }}</strong></div>
                            @if($invoice->late_fee_amount > 0)
                                <div class="small mb-2">Denda ({{ $invoice->late_months }} bulan) <strong class="d-block text-danger">{{ $rupiah($invoice->late_fee_amount) }}</strong></div>
                            @endif
                            @if($adminView)
                                <div class="small mb-2">Cashback {{ number_format((float) $invoice->cashback, 0) }}%
                                    <strong class="d-block text-success">Reward {{ $rupiah((float) $invoice->grand_total * (float) $invoice->cashback / 100) }}</strong>
                                </div>
                            @endif
                            <hr class="my-2">
                            <strong class="text-success">
                                {{ $invoice->income_tax_withheld ? 'Pembayaran Bersih' : 'Total Bayar' }}
                                <span class="d-block">{{ $rupiah($invoice->income_tax_withheld ? $invoice->net_receivable : $invoice->total_due) }}</span>
                            </strong>
                            @if($invoice->income_tax_withheld && (float) $invoice->income_tax_amount > 0)
                                <small class="d-block text-danger">PPh dipotong {{ $rupiah($invoice->income_tax_amount) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$invoice->status] ?? 'secondary' }} rounded-pill px-3 py-2">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="small">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span>
                            @if($invoice->late_months > 0)
                                <small class="d-block text-danger">Terlambat {{ $invoice->late_months }} bulan</small>
                            @endif
                            @if($invoice->late_fee_active)
                                <small class="d-block text-muted">Denda {{ $rupiah($invoice->late_fee_per_month) }}/30 hari</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a class="btn btn-success btn-sm rounded-pill mb-1" href="{{ route($pdfRoute, ['invoice'=>$invoice->id, 'lang'=>'id']) }}">PDF Indonesia</a>
                                <a class="btn btn-outline-success btn-sm rounded-pill mb-1" href="{{ route($pdfRoute, ['invoice'=>$invoice->id, 'lang'=>'en']) }}">PDF English</a>
                                @if($adminView)
                                    <a class="btn btn-warning btn-sm rounded-pill mb-1" href="{{ route('invoices.edit', $invoice->id) }}">Edit</a>
                                    @if($invoice->status !== 'cancelled' || $invoice->agreement_exists)
                                        <a class="btn btn-outline-primary btn-sm rounded-pill mb-1" href="{{ route('agreements.create', $invoice) }}">{{ $invoice->agreement_exists ? 'Lihat MoU' : 'Buat MoU' }}</a>
                                    @endif
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm rounded-pill w-100" onclick="return confirm('Yakin hapus invoice?')">Hapus</button>
                                    </form>
                                @elseif($invoice->agreement)
                                    <a class="btn btn-outline-primary btn-sm rounded-pill" href="{{ route('client.agreements.show', $invoice->agreement->id) }}">Lihat MoU</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center py-5 text-muted">Belum ada invoice untuk pilihan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-4 border-top">
        @include('layouts.partials.pagination', ['paginator' => $invoices])
    </div>
</div>
