@extends('layouts.admin')

@section('page-title', 'Edit Invoice')

@section('admin-content')

<div class="card">

    <form action="{{ route('invoices.update', $invoice->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Client</label>

                    <select name="client_id"
                            class="form-control"
                            required>

                        @foreach($clients as $client)

                            <option value="{{ $client->id }}"
                                @selected($invoice->client_id == $client->id)>

                                {{ $client->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label>Project</label>

                    <select name="project_id"
                            class="form-control">

                        <option value="">
                            -- Pilih Project --
                        </option>

                        @foreach($projects as $project)

                            <option value="{{ $project->id }}"
                                @selected($invoice->project_id == $project->id)>

                                {{ $project->title }}

                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label>Invoice Number</label>

                    <input type="text"
                           name="invoice_number"
                           class="form-control"
                           value="{{ $invoice->invoice_number }}"
                           required>

                </div>

                <div class="col-md-4 mb-3">

                    <label>Tanggal Invoice</label>

                    <input type="date"
                           class="form-control"
                           value="{{ $invoice->created_at->format('Y-m-d') }}"
                           readonly>

                </div>

                <div class="col-md-4 mb-3">

                    <label>Due Date</label>

                    <input type="date"
                           name="due_date"
                           class="form-control"
                           value="{{ $invoice->due_date }}">

                </div>

            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h4>Item Invoice</h4>

                <button type="button"
                        class="btn btn-success btn-sm"
                        id="add-item">

                    + Tambah Item

                </button>

            </div>

            <div id="invoice-items">

                @foreach($invoice->items as $item)

                <div class="card mb-3 invoice-item">

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3 mb-2">

                                <label>Deskripsi</label>

                                <input type="text"
                                       name="description[]"
                                       class="form-control"
                                       value="{{ $item->description }}">

                            </div>

                            <div class="col-md-1 mb-2">

                                <label>Qty</label>

                                <input type="number"
                                       name="qty[]"
                                       class="form-control qty"
                                       value="{{ $item->qty }}">

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Harga</label>

                                <input type="number"
                                       name="price[]"
                                       class="form-control price"
                                       value="{{ $item->price }}">

                            </div>

                            <div class="col-md-1 mb-2">

                                <label>Durasi</label>

                                <input type="number"
                                       name="duration[]"
                                       class="form-control duration"
                                       value="{{ $item->duration }}">

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Jenis</label>

                                <select name="duration_type[]"
                                        class="form-control duration-type">

                                    <option value="Hari"
                                        @selected($item->duration_type == 'Hari')>

                                        Hari

                                    </option>

                                    <option value="Bulan"
                                        @selected($item->duration_type == 'Bulan')>

                                        Bulan

                                    </option>

                                    <option value="Tahun"
                                        @selected($item->duration_type == 'Tahun')>

                                        Tahun

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Total</label>

                                <input type="number"
                                       class="form-control total"
                                       value="{{ $item->total }}"
                                       readonly>

                            </div>

                            <div class="col-md-1 mb-2 d-flex align-items-end">

                                <button type="button"
                                        class="btn btn-danger btn-sm remove-item">

                                    X

                                </button>

                            </div>

                        </div>

                        <div class="row mt-2">

                            <div class="col-md-3">

                                <label>Mulai Layanan</label>

                                <input type="date"
                                       name="start_date[]"
                                       class="form-control start-date"
                                       value="{{ $item->start_date }}">

                            </div>

                            <div class="col-md-3">

                                <label>Akhir Layanan</label>

                                <input type="date"
                                       name="end_date[]"
                                       class="form-control end-date"
                                       value="{{ $item->end_date }}"
                                       readonly>

                            </div>

                            <div class="col-md-3">

                                <label>Ringkasan</label>

                                <input type="text"
                                       class="form-control summary"
                                       value="{{ $item->duration }} {{ $item->duration_type }}"
                                       readonly>

                            </div>

                        </div>

                    </div>

                </div>

                @endforeach

            </div>

            <hr>

            <div class="row">

                <div class="col-md-4">

                    <label>PPN (%)</label>

                    <input type="number"
                           name="vat_percent"
                           class="form-control"
                           id="vat-percent"
                           value="{{ $invoice->vat_percent }}">

                </div>


<div class="col-md-4">

    <label>Biaya Layanan</label>

    <input type="number"
           name="service_fee"
           class="form-control"
           id="service-fee"
           value="{{ $invoice->service_fee }}">

</div>

<div class="col-md-4">

    <label>Cashback (%)</label>

    <input type="number"
           name="cashback"
           id="cashback"
           class="form-control"
           value="{{ $invoice->cashback ?? 0 }}"
           min="0"
           max="20">

</div>

<div class="col-md-4">

    <label>Status</label>

    <select name="status"
            class="form-control">

        <option value="unpaid"
            @selected($invoice->status == 'unpaid')>

            Unpaid

        </option>

        <option value="paid"
            @selected($invoice->status == 'paid')>

            Paid

        </option>

        <option value="partial"
            @selected($invoice->status == 'partial')>

            Partial

        </option>

        <option value="cancelled"
            @selected($invoice->status == 'cancelled')>

            Cancelled

        </option>

    </select>

</div>



            </div>

            <hr>


    <div class="text-right">

        <h5>

            Grand Total:
            Rp <span id="grand-total">

                {{ number_format($invoice->grand_total,0,',','.') }}

            </span>

        </h5>

        <h5 class="text-success">

            Cashback Reward:
            <span id="cashback-percent">

                {{ $invoice->cashback ?? 0 }}

            </span>%
            (
            Rp <span id="cashback-amount">

                0

            </span>
            )

        </h5>

    </div>

    <h3 class="text-primary">

        Total Bayar:
        Rp <span id="final-total">

            {{ number_format($invoice->grand_total,0,',','.') }}

        </span>

    </h3>




            <div class="mt-3">

                <label>Notes</label>

                <textarea name="notes"
                          class="form-control"
                          rows="4">{{ $invoice->notes }}</textarea>

            </div>

        </div>

        <div class="card-footer text-right">

            <button class="btn btn-primary">

                Update Invoice

            </button>

        </div>

    </form>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('cashback')
        .addEventListener('input', function(){

            if(this.value > 20){

                this.value = 20;

            }

        });

    function calculateTotals() {

        let subtotal = 0;

        document.querySelectorAll('.invoice-item').forEach(item => {

            let qty = parseFloat(
                item.querySelector('.qty').value
            ) || 0;

            let price = parseFloat(
                item.querySelector('.price').value
            ) || 0;

            let total = qty * price;

            item.querySelector('.total').value = total;

            subtotal += total;

            updateEndDate(item);

            updateSummary(item);

        });

        let vatPercent = parseFloat(
            document.getElementById('vat-percent').value
        ) || 0;

        let serviceFee = parseFloat(
            document.getElementById('service-fee').value
        ) || 0;

        let vat = subtotal * vatPercent / 100;

        let grandTotal = subtotal + vat + serviceFee;

        let cashback = parseFloat(
            document.getElementById('cashback').value
        ) || 0;

        let cashbackAmount =
            grandTotal * cashback / 100;



        // let finalTotal =
        //     grandTotal - cashbackAmount;

        document.getElementById('cashback-percent')
            .innerText = cashback;

        document.getElementById('cashback-amount')
            .innerText = cashbackAmount.toLocaleString('id-ID');


        document.getElementById('grand-total')
            .innerText = grandTotal.toLocaleString('id-ID');
    }

    function updateSummary(item) {

        let duration = item.querySelector('.duration').value;

        let type = item.querySelector('.duration-type').value;

        item.querySelector('.summary').value =
            duration + ' ' + type;
    }

    function updateEndDate(item) {

        let startDate = item.querySelector('.start-date').value;

        let duration = parseInt(
            item.querySelector('.duration').value
        ) || 0;

        let durationType = item.querySelector('.duration-type').value;

        if (!startDate) return;

        let date = new Date(startDate);

        if (durationType === 'Hari') {

            date.setDate(date.getDate() + duration);

        }

        if (durationType === 'Bulan') {

            date.setMonth(date.getMonth() + duration);

        }

        if (durationType === 'Tahun') {

            date.setFullYear(date.getFullYear() + duration);

        }

        let endDate = date.toISOString().split('T')[0];

        item.querySelector('.end-date').value = endDate;
    }

    document.addEventListener('input', calculateTotals);

    document.getElementById('add-item')
        .addEventListener('click', function () {

        let item = document.querySelector('.invoice-item')
            .cloneNode(true);

        item.querySelectorAll('input').forEach(input => {

            input.value = '';

        });

        item.querySelector('.qty').value = 1;
        item.querySelector('.duration').value = 1;

        document.getElementById('invoice-items')
            .appendChild(item);

    });

    document.addEventListener('click', function(e){

        if(e.target.classList.contains('remove-item')){

            if(document.querySelectorAll('.invoice-item').length > 1){

                e.target.closest('.invoice-item').remove();

                calculateTotals();

            }

        }

    });

    calculateTotals();

});


</script>

@endsection
