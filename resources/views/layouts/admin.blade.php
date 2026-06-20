@extends('adminlte::page')

@section(
    'title',
    'SIS.COM | ' .
    ucfirst(auth()->user()->roles->first()->name ?? 'Admin')
)

@section('content_header')



<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1 class="m-0 font-weight-bold text-primary">

            @yield('page-title')

        </h1>

        <small class="text-muted">

            SIS.COM — Software House & IT Solutions

        </small>

        <a
            href="{{ route('admin.notifications') }}"
            class="btn btn-light position-relative">

            <i class="fas fa-bell"></i>

            @if($adminNotificationCount > 0)

                <span
                    class="badge badge-danger position-absolute"
                    style="
                        top:-5px;
                        right:-5px;
                    ">

                    {{ $adminNotificationCount }}

                </span>

            @endif

        </a>

    </div>

    <div class="text-right">

        <small class="text-muted d-block">

            {{ now()->format('d M Y') }}

        </small>

        <strong>

            {{ auth()->user()->name }}

        </strong>

        <br>

        <small class="text-muted">

            {{ ucfirst(
                auth()->user()
                ->roles
                ->first()
                ->name ?? 'Admin'
            ) }}

        </small>

    </div>

</div>

@stop

@section('content')

<div class="sis-wrapper">

    @yield('admin-content')

</div>

@stop

@section('css')

<style>

body{
    background:#f4f7fb !important;
    font-family:'Segoe UI',sans-serif;
}

.sis-wrapper{
    padding-bottom:20px;
}

.main-header.navbar{
    background:#fff !important;
    border-bottom:1px solid #e5e7eb;
}

.main-sidebar{
    background:linear-gradient(
        180deg,
        #0a3b96 0%,
        #082c70 100%
    ) !important;
}

.brand-link{
    background:transparent !important;
    border-bottom:1px solid rgba(255,255,255,.08);
}

.brand-text{
    color:#fff !important;
    font-size:14px;
    font-weight:700;
    letter-spacing:.5px;
}

.content-wrapper{
    background:#f4f7fb !important;
}

.content{
    padding-top:5px;
}

.nav-sidebar .nav-link{
    border-radius:10px;
    margin:6px 12px;
    padding:12px 16px;
    color:rgba(255,255,255,.85) !important;
    transition:.2s;
}

.nav-sidebar .nav-link:hover{
    background:rgba(255,255,255,.12);
    color:#fff !important;
}

.nav-sidebar .nav-link.active{
    background:#fff !important;
    color:#0a3b96 !important;
    font-weight:700;
}

.nav-sidebar .nav-icon{
    margin-right:6px;
}

.card{
    border:none !important;
    border-radius:16px !important;
    box-shadow:
    0 4px 20px rgba(15,23,42,.05) !important;
}

.card-header{
    background:#fff !important;
    border-bottom:1px solid #f1f5f9;
}

.btn{
    border-radius:10px !important;
}

.form-control{
    border-radius:10px !important;
}

.pagination .page-link{
    border:none;
    border-radius:8px !important;
}

@media(max-width:768px){

    .content-header h1{
        font-size:22px;
    }

    .nav-sidebar .nav-link{

        padding:14px 16px;

        font-size:15px;

    }

}

</style>

@stop
