@extends('layouts.admin')

@section('page-title', 'Clients')

@section('admin-content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-0 font-weight-bold">

                Client Management

            </h4>

            <small class="text-muted">

                Kelola seluruh client SIS.COM

            </small>

        </div>

        <a href="{{ route('clients.create') }}"
           class="btn btn-primary">

            + Tambah Client

        </a>

    </div>

    <div class="card-body">

        @if(session('success'))

            <div class="alert alert-success">

                {{ session('success') }}

            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th width="5%">#</th>

                        <th>Client</th>

                        <th>Perusahaan</th>

                        <th>Paket</th>

                        <th>Berakhir</th>

                        <th></th>

                        <th>Status</th>

                        <th>Projects</th>

                        <th>Invoices</th>

                        <th>Created</th>

                        <th>Email</th>

                        <th>WhatsApp</th>
                        <th class="text-center">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($clients as $client)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <div class="d-flex align-items-center">

                                    <div style="
                                        width:45px;
                                        height:45px;
                                        border-radius:50%;
                                        background:#0a3b96;
                                        color:white;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        font-weight:bold;
                                        margin-right:12px;
                                    ">

                                        {{ strtoupper(substr($client->name,0,1)) }}

                                    </div>

                                    <div>

                                        <div class="font-weight-bold">

                                            {{ $client->name }}

                                        </div>

                                        <small class="text-muted">

                                            Client SIS.COM

                                        </small>

                                    </div>

                                </div>

                            </td>

                            <td>

                                {{ $client->company ?? '-' }}

                            </td>
                            <td>
                                {{ $client->package_name ?? '-' }}
                            </td>
                            <td>
                                {{ $client->subscription_end
                                    ? \Carbon\Carbon::parse($client->subscription_end)->format('d M Y')
                                    : '-' }}
                            </td>

                            <td>
                                @if($client->subscription_end)

                                    @php
                                        $daysLeft = floor(
                                            now()->diffInDays(
                                                $client->subscription_end,
                                                false
                                            )
                                        );
                                    @endphp

                                    @if($daysLeft > 30)

                                        <span class="badge bg-success">
                                            {{ $daysLeft }} Hari
                                        </span>

                                    @elseif($daysLeft >= 0)

                                        <span class="badge bg-warning text-dark">
                                            {{ $daysLeft }} Hari
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Terlambat {{ abs($daysLeft) }} Hari
                                        </span>

                                    @endif

                                @else

                                    -

                                @endif
                            </td>


                            <td>

                                @if($client->subscription_status == 'active')

                                    <span class="badge bg-success">
                                        Aktif
                                    </span>

                                @elseif($client->subscription_status == 'grace')

                                    <span class="badge bg-warning text-dark">
                                        Masa Tenggang
                                    </span>

                                @else

                                    <span class="badge bg-danger">
                                        Expired
                                    </span>

                                @endif

                            </td>

                            <td>{{ $client->projects_count }}</td>

                            <td>{{ $client->invoices_count }}</td>

                            <td>{{ $client->created_at->format('d M Y') }}</td>

                            <td>

                                {{ $client->email ?? '-' }}

                            </td>

                            <td>

                                @if($client->phone)

                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}"
                                       target="_blank"
                                       class="btn btn-success btn-sm">

                                        WhatsApp

                                    </a>

                                @else

                                    -

                                @endif

                            </td>

                            <td class="text-center">

                                <a href="{{ route('clients.edit', $client->id) }}"
                                   class="btn btn-warning btn-sm">

                                    Edit

                                </a>

                                <form action="{{ route('clients.destroy', $client->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus client?')">

                                        Hapus

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="13"
                                class="text-center text-muted py-4">

                                Belum ada data client

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">

            {{ $clients->links() }}

        </div>

    </div>

</div>

@endsection
