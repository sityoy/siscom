@extends('layouts.admin')
@section('page-title', 'Proposal Paket Sekolah')
@section('admin-content')
<div class="card"><div class="card-body">
<h1 class="h4">Proposal paket sekolah</h1>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('school-registrations.proposal.pdf') }}">@csrf
<label>Nama sekolah <input class="form-control" required name="school" maxlength="150" value="{{ old('school') }}"></label>
<label>Tanggal proposal <input class="form-control" required type="date" name="date" value="{{ old('date', today()->toDateString()) }}"></label>
<label>Periode harga <select class="form-control" name="period">@foreach(['bulan','semester','tahun'] as $period)<option value="{{ $period }}" @selected(old('period')===$period)>Per {{ $period }}</option>@endforeach</select></label>
<p>Pilih paket yang akan ditawarkan. Isi harga masing-masing secara manual atau kosongkan untuk dibahas bersama sekolah.</p>
@foreach($packages as $key=>[$name,$description])
<div class="border-top py-2"><label><input type="checkbox" name="packages[]" value="{{ $key }}" @checked(in_array($key,old('packages',array_keys($packages))))> {{ $name }}</label>
<label class="ml-3">Tarif (Rp) <input class="form-control" type="number" min="0" max="9999999999999" name="prices[{{ $key }}]" value="{{ old('prices.'.$key) }}"></label><p>{{ $description }}</p></div>
@endforeach
<fieldset class="border rounded p-3 my-3">
<legend class="h6">Dasar tarif CBT Sekolah saja</legend>
<div class="form-row">
    <div class="col-md"><label for="cbt-basis">Metode tarif</label><select class="form-control" id="cbt-basis" name="cbt_basis">
        @foreach($cbtBases as $key => $label)
            <option value="{{ $key }}" @selected(old('cbt_basis','kesepakatan') === $key)>{{ $label }}</option>
        @endforeach
    </select></div>
    <div class="col-md"><label for="cbt-units">Jumlah siswa atau ujian <small>(opsional)</small></label><input class="form-control" id="cbt-units" type="number" min="1" max="100000" name="cbt_units" value="{{ old('cbt_units') }}"></div>
</div>
<label class="mt-2 d-block">Ketentuan harga CBT <textarea class="form-control" name="cbt_note" rows="2" maxlength="500">{{ old('cbt_note') }}</textarea></label>
</fieldset>
<fieldset><legend class="h6">Modul untuk paket CBT + Modul Pilihan</legend>@foreach($modules as $key=>$label)<label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key,old('modules',[])))> {{ $label }}</label>@endforeach</fieldset>
<label>Nama penandatangan sekolah <input class="form-control" name="signer" value="{{ old('signer') }}"></label>
<label>Jabatan <input class="form-control" required name="title" value="{{ old('title','Kepala Sekolah') }}"></label>
<button class="btn btn-primary">Unduh proposal PDF</button></form></div></div>
@endsection
