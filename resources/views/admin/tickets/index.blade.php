@extends('layouts.admin')

@section('page-title', 'Support Tickets')

@section('admin-content')

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0">

        <h4 class="mb-0 font-weight-bold">

            Ticket Support Client

        </h4>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Client</th>
                        <th>Subject</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($tickets as $ticket)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                {{ $ticket->client->name ?? '-' }}

                            </td>

                            <td>

                                <strong>

                                    {{ $ticket->subject }}

                                </strong>

                            </td>

                            <td>

                                {{ $ticket->project?->title ?? '-' }}

                            </td>

                            <td>

                                @if($ticket->status == 'open')

                                    <span class="badge bg-danger">

                                        Open

                                    </span>

                                @elseif($ticket->status == 'progress')

                                    <span class="badge bg-warning">

                                        Progress

                                    </span>

                                @else

                                    <span class="badge bg-success">

                                        Closed

                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ $ticket->created_at->format('d M Y') }}

                            </td>

                            <td>

                                <a href="{{ route('admin.tickets.show', $ticket->id) }}"
                                   class="btn btn-primary btn-sm">

                                    Detail

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center py-5">

                                Belum ada ticket 😄🔥

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
