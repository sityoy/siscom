@extends('layouts.admin')

@section('page-title', 'Tambah Project')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Tambah Project Baru

        </h4>

        <small class="text-muted">

            Buat project baru untuk client SIS.COM

        </small>

    </div>

    <form action="{{ route('projects.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            <div class="row">

                {{-- CLIENT --}}
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

                {{-- STATUS --}}
                <div class="col-md-6 mb-3">

                    <label>Status</label>

                    <select name="status"
                            class="form-control">

                        <option value="pending">
                            Pending
                        </option>

                        <option value="progress">
                            Progress
                        </option>

                        <option value="completed">
                            Completed
                        </option>

                        <option value="cancelled">
                            Cancelled
                        </option>

                    </select>

                </div>

            </div>

            {{-- TITLE --}}
            <div class="mb-3">

                <label>Nama Project</label>

                <input type="text"
                       name="title"
                       class="form-control"
                       placeholder="Masukkan nama project"
                       required>

            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-3">

                <label>Description</label>

                <textarea name="description"
                          class="form-control"
                          rows="4"
                          placeholder="Deskripsi project"></textarea>

            </div>

            <div class="row">

                {{-- BUDGET --}}
                <div class="col-md-6 mb-3">

                    <label>Budget</label>

                    <input type="number"
                           name="budget"
                           class="form-control"
                           placeholder="0">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Progress (%)</label>

                    <input type="number"
                        name="progress"
                        class="form-control"
                        value="0"
                        min="0"
                        max="100">

                </div>

                {{-- DEADLINE --}}
                <div class="col-md-6 mb-3">

                    <label>Deadline</label>

                    <input type="date"
                           name="deadline"
                           class="form-control">

                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('projects.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Simpan Project

            </button>

        </div>

    </form>

</div>

@endsection
