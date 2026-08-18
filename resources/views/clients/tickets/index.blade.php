@extends('layouts.client')

@section('page-title', 'Support Tickets')

@section('client-content')

<div class="card border-0 shadow-sm">

    <div class="card-header
                bg-white
                d-flex
                justify-content-between
                align-items-center">

        <h4 class="mb-0 font-weight-bold">

            Support Ticket

        </h4>

        <a href="{{ route('client.tickets.create') }}"
           class="btn btn-primary">

            + Buat Ticket

        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>#</th>
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
                                <a href="{{ route(
                                    'client.tickets.show',
                                    $ticket->id
                                ) }}">

                                    <strong>
                                        {{ $ticket->subject }}
                                    </strong>

                                </a>
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

                                <a href="{{ route('client.tickets.show', $ticket->id) }}"
                                   class="btn btn-primary btn-sm">

                                    Detail

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
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
