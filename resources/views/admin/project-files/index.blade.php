@extends('layouts.admin')

@section('page-title', 'Project Files')

@section('admin-content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-0 font-weight-bold">

                {{ $project->title }}

            </h4>

            <small class="text-muted">

                File management project SIS.COM

            </small>

        </div>

        <a href="{{ route('projects.index') }}"
           class="btn btn-secondary">

            Kembali

        </a>

    </div>

    <div class="card-body">

        @if(session('success'))

            <div class="alert alert-success">

                {{ session('success') }}

            </div>

        @endif

        {{-- UPLOAD --}}
        <form action="{{ route('project.files.store', $project->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="row">

                <div class="col-md-10 mb-3">

                    <input type="file"
                           name="file"
                           class="form-control"
                           required>

                </div>

                <div class="col-md-2 mb-3">

                    <button class="btn btn-primary w-100">

                        Upload

                    </button>

                </div>

            </div>

        </form>

        <hr>

        {{-- FILE TABLE --}}
        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Nama File</th>

                        <th>Tipe</th>

                        <th>Ukuran</th>

                        <th>Tanggal Upload</th>

                        <th class="text-center">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($files as $file)

                        @php

                            $extension =
                                pathinfo(
                                    $file->file_name,
                                    PATHINFO_EXTENSION
                                );

                            $filePath =
                                storage_path(
                                    'app/public/' .
                                    $file->file_path
                                );

                            $size =
                                file_exists($filePath)
                                ? round(filesize($filePath) / 1024, 2)
                                : 0;

                        @endphp

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <div class="font-weight-bold">

                                    {{ $file->file_name }}

                                </div>

                            </td>

                            <td>

                                <span class="badge bg-info">

                                    {{ strtoupper($extension) }}

                                </span>

                            </td>

                            <td>

                                {{ $size }} KB

                            </td>

                            <td>

                                {{ $file->created_at->format('d M Y H:i') }}

                            </td>

                            <td class="text-center">

                                <a href="{{ asset('storage/' . $file->file_path) }}"
                                   target="_blank"
                                   class="btn btn-success btn-sm">

                                    Download

                                </a>

                                <form action="{{ route('project.files.delete', $file->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus file ini?')">

                                        Hapus

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-5">

                                Belum ada file project 😄🔥

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
