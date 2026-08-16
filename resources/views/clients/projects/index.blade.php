@extends('layouts.client')

@section('page-title', 'Project Saya')

@section('client-content')

<div class="row">

    @forelse($projects as $project)

        <div class="col-lg-6 mb-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    {{-- HEADER --}}
                    <div class="d-flex
                                justify-content-between
                                align-items-start">

                        <div>

                            <h4 class="font-weight-bold mb-1">

                                {{ $project->title }}

                            </h4>

                            <small class="text-muted">

                                Dibuat:
                                {{ $project->created_at->format('d M Y') }}

                            </small>

                        </div>

                        <div>

                            @if($project->status == 'completed')

                                <span class="badge bg-success">

                                    Completed

                                </span>

                            @elseif($project->status == 'pending')

                                <span class="badge bg-warning">

                                    Pending

                                </span>

                            @else

                                <span class="badge bg-primary">

                                    Progress

                                </span>

                            @endif

                        </div>

                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="mt-3">

                        <p class="text-muted mb-0">

                            {{ $project->description ?? 'Tidak ada deskripsi project.' }}

                        </p>

                    </div>

                    {{-- PROGRESS --}}
                    <div class="mt-4">

                        <div class="d-flex
                                    justify-content-between
                                    mb-2">

                            <strong>

                                Progress

                            </strong>

                            <strong class="text-primary">

                                {{ $project->progress ?? 0 }}%

                            </strong>

                        </div>

                        <div class="progress"
                             style="height:10px;
                                    border-radius:20px;">

                            <div class="progress-bar bg-primary"
                                 role="progressbar"
                                 style="
                                    width:
                                    {{ $project->progress ?? 0 }}%;
                                 ">

                            </div>

                        </div>

                    </div>

                    {{-- DEADLINE --}}
                    <div class="mt-4">

                        <small class="text-muted d-block">

                            Deadline

                        </small>

                        <strong>

                            {{ $project->deadline
                                ? \Carbon\Carbon::parse($project->deadline)->format('d M Y')
                                : '-' }}

                        </strong>

                    </div>

                    @if($project->late_fee_active)

                        <div class="mt-3">
                            <small class="text-danger">
                                Denda keterlambatan:
                                Rp {{ number_format($project->late_fee_per_month,0,',','.') }}
                                per 30 hari
                            </small>
                        </div>

                    @endif

                    {{-- FOOTER --}}
                    <div class="mt-4 d-flex
                                justify-content-between
                                align-items-center">

                        <small class="text-muted">

                            SIS.COM Project Management

                        </small>

                        <a href="{{ route('client.projects.show', $project->id) }}"
                           class="btn btn-primary btn-sm">

                            Detail Project

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

                        Belum Ada Project 😄🔥

                    </h4>

                    <p class="text-muted mb-0">

                        Project client akan muncul di sini.

                    </p>

                </div>

            </div>

        </div>

    @endforelse

</div>

@endsection
