@extends('layouts.admin')

@section('page-title', 'Edit Project')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Edit Project

        </h4>

        <small class="text-muted">

            Perbarui data project client SIS.COM

        </small>

    </div>

    <form action="{{ route('projects.update', $project->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                {{-- CLIENT --}}
                <div class="col-md-6 mb-3">

                    <label>Client</label>

                    <select name="client_id"
                            class="form-control"
                            required>

                        @foreach($clients as $client)

                            <option value="{{ $client->id }}"
                                @selected($project->client_id == $client->id)>

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

                        <option value="pending"
                            @selected($project->status == 'pending')>

                            Pending

                        </option>

                        <option value="progress"
                            @selected($project->status == 'progress')>

                            Progress

                        </option>

                        <option value="completed"
                            @selected($project->status == 'completed')>

                            Completed

                        </option>

                        <option value="cancelled"
                            @selected($project->status == 'cancelled')>

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
                       value="{{ old('title', $project->title) }}"
                       required>

            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-3">

                <label>Description</label>

                <textarea name="description"
                          class="form-control"
                          rows="4">{{ old('description', $project->description) }}</textarea>

            </div>

            <div class="row">

                {{-- BUDGET --}}
                <div class="col-md-6 mb-3">

                    <label>Budget</label>

                    <input type="number"
                           name="budget"
                           class="form-control"
                           value="{{ old('budget', $project->budget) }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label>Progress (%)</label>

                    <input type="number"
                        name="progress"
                        class="form-control"
                        value="{{ $project->progress ?? 0 }}"
                        min="0"
                        max="100">

                </div>

                {{-- DEADLINE --}}
                <div class="col-md-6 mb-3">

                    <label>Deadline</label>

                    <input type="date"
                           name="deadline"
                           class="form-control"
                           value="{{ old('deadline', $project->deadline) }}">

                </div>

            </div>

            <hr>

            <h5 class="font-weight-bold">Denda Keterlambatan</h5>

            <div class="form-check mb-3">

                <input type="hidden"
                       name="late_fee_active"
                       value="0">

                <input type="checkbox"
                       name="late_fee_active"
                       id="late_fee_active"
                       class="form-check-input"
                       value="1"
                       @checked(old('late_fee_active', $project->late_fee_active))>

                <label class="form-check-label"
                       for="late_fee_active">

                    Aktifkan denda keterlambatan pembayaran

                </label>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Denda per 30 Hari</label>

                    <input type="number"
                           name="late_fee_per_month"
                           class="form-control @error('late_fee_per_month') is-invalid @enderror"
                           value="{{ old('late_fee_per_month', $project->late_fee_per_month ?? 100000) }}"
                           min="0"
                           step="1000">

                    @error('late_fee_per_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <div class="col-md-6 mb-3 d-flex align-items-end">

                    <small class="text-muted">
                        Denda dihitung setiap 30 hari setelah jatuh tempo invoice
                        dan dibulatkan ke atas.
                    </small>

                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('projects.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Update Project

            </button>

        </div>

    </form>

</div>

@endsection
