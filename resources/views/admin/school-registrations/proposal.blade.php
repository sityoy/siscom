@extends('layouts.admin')
@section('page-title', 'Proposal Paket Sekolah')
@section('admin-content')
<div class="card"><div class="card-body">
<h1 class="h4">Proposal paket sekolah</h1>
<form method="post" action="{{ route('school-registrations.proposal.pdf') }}">@csrf
<label>Nama sekolah <input class="form-control" required name="school" maxlength="150" value="{{ old('school') }}"></label>
<label>Tanggal proposal <input class="form-control" required type="date" name="date" value="{{ old('date', today()->toDateString()) }}"></label>
<label>Periode harga <select class="form-control" name="period">@foreach(['bulan','semester','tahun'] as $period)<option value="{{ $period }}" @selected(old('period')===$period)>Per {{ $period }}</option>@endforeach</select></label>
<p>Pilih paket yang akan ditawarkan. Isi harga masing-masing secara manual atau kosongkan untuk dibahas bersama sekolah.</p>
@foreach($packages as $key=>[$name,$description])
<div class="border-top py-2"><label><input type="checkbox" name="packages[]" value="{{ $key }}" @checked(in_array($key,old('packages',array_keys($packages))))> {{ $name }}</label>
<label class="ml-3">Harga (Rp) <input class="form-control" type="number" min="0" max="9999999999999" name="prices[{{ $key }}]" value="{{ old('prices.'.$key) }}"></label><p>{{ $description }}</p></div>
@endforeach
<fieldset><legend class="h6">Modul untuk paket CBT + Modul Pilihan</legend>@foreach($modules as $key=>$label)<label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key,old('modules',[])))> {{ $label }}</label>@endforeach</fieldset>
<label>Nama penandatangan sekolah <input class="form-control" name="signer" value="{{ old('signer') }}"></label>
<label>Jabatan <input class="form-control" required name="title" value="{{ old('title','Kepala Sekolah') }}"></label>
<button class="btn btn-primary">Unduh proposal PDF</button></form></div></div>
@endsection
