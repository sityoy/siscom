@extends('layouts.client')

@section('page-title', 'Profile Saya')

@section('client-content')

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0">

                <h4 class="mb-0 font-weight-bold">

                    Client Profile

                </h4>

            </div>

            <form action="{{ route('client.profile.update') }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">

                    @if(session('success'))

                        <div class="alert alert-success">

                            {{ session('success') }}

                        </div>

                    @endif

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>Nama</label>

                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ $client->name }}">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ $client->email }}">

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>Phone</label>

                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ $client->phone }}">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Company</label>

                            <input type="text"
                                   name="company"
                                   class="form-control"
                                   value="{{ $client->company ?? '' }}">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label>Address</label>

                        <textarea name="address"
                                  rows="4"
                                  class="form-control">{{ $client->address ?? '' }}</textarea>

                    </div>

                </div>

                <div class="card-footer bg-white text-right">

                    <button class="btn btn-primary">

                        Update Profile

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
