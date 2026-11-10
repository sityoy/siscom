@extends('layouts.admin')

@section('page-title', 'Dokumen Pajak Rekanan')

@section('admin-content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Periksa kembali data berikut:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="font-weight-bold mb-0">Status Pajak Rekanan</h5></div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h4 class="font-weight-bold">{{ $company->tax_status_label }}</h4>
                    <span class="badge badge-{{ $company->pp55_active ? 'success' : 'secondary' }} p-2">
                        PP 55 {{ $company->pp55_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <dl class="row mb-0">
                    <dt class="col-7">PPh Final UMKM</dt>
                    <dd class="col-5 text-right font-weight-bold">{{ number_format($company->income_tax_rate ?? 0.5, 2, ',', '.') }}%</dd>
                    <dt class="col-7">PPN Dipungut</dt>
                    <dd class="col-5 text-right font-weight-bold">{{ $company->isPkp() ? 'Ya' : 'Tidak' }}</dd>
                    <dt class="col-7">Faktur Pajak</dt>
                    <dd class="col-5 text-right font-weight-bold">{{ $company->isPkp() ? 'Diterbitkan' : 'Tidak' }}</dd>
                </dl>

                <hr>
                <div class="alert alert-info mb-0">
                    Jika sekolah/bendahara merupakan pemotong pajak, serahkan Surat Keterangan PP 55 agar PPh dipotong final 0,5% dari nilai bruto dan mintalah bukti potong.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="font-weight-bold mb-0">Arsip BPE dan Surat Keterangan PP 55</h5></div>
            <form method="POST" enctype="multipart/form-data"
                  action="{{ route('procurement-documents.tax-documents.update') }}">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Status PKP</label>
                            <select name="tax_status" class="form-control" required>
                                <option value="non_pkp" @selected(old('tax_status', $company->tax_status) === 'non_pkp')>Non-PKP</option>
                                <option value="pkp" @selected(old('tax_status', $company->tax_status) === 'pkp')>PKP</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Skema PPh</label>
                            <select name="income_tax_scheme" class="form-control" required>
                                <option value="umkm_final" @selected(old('income_tax_scheme', $company->income_tax_scheme) === 'umkm_final')>PPh Final UMKM</option>
                                <option value="normal" @selected(old('income_tax_scheme', $company->income_tax_scheme) === 'normal')>PPh Normal/Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tarif PPh (%)</label>
                            <input type="number" name="income_tax_rate" class="form-control" min="0" max="100" step="0.01"
                                   value="{{ old('income_tax_rate', $company->income_tax_rate ?? 0.50) }}" required>
                        </div>
                    </div>

                    <input type="hidden" name="pp55_active" value="0">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="pp55-active" name="pp55_active" value="1"
                               @checked(old('pp55_active', $company->pp55_active))>
                        <label class="custom-control-label font-weight-bold" for="pp55-active">Surat Keterangan PP 55 masih aktif</label>
                    </div>

                    <h6 class="font-weight-bold text-primary">Data Permohonan/BPE</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Nomor Kasus Coretax</label>
                            <input type="text" name="pp55_case_number" class="form-control"
                                   value="{{ old('pp55_case_number', $company->pp55_case_number) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Nomor BPE</label>
                            <input type="text" name="pp55_bpe_number" class="form-control"
                                   value="{{ old('pp55_bpe_number', $company->pp55_bpe_number) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Permohonan/Berlaku</label>
                            <input type="date" name="pp55_effective_date" class="form-control"
                                   value="{{ old('pp55_effective_date', $company->pp55_effective_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label>File BPE (PDF)</label>
                            <input type="file" name="pp55_document" class="form-control" accept="application/pdf">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            @if($company->pp55_document)
                                <a href="{{ route('procurement-documents.tax-documents.download', 'bpe') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-download mr-1"></i> Unduh BPE
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-success">Surat Keterangan PP 55</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Nomor Surat Keterangan</label>
                            <input type="text" name="pp55_certificate_number" class="form-control"
                                   value="{{ old('pp55_certificate_number', $company->pp55_certificate_number) }}"
                                   placeholder="Contoh: KET-xxxxx/PP23-CT/KPP.xxxx/2026">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Surat</label>
                            <input type="date" name="pp55_certificate_date" class="form-control"
                                   value="{{ old('pp55_certificate_date', $company->pp55_certificate_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label>File Surat Keterangan (PDF)</label>
                            <input type="file" name="pp55_certificate_document" class="form-control" accept="application/pdf">
                            <small class="text-muted">Dokumen disimpan privat dan hanya dapat diunduh oleh admin.</small>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            @if($company->pp55_certificate_document)
                                <a href="{{ route('procurement-documents.tax-documents.download', 'certificate') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-download mr-1"></i> Unduh Suket
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Catatan Internal</label>
                        <textarea name="pp55_notes" class="form-control" rows="3"
                                  placeholder="Catatan masa berlaku, konfirmasi KPP, atau informasi bukti potong">{{ old('pp55_notes', $company->pp55_notes) }}</textarea>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Profil Pajak</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
