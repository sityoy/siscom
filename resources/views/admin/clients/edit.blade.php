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

                    <label>Nama Client</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $client->name) }}"
                           required>

                </div>

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
                           value="{{ old('email', $client->email) }}">

                </div>

                {{-- WHATSAPP --}}
                <div class="col-md-6 mb-3">

                    <label>No WhatsApp</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone', $client->phone) }}">

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
