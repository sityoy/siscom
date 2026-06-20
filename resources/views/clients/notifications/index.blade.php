@extends('layouts.client')

@section('page-title', 'Notifications')

@section('client-content')

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0">

    <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0 font-weight-bold">

                Notification Center

            </h4>

            <span class="badge badge-primary">

                {{ $notifications->total() }}

            </span>

        </div>

    </div>

    <div class="card-body">

        @forelse($notifications as $notification)

        <div class="card border-0 shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <strong>

                            {{ $notification->title }}

                        </strong>

                        @if(str_contains($notification->title, 'Invoice'))

                            <span class="badge badge-primary ml-2">

                                Invoice

                            </span>

                        @elseif(str_contains($notification->title, 'Pembayaran'))

                            <span class="badge badge-success ml-2">

                                Payment

                            </span>

                        @elseif(str_contains($notification->title, 'Ticket'))

                            <span class="badge badge-warning ml-2">

                                Ticket

                            </span>

                        @endif

                    </div>

                    <small class="text-muted">

                        {{ $notification->created_at->diffForHumans() }}

                    </small>

                </div>

                <p class="mb-0 mt-3 text-muted">

                    {{ $notification->message }}

                </p>

            </div>

        </div>

        @empty

        <div class="text-center py-5">

            <h5>

                Belum ada notifikasi 😄🔥

            </h5>

        </div>

        @endforelse

        <div class="mt-4">

            {{ $notifications->links() }}

        </div>

</div>

@endsection
