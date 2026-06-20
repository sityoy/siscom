@extends('layouts.admin')

@section('page-title', 'Tambah Client')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Tambah Client Baru

        </h4>

        <small class="text-muted">

            Tambahkan data client untuk kebutuhan project & invoice

        </small>

    </div>

    <form action="{{ route('clients.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            <div class="row">

                {{-- NAMA --}}
                <div class="col-md-6 mb-3">

                    <label>Nama Client</label>

                        <select name="user_id"
                            id="user-select"
                            class="form-control">

                        <option value="">
                            Pilih User
                        </option>

                        @foreach($users as $user)

                            <option
                                value="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}">

                                {{ $user->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- PERUSAHAAN --}}
                <div class="col-md-6 mb-3">

                    <label>Perusahaan</label>

                    <input type="text"
                           name="company"
                           class="form-control"
                           placeholder="Nama perusahaan">

                </div>

            </div>

            <div class="row">

                {{-- EMAIL --}}
                <div class="col-md-6 mb-3">

                    <label>Email</label>

                    <input
                        type="text"
                        name="name"
                        id="client-name"
                        readonly hidden>

                    <input type="email"
                           name="email"
                           id="client-email"
                           class="form-control"
                           readonly>

                </div>

                {{-- WHATSAPP --}}
                <div class="col-md-6 mb-3">

                    <label>No WhatsApp</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           placeholder="62xxxxxxxxxx" maxlength="13">

                </div>

            </div>

            {{-- ALAMAT --}}
            <div class="mb-3">

                <label>Alamat</label>

                <textarea name="address"
                          class="form-control"
                          rows="4"
                          placeholder="Masukkan alamat client"></textarea>

            </div>
<hr>

<h5 class="mb-3">
    Informasi Langganan
</h5>

<div class="row">

    <div class="col-md-6 mb-3">

        <label>Paket</label>

        <select
            name="package_name"
            class="form-control">

            <option value="">
                Pilih Paket
            </option>

            <option value="Basic">
                Basic
            </option>

            <option value="Standard">
                Standard
            </option>

            <option value="Premium">
                Premium
            </option>

        </select>

    </div>

    <div class="col-md-6 mb-3">

        <label>Harga Paket</label>

        <input
            type="number"
            name="package_price"
            class="form-control"
            placeholder="1500000">

    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">

        <label>Tanggal Mulai</label>

        <input
            type="date"
            name="subscription_start"
            class="form-control">

    </div>

    <div class="col-md-4 mb-3">

        <label>Tanggal Berakhir</label>

        <input
            type="date"
            name="subscription_end"
            class="form-control">

    </div>

    <div class="col-md-4 mb-3">

        <label>Masa Tenggang (Hari)</label>

        <input
            type="number"
            name="grace_period_days"
            class="form-control"
            value="7">

    </div>

</div>
        </div>
        

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('clients.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Simpan Client

            </button>

        </div>

    </form>

</div>

<script>
document
    .getElementById('user-select')
    .addEventListener('change', function(){

        let option =
            this.options[this.selectedIndex];

        document
            .getElementById('client-name')
            .value =
            option.dataset.name || '';

        document
            .getElementById('client-email')
            .value =
            option.dataset.email || '';

    });
</script>

@endsection
