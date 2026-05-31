@extends('layouts.admin')

@section('page-title', 'Tambah User')

@section('admin-content')

<div class="card">

    <form action="{{ route('users.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            <div class="mb-3">

                <label>Nama</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label>Password</label>

                <input type="password"
                       name="password"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label>Role</label>

                <select name="role"
                        class="form-control"
                        required>

                    <option value="">
                        -- Pilih Role --
                    </option>

                    @foreach($roles as $role)

                        <option value="{{ $role->name }}">

                            {{ $role->name }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-primary">

                Simpan User

            </button>

        </div>

    </form>

</div>

@endsection
