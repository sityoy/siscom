@extends('layouts.client')

@section('page-title', 'Buat Support Ticket')

@section('client-content')

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0">

                <h4 class="mb-0 font-weight-bold">

                    Buat Ticket Baru

                </h4>

            </div>

            <form action="{{ route('client.tickets.store') }}"
                  method="POST">

                @csrf

                <div class="card-body">

                    @if ($errors->any())

                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    @endif

                    {{-- PROJECT --}}
                    <div class="mb-3">

                        <label>Project</label>

                        <select name="project_id"
                                class="form-control">

                            <option value="">

                                -- Pilih Project --

                            </option>

                            @foreach($projects as $project)

                                <option value="{{ $project->id }}">

                                    {{ $project->title }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- SUBJECT --}}
                    <div class="mb-3">

                        <label>Subject</label>

                        <input type="text"
                               name="subject"
                               class="form-control"
                               placeholder="Contoh: Revisi Landing Page">

                    </div>

                    {{-- MESSAGE --}}
                    <div class="mb-3">

                        <label>Pesan / Revisi</label>

                        <textarea name="message"
                                  rows="6"
                                  class="form-control"
                                  placeholder="Tulis kebutuhan revisi / bug / request fitur..."></textarea>

                    </div>

                </div>

                <div class="card-footer
                            bg-white
                            text-right">

                    <button class="btn btn-primary">

                        Kirim Ticket

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
