@extends('layouts.admin')

@section('page-title', 'Tambah Invoice')

@section('admin-content')

<div class="card">

    <form action="{{ route('invoices.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Client</label>

                    <select name="client_id"
                            class="form-control"
                            required>

                        <option value="">
                            -- Pilih Client --
                        </option>

                        @foreach($clients as $client)

                            <option value="{{ $client->id }}">

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

                            <option value="{{ $project->id }}">

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
                           value="#{{ date('dmY') }}-{{ rand(1000,9999) }}"
                           required>

                </div>

                <div class="col-md-4 mb-3">

                    <label>Tanggal Invoice</label>

                    <input type="date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}"
                           readonly>

                </div>

                <div class="col-md-4 mb-3">

                    <label>Due Date</label>

                    <input type="date"
                           name="due_date"
                           class="form-control">

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

                <div class="card mb-3 invoice-item">

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3 mb-2">

                                <label>Deskripsi</label>

                                <input type="text"
                                       name="description[]"
                                       class="form-control"
                                       placeholder="Deskripsi layanan">

                            </div>

                            <div class="col-md-1 mb-2">

                                <label>Qty</label>

                                <input type="number"
                                       name="qty[]"
                                       class="form-control qty"
                                       value="1">

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Harga</label>

                                <input type="number"
                                       name="price[]"
                                       class="form-control price"
                                       placeholder="Harga">

                            </div>

                            <div class="col-md-1 mb-2">

                                <label>Durasi</label>

                                <input type="number"
                                       name="duration[]"
                                       class="form-control duration"
                                       value="1">

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Jenis</label>

                                <select name="duration_type[]"
                                        class="form-control duration-type">

                                    <option value="Hari">

                                        Hari

                                    </option>

                                    <option value="Bulan">

                                        Bulan

                                    </option>

                                    <option value="Tahun">

                                        Tahun

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-2 mb-2">

                                <label>Total</label>

                                <input type="number"
                                       class="form-control total"
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
                                       class="form-control start-date">

                            </div>

                            <div class="col-md-3">

                                <label>Akhir Layanan</label>

                                <input type="date"
                                       name="end_date[]"
                                       class="form-control end-date"
                                       readonly>

                            </div>

                            <div class="col-md-3">

                                <label>Ringkasan</label>

                                <input type="text"
                                       class="form-control summary"
                                       readonly>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-4">

                    <label>PPN (%)</label>

                    <input type="number"
                           name="vat_percent"
                           class="form-control"
                           id="vat-percent"
                           value="11">

                </div>

                <div class="col-md-4">

                    <label>Biaya Layanan</label>

                    <input type="number"
                           name="service_fee"
                           class="form-control"
                           id="service-fee"
                           value="10000">

                </div>
                <div class="col-md-4">

                <label>Cashback (%)</label>

                    <input type="number"
                        name="cashback"
                        id="cashback"
                        class="form-control"
                        value="0" max="20">

                </div>

                <div class="col-md-4">

                    <label>Status</label>

                    <select name="status"
                            class="form-control">

                        <option value="unpaid">

                            Unpaid

                        </option>

                        <option value="paid">

                            Paid

                        </option>

                        <option value="partial">

                            Partial

                        </option>

                        <option value="cancelled">

                            Cancelled

                        </option>

                    </select>

                </div>

            </div>

            <hr>

            <h3 class="text-right">

                Grand Total:
                Rp <span id="grand-total">

                    0

                </span>

            </h3>

            <div class="mt-3">

                <label>Notes</label>

                <textarea name="notes"
                          class="form-control"
                          rows="4"></textarea>

            </div>

        </div>

        <div class="card-footer text-right">

            <button class="btn btn-primary">

                Simpan Invoice

            </button>

        </div>

    </form>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    function calculateTotals() {

        let subtotal = 0;
        let cashback = parseFloat(
                document.getElementById('cashback').value
            ) || 0;


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

        let cashbackAmount =
            (subtotal + vat + serviceFee) * cashback / 100;

        let grandTotal =
            subtotal + vat + serviceFee - cashbackAmount;

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
