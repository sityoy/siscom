@extends('layouts.admin')

@section('page-title', 'Projects')

@section('admin-content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-0 font-weight-bold">

                Project Management

            </h4>

            <small class="text-muted">

                Kelola seluruh project client SIS.COM

            </small>

        </div>

        <a href="{{ route('projects.create') }}"
           class="btn btn-primary">

            + Creat Project

        </a>

    </div>

    <div class="card-body">

@if(session('success'))

            <div class="alert alert-success">

                {{ session('success') }}

            </div>

@endif

        @if(session('error'))

            <div class="alert alert-danger">

                {{ session('error') }}

            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Client</th>

                        <th>Project</th>

                        <th>Budget</th>

                        <th>Denda/Bulan</th>

                        <th>Deadline</th>

                        <th>Status</th>

                        <th>Progress</th>

                        <th class="text-center">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($projects as $project)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <div class="font-weight-bold">

                                    {{ $project->client->name }}

                                </div>

                                <small class="text-muted">

                                    Client SIS.COM

                                </small>

                            </td>

                            <td>

                                <div class="font-weight-bold">

                                    {{ $project->title }}

                                </div>

                                <small class="text-muted">

                                    {{ \Illuminate\Support\Str::limit($project->description, 40) }}

                                </small>

                            </td>

                            <td>

                                <span class="font-weight-bold text-success">

                                    Rp {{ number_format($project->budget,0,',','.') }}

                                </span>

                            </td>

                            <td>

                                @if($project->late_fee_active && $project->late_fee_per_month)

                                    <span class="font-weight-bold text-primary">

                                        Rp {{ number_format($project->late_fee_per_month,0,',','.') }}

                                    </span>

                                    <small class="d-block text-muted">
                                        Setiap 30 hari terlambat
                                    </small>

                                @else

                                    <span class="badge bg-secondary">

                                        Nonaktif

                                    </span>

                                @endif

                            </td>

                            <td>

                                @if($project->deadline)

                                    @php
                                        $deadline =
                                            \Carbon\Carbon::parse(
                                                $project->deadline
                                            );
                                    @endphp

                                    <span class="
                                        @if($deadline->isPast())
                                            text-danger
                                        @else
                                            text-primary
                                        @endif
                                    ">
                                        {{ $deadline->format('d M Y') }}
                                    </span>

                                @else

                                    <span class="text-muted">

                                        No Deadline

                                    </span>

                                @endif

                            </td>

                            <td>

                                @if($project->status == 'pending')

                                    <span class="badge bg-warning">

                                        Pending

                                    </span>

                                @elseif($project->status == 'progress')

                                    <span class="badge bg-info">

                                        Progress

                                    </span>

                                @elseif($project->status == 'completed')

                                    <span class="badge bg-success">

                                        Completed

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Cancelled

                                    </span>

                                @endif

                            </td>

                            <td>

                            <div class="progress">

                                <div class="progress-bar
                                    @if($project->progress >= 100)
                                        bg-success
                                    @elseif($project->progress >= 50)
                                        bg-info
                                    @else
                                        bg-warning
                                    @endif"
                                    style="width: {{ $project->progress }}%">

                                    {{ $project->progress }}%

                                </div>

                            </div>

                            </td>

                            <td class="text-center">

                                <a href="{{ route('invoices.create', [
                                    'project_id' => $project->id
                                ]) }}"
                                   class="btn btn-success btn-sm">

                                    Buat Invoice

                                </a>

                                <a href="{{ route('project.files', $project->id) }}"
                                   class="btn btn-info btn-sm">

                                    Files {{ $project->files()->count() }}

                                </a>







                                <a href="{{ route('projects.edit', $project->id) }}"
                                   class="btn btn-warning btn-sm">

                                    Edit

                                </a>

                                <form action="{{ route('projects.destroy', $project->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus project?')">

                                        Hapus

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center text-muted py-4">

                                Belum ada data project

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">

            {{ $projects->links() }}

        </div>

    </div>

</div>

@endsection
