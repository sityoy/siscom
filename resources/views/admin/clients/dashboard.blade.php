@extends('layouts.admin')

@section('page-title', 'Client Dashboard')

@section('admin-content')

<div class="row">

    <div class="col-lg-3 col-6">

        <div class="small-box bg-info">

            <div class="inner">

                <h3>{{ $projects }}</h3>

                <p>Total Project</p>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-6">

        <div class="small-box bg-success">

            <div class="inner">

                <h3>{{ $invoices }}</h3>

                <p>Total Invoice</p>

            </div>

        </div>

    </div>

</div>

@endsection
