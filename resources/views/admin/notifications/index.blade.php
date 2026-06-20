@extends('layouts.admin')

@section('page-title', 'Notifications')

@section('admin-content')

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0">

        <div class="d-flex justify-content-between">

            <h4 class="mb-0 font-weight-bold">

                Notification Center

            </h4>

            <span class="badge badge-danger">

                {{ $unreadCount }}

            </span>

        </div>

    </div>

    <div class="card-body">

        @forelse($notifications as $notification)

        <div class="card shadow-sm mb-3

            @if(!$notification->is_read)

            border-left border-primary

            @endif

            ">

            <div class="card-body">

                <div
                    class="d-flex justify-content-between align-items-center">

                    <div>


                        <strong>

                            {{ $notification->title }}

                        </strong>

                        @if(str_contains($notification->title, 'Ticket'))

                            <span class="badge badge-warning ml-2">

                                🎫 Ticket

                            </span>

                        @elseif(str_contains($notification->title, 'Pembayaran'))

                            <span class="badge badge-success ml-2">

                                💰 Payment

                            </span>

                        @elseif(str_contains($notification->title, 'Invoice'))

                            <span class="badge badge-primary ml-2">

                                📄 Invoice

                            </span>

                        @endif


                    </div>

                    <small class="text-muted">

                        @if(
                            $notification->created_at->isToday()
                        )

                            {{ $message->created_at->format('d M Y H:i') }}

                        @else

                            {{ $notification->created_at->format('d/m/Y H:i') }}

                        @endif

                        <form
                            action="{{ route('admin.notifications.destroy',$notification) }}"
                            method="POST">

                            @csrf
                            @method('DELETE')

                            <button
                                class="btn btn-sm btn-danger">

                                Hapus

                            </button>

                        </form>

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



        <div class="d-flex justify-content-between">

            <a href="{{ route('admin.dashboard') }}"
            class="btn btn-secondary">

                <i class="fas fa-arrow-left"></i>
                Kembali

            </a>

        </div>

        <div class="mt-4">

            {{ $notifications->links() }}

        </div>

    </div>

</div>

@endsection
