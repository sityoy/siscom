@extends('layouts.client')

@section('page-title', 'Detail Ticket')

@section('client-content')

<div class="row">


<div class="col-lg-8">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h4 class="mb-1">
                {{ $ticket->subject }}
            </h4>

            <small class="text-muted">
                Dibuat:
                {{ $ticket->created_at->format('d M Y H:i') }}
            </small>

        </div>

        <div class="card-body"
             style="max-height:600px;overflow-y:auto;background:#f8fafc;">

            {{-- PESAN AWAL CLIENT --}}
            <div class="d-flex justify-content-end mb-4">

                <div class="bg-primary text-white p-3 rounded shadow-sm"
                     style="max-width:75%;">

                    <div class="mb-2">

                        <strong>
                            {{ $ticket->client->name }}
                        </strong>

                        <br>

                        <small class="text-white opacity-75">
                            {{ $ticket->created_at->diffForHumans() }}
                        </small>

                    </div>

                    <hr class="bg-white">

                    {{ $ticket->message }}

                </div>

            </div>

            {{-- REPLIES --}}
            @foreach($ticket->messages as $message)

                <div class="d-flex mb-4
                    {{ $message->sender_type == 'admin'
                        ? 'justify-content-start'
                        : 'justify-content-end'
                    }}">

                    <div style="max-width:75%;">

                        <div class="
                            p-3
                            rounded
                            shadow-sm
                            {{ $message->sender_type == 'admin'
                                ? 'bg-white'
                                : 'bg-primary text-white'
                            }}
                        ">

                            <div class="mb-2">

                                <strong>
                                    {{ $message->sender_name }}
                                </strong>

                                <br>

                                <small class="
                                    {{ $message->sender_type == 'admin'
                                        ? 'text-muted'
                                        : 'text-white'
                                    }}
                                    opacity-75
                                ">
                                    {{ $message->created_at->diffForHumans() }}
                                </small>

                            </div>

                            <hr>

                            {{ $message->message }}

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

    {{-- FORM REPLY --}}
    @if($ticket->status != 'closed')

        <div class="card border-0 shadow-sm mt-4">

            <div class="card-header bg-white">

                Balas Ticket

            </div>

            <form action="{{ route('client.tickets.reply', $ticket->id) }}"
                  method="POST">

                @csrf

                <div class="card-body">

                    <textarea
                        name="message"
                        rows="4"
                        class="form-control"
                        placeholder="Tulis balasan..."
                        required></textarea>

                </div>

                <div class="card-footer bg-white">

                    <button class="btn btn-primary">

                        Kirim Balasan

                    </button>

                </div>

            </form>

        </div>

    @else

        <div class="alert alert-success mt-4">

            Ticket telah ditutup.
            Jika masih ada kendala,
            silakan buat ticket baru.

        </div>

    @endif

</div>

{{-- SIDEBAR --}}
<div class="col-lg-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            Informasi Ticket

        </div>

        <div class="card-body">

            <p>
                <strong>Status:</strong>
                {{ strtoupper($ticket->status) }}
            </p>

            <p>
                <strong>Project:</strong>
                {{ $ticket->project?->title ?? '-' }}
            </p>

            <p>
                <strong>Dibuat:</strong>
                {{ $ticket->created_at->format('d M Y') }}
            </p>

        </div>

    </div>

</div>


</div>

@endsection
