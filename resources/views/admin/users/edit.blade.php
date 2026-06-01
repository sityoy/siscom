@extends('layouts.admin')

@section('page-title', 'Edit User')

@section('admin-content')

<div class="card">

    <form action="{{ route('users.update', $user->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="mb-3">

                <label>Nama</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $user->name) }}"
                       required>

            </div>

            <div class="mb-3">

                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $user->email) }}"
                       required>

            </div>

            <div class="mb-3">

                <label>Password</label>

               <input   type="password"
                        name="password"
                        class="form-control"
                        autocomplete="new-password">

                <small class="text-muted">

                    Kosongkan jika tidak ingin mengubah password

                </small>

            </div>

            <div class="mb-3">

                <label>Role</label>

                <select name="role"
                        class="form-control"
                        required>

                    @foreach($roles as $role)

                        <option value="{{ $role->name }}"

                            @selected(
                                $user->roles->pluck('name')->first()
                                == $role->name
                            )>

                            {{ $role->name }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-primary">

                Update User

            </button>

        </div>

    </form>

</div>

@endsection
