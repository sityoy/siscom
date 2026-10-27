@extends('layouts.admin')

@section('page-title', 'Company Settings')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4>Pengaturan Perusahaan</h4>

    </div>

    <form action="{{ route('settings.update') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Nama Perusahaan</label>

                    <input type="text"
                           name="company_name"
                           class="form-control"
                           value="{{ $setting->company_name }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Email</label>

                    <input type="email"
                           name="company_email"
                           class="form-control"
                           value="{{ $setting->company_email }}">
                </div>

            </div>

            <div class="mb-3">

                <label>Nama Legal/Badan Usaha</label>

                <input type="text"
                       name="legal_name"
                       class="form-control"
                       value="{{ old('legal_name', $setting->legal_name) }}"
                       placeholder="Contoh: PT Sityoy IT Solutions">

            </div>

            <div class="mb-3">

                <label>No WhatsApp</label>

                <input type="text"
                       name="company_phone"
                       class="form-control"
                       value="{{ $setting->company_phone }}">

            </div>

            <hr>

            <h5>Legalitas dan Penandatangan Rekanan</h5>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>NIB</label>
                    <input type="text" name="nib" class="form-control"
                           value="{{ old('nib', $setting->nib) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>NPWP</label>
                    <input type="text" name="npwp" class="form-control"
                           value="{{ old('npwp', $setting->npwp) }}">
                </div>

                <div class="col-md-5 mb-3">
                    <label>Nama Penandatangan</label>
                    <input type="text" name="director_name" class="form-control"
                           value="{{ old('director_name', $setting->director_name) }}"
                           placeholder="Nama direktur/pimpinan">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Jabatan</label>
                    <input type="text" name="director_title" class="form-control"
                           value="{{ old('director_title', $setting->director_title) }}"
                           placeholder="Direktur/Pimpinan">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Kota Penandatanganan</label>
                    <input type="text" name="city" class="form-control"
                           value="{{ old('city', $setting->city) }}"
                           placeholder="Tangerang">
                </div>

            </div>

            <div class="mb-3">

                <label>Instagram</label>

                <input type="text"
                    name="instagram"
                    class="form-control"
                    value="{{ $setting->instagram }}">

            </div>

            <div class="mb-3">

                <label>LinkedIn</label>

                <input type="text"
                    name="linkedin"
                    class="form-control"
                    value="{{ $setting->linkedin }}">

            </div>

            <div class="mb-3">

                <label>Facebook</label>

                <input type="text"
                    name="facebook"
                    class="form-control"
                    value="{{ $setting->facebook }}">

            </div>


            <div class="mb-3">

                <label>Alamat</label>

                <textarea
                    name="company_address"
                    class="form-control"
                    rows="3">{{ $setting->company_address }}</textarea>

            </div>

            <div class="mb-3">

                <label>Website</label>

                <input type="text"
                    name="website"
                    class="form-control"
                    value="{{ $setting->website }}">

            </div>

            <div class="mb-3">

                <label>Logo Perusahaan</label>

                <input type="file"
                    name="logo"
                    class="form-control">

                    @if($setting->logo)

                        <div class="mt-2">

                            <img
                                src="{{ asset('storage/'.$setting->logo) }}"
                                style="max-height:100px;">

                        </div>

                    @endif

                <small class="text-muted">

                Format:
                PNG, JPG, JPEG, WEBP
                (Maksimal 2 MB)

                </small>

            </div>

            <hr>

            <h5>Rekening Pembayaran</h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Bank Jakarta</label>

                    <input type="text"
                           name="bank_jakarta"
                           class="form-control"
                           value="{{ $setting->bank_jakarta }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Atas Nama</label>

                    <input type="text"
                           name="bank_jakarta_name"
                           class="form-control"
                           value="{{ $setting->bank_jakarta_name }}">

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Bank Mandiri</label>

                    <input type="text"
                           name="bank_mandiri"
                           class="form-control"
                           value="{{ $setting->bank_mandiri }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Atas Nama</label>

                    <input type="text"
                           name="bank_mandiri_name"
                           class="form-control"
                           value="{{ $setting->bank_mandiri_name }}">

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Bank BCA</label>

                    <input type="text"
                           name="bank_bca"
                           class="form-control"
                           value="{{ $setting->bank_bca }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Atas Nama</label>

                    <input type="text"
                           name="bank_bca_name"
                           class="form-control"
                           value="{{ $setting->bank_bca_name }}">

                </div>

            </div>

        </div>


        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('admin.dashboard') }}"
            class="btn btn-secondary">

                <i class="fas fa-arrow-left"></i>
                Kembali

            </a>

            <button class="btn btn-primary">

                <i class="fas fa-save"></i>
                Simpan Setting

            </button>

        </div>

    </form>

</div>

@endsection
