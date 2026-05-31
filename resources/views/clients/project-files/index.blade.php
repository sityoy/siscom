@extends('layouts.client')

@section('page-title', 'File Project')

@section('client-content')

<div class="row">

    @forelse($files as $file)

        <div class="col-lg-4 mb-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    {{-- FILE ICON --}}
                    <div class="mb-3 text-center">

                        <div style="
                            width:70px;
                            height:70px;
                            line-height:70px;
                            border-radius:20px;
                            background:#eff6ff;
                            margin:auto;
                            font-size:30px;
                        ">

                            📁

                        </div>

                    </div>

                    {{-- FILE INFO --}}
                    <div class="text-center">

                        <h5 class="font-weight-bold">

                            {{ $file->title ?? 'File Project' }}

                        </h5>

                        <small class="text-muted">

                            {{ $file->created_at->format('d M Y') }}

                        </small>

                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="mt-3">

                        <p class="text-muted text-center">

                            {{ $file->description ?? 'Tidak ada deskripsi file.' }}

                        </p>

                    </div>

                    {{-- ACTION --}}
                    <div class="mt-4 text-center">

                        <a href="{{ asset('storage/' . $file->file) }}"
                           target="_blank"
                           class="btn btn-primary btn-sm">

                            Download File

                        </a>

                    </div>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <h4 class="mb-3">

                        Belum Ada File 😄🔥

                    </h4>

                    <p class="text-muted mb-0">

                        File project client akan muncul di sini.

                    </p>

                </div>

            </div>

        </div>

    @endforelse

</div>

@endsection
