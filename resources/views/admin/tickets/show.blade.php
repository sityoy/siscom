@extends('layouts.admin')

@section('page-title', 'Detail Ticket')

@section('admin-content')

<div class="row">


{{-- LEFT CONTENT --}}
<div class="col-lg-8">

    {{-- DETAIL TICKET --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="mb-1 font-weight-bold">

                        {{ $ticket->subject }}

                    </h4>

                    <small class="text-muted">

                        Dibuat:
                        {{ $ticket->created_at->format('d M Y H:i') }}

                    </small>

                </div>

                <div>

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

                </div>

            </div>

        </div>

        <div class="card-body">

            <h6 class="font-weight-bold">

                Pesan Awal Client

            </h6>

            <div class="border rounded p-3 bg-light">

                {{ $ticket->message }}

            </div>

        </div>

    </div>

    {{-- CHAT --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0">

            <h5 class="mb-0 font-weight-bold">

                Percakapan Ticket

            </h5>

        </div>

        <div class="card-body"
             style="
                max-height:500px;
                overflow-y:auto;
                background:#f5f7fb;
             ">

            {{-- PESAN PERTAMA CLIENT --}}
            <div class="d-flex justify-content-start mb-4">

                <div style="max-width:75%;">

                    <div class="bg-white p-3 rounded-4 shadow-sm">

                        <div class="mb-2">

                            <strong class="text-primary">

                                {{ $ticket->client->name }}

                            </strong>

                            <br>

                            <small class="text-muted">

                                {{ $ticket->created_at->diffForHumans() }}

                            </small>

                        </div>

                        {{ $ticket->message }}

                    </div>

                </div>

            </div>

            {{-- REPLIES --}}
            @foreach($ticket->messages as $message)

                <div class="d-flex mb-4

                    {{ $message->sender_type == 'admin'
                        ? 'justify-content-end'
                        : 'justify-content-start'
                    }}

                ">

                    <div style="max-width:75%;">

                        <div class="

                            p-3
                            rounded-4
                            shadow-sm

                            {{ $message->sender_type == 'admin'
                                ? 'bg-primary text-white'
                                : 'bg-white'
                            }}

                        ">

                            <div class="mb-2">

                                <strong>

                                    {{ $message->sender_name }}

                                </strong>

                                <br>

                                <small class="

                                    {{ $message->sender_type == 'admin'
                                        ? 'text-white'
                                        : 'text-muted'
                                    }}

                                    opacity-75

                                ">

                                    {{ $message->created_at->diffForHumans() }}

                                </small>

                            </div>

                            {{ $message->message }}

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

    {{-- FORM REPLY --}}
    @if($ticket->status != 'closed')

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0 font-weight-bold">

                    Balas Ticket

                </h5>

            </div>

            <form
                action="{{ route('admin.tickets.reply', $ticket->id) }}"
                method="POST">

                @csrf

                <div class="card-body">

                    <textarea
                        name="message"
                        rows="5"
                        class="form-control rounded-4"
                        placeholder="Tulis balasan..."
                        required></textarea>

                </div>

                <div class="card-footer bg-white border-0 text-right">

                    <button
                        class="btn btn-primary rounded-pill px-4">

                        Kirim Balasan

                    </button>

                </div>

            </form>

        </div>

    @else

        <div class="alert alert-success">

            Ticket telah ditutup.
            Jika masih ada kendala,
            silakan buat ticket baru.

        </div>

    @endif

</div>

{{-- RIGHT SIDEBAR --}}
<div class="col-lg-4">

    {{-- CLIENT --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0">

            <h5 class="mb-0 font-weight-bold">

                Informasi Client

            </h5>

        </div>

        <div class="card-body">

            <div class="mb-3">

                <small class="text-muted d-block">

                    Nama

                </small>

                <strong>

                    {{ $ticket->client->name ?? '-' }}

                </strong>

            </div>

            <div class="mb-3">

                <small class="text-muted d-block">

                    Email

                </small>

                <strong>

                    {{ $ticket->client->email ?? '-' }}

                </strong>

            </div>

            <div>

                <small class="text-muted d-block">

                    Project

                </small>

                <strong>

                    {{ $ticket->project?->title ?? '-' }}

                </strong>

            </div>

        </div>

    </div>

    {{-- STATUS --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0">

            <h5 class="mb-0 font-weight-bold">

                Update Status

            </h5>

        </div>

        <form
            action="{{ route('admin.tickets.status', $ticket->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="card-body">

                <label>Status Ticket</label>

                <select
                    name="status"
                    class="form-control">

                    <option value="open"
                        @selected($ticket->status == 'open')>

                        Open

                    </option>

                    <option value="progress"
                        @selected($ticket->status == 'progress')>

                        Progress

                    </option>

                    <option value="closed"
                        @selected($ticket->status == 'closed')>

                        Closed

                    </option>

                </select>

            </div>

            <div class="card-footer bg-white border-0">

                <button
                    class="btn btn-primary btn-block">

                    Update Status

                </button>

            </div>

        </form>

    </div>

</div>


</div>

@endsection
