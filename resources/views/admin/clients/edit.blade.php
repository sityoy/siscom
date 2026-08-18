@extends('layouts.admin')

@section('page-title', 'Edit Client')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Edit Client

        </h4>

        <small class="text-muted">

            Perbarui informasi client SIS.COM

        </small>

    </div>

    <form action="{{ route('clients.update', $client->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                {{-- NAMA --}}
                <div class="col-md-6 mb-3">

                <label>User</label>

                <select name="user_id"
                        class="form-control">

                    @foreach($users as $user)

                        <option value="{{ $user->id }}"
                            {{ $client->user_id == $user->id ? 'selected' : '' }}>

                            {{ $user->name }}

                        </option>

                    @endforeach

                </select>

            </div>



                <label hidden>Nama User</label>

                <input type="text"
                    class="form-control"
                    value="{{ $client->user?->name }}"
                    readonly hidden>



                {{-- PERUSAHAAN --}}
                <div class="col-md-6 mb-3">

                    <label>Perusahaan</label>

                    <input type="text"
                           name="company"
                           class="form-control"
                           value="{{ old('company', $client->company) }}">

                </div>

            </div>

            <div class="row">

                {{-- EMAIL --}}
                <div class="col-md-6 mb-3">

                    <label>Email</label>

                    <input type="email"
                    name="email"
                    class="form-control"
                    value="{{ $client->user?->email }}"
                    readonly>

                </div>

            </div>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label>WhatsApp 1 (Utama)</label>

                    <input type="text"
                           name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $client->phone) }}"
                           placeholder="62xxxxxxxxxx"
                           maxlength="20">

                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <div class="col-md-4 mb-3">

                    <label>WhatsApp 2</label>

                    <input type="text"
                           name="phone_2"
                           class="form-control @error('phone_2') is-invalid @enderror"
                           value="{{ old('phone_2', $client->phone_2) }}"
                           placeholder="62xxxxxxxxxx"
                           maxlength="20">

                    @error('phone_2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <div class="col-md-4 mb-3">

                    <label>WhatsApp 3</label>

                    <input type="text"
                           name="phone_3"
                           class="form-control @error('phone_3') is-invalid @enderror"
                           value="{{ old('phone_3', $client->phone_3) }}"
                           placeholder="62xxxxxxxxxx"
                           maxlength="20">

                    @error('phone_3')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

            </div>

            <small class="form-text text-muted mb-3">
                Gunakan format 62. Pengiriman otomatis dilakukan berurutan dengan jeda 5–10 menit.
            </small>

            {{-- ALAMAT --}}
            <div class="mb-3">

                <label>Alamat</label>

                <textarea name="address"
                          class="form-control"
                          rows="4">{{ old('address', $client->address) }}</textarea>

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

            <option value="Basic"
                {{ $client->package_name == 'Basic' ? 'selected' : '' }}>
                Basic
            </option>

            <option value="Standard"
                {{ $client->package_name == 'Standard' ? 'selected' : '' }}>
                Standard
            </option>

            <option value="Premium"
                {{ $client->package_name == 'Premium' ? 'selected' : '' }}>
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
            value="{{ old('package_price', $client->package_price) }}">

    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">

        <label>Tanggal Mulai</label>

        <input
            type="date"
            name="subscription_start"
            class="form-control"
            value="{{ old('subscription_start', optional($client->subscription_start)->format('Y-m-d')) }}">

    </div>

    <div class="col-md-4 mb-3">

        <label>Tanggal Berakhir</label>

        <input
            type="date"
            name="subscription_end"
            class="form-control"
            value="{{ old('subscription_end', optional($client->subscription_end)->format('Y-m-d')) }}">

    </div>

    <div class="col-md-4 mb-3">

        <label>Masa Tenggang (Hari)</label>

        <input
            type="number"
            name="grace_period_days"
            class="form-control"
            value="{{ old('grace_period_days', $client->grace_period_days ?? 7) }}">

    </div>

</div>
        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('clients.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Update Client

            </button>

        </div>

    </form>

</div>

@endsection
