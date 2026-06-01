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

                {{-- WHATSAPP --}}
                <div class="col-md-6 mb-3">

                    <label>No WhatsApp</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone', $client->phone) }}" maxlength="13">

                </div>

            </div>

            {{-- ALAMAT --}}
            <div class="mb-3">

                <label>Alamat</label>

                <textarea name="address"
                          class="form-control"
                          rows="4">{{ old('address', $client->address) }}</textarea>

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
