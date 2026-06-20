<x-guest-layout>

<div class="min-h-screen flex items-center justify-center px-6">

    <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-10 items-center">

        {{-- LEFT --}}
        <div class="hidden lg:block text-white">

            <h1 class="text-6xl font-bold mb-6">

                SIS.COM

            </h1>

            <h2 class="text-3xl font-semibold mb-6">

                Software House Management System

            </h2>

            <p class="text-lg text-gray-200 leading-relaxed">

                Kelola client, project,
                invoice, payment,
                support ticket,
                dan workflow software house
                dalam satu sistem modern.

            </p>

            <div class="mt-10 space-y-4 text-lg">

                <div>✅ Client Portal</div>

                <div>✅ Invoice Management</div>

                <div>✅ Support Ticket CRM</div>

                <div>✅ Project Workflow</div>

            </div>

        </div>

        {{-- RIGHT --}}
        <div>

            <div class="bg-white rounded-3xl shadow-2xl p-10">

                <div class="text-center mb-8">

                    <h2 class="text-4xl font-bold text-gray-800">

                        Welcome Back 👋

                    </h2>

                    <p class="text-gray-500 mt-3">

                        Login ke dashboard SIS.COM

                    </p>

                </div>

                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')" />

                <form method="POST"
                      action="{{ route('login') }}">

                    @csrf

                    {{-- EMAIL --}}
                    <div class="mb-5">

                        <label class="block mb-2
                                     text-sm
                                     font-semibold
                                     text-gray-700">

                            Email Address

                        </label>

                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="
                                    w-full
                                    rounded-2xl
                                    border-gray-300
                                    shadow-sm
                                    focus:ring-blue-500
                                    focus:border-blue-500
                                    px-5
                                    py-4
                               "
                               placeholder="Masukkan email"
                               required>

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="mt-2 text-red-500" />

                    </div>

                    {{-- PASSWORD --}}
                    <div class="mb-5">

                        <label class="block mb-2
                                     text-sm
                                     font-semibold
                                     text-gray-700">

                            Password

                        </label>

                        <input type="password"
                               name="password"
                               class="
                                    w-full
                                    rounded-2xl
                                    border-gray-300
                                    shadow-sm
                                    focus:ring-blue-500
                                    focus:border-blue-500
                                    px-5
                                    py-4
                               "
                               placeholder="Masukkan password"
                               required>

                        <x-input-error
                            :messages="$errors->get('password')"
                            class="mt-2 text-red-500" />

                    </div>

                    {{-- REMEMBER --}}
                    <div class="
                        flex
                        items-center
                        justify-between
                        mb-6
                    ">

                        <label class="flex items-center">

                            <input type="checkbox"
                                   name="remember"
                                   class="rounded border-gray-300">

                            <span class="ml-2 text-sm text-gray-600">

                                Remember me

                            </span>

                        </label>

                        @if (Route::has('password.request'))

                            <a href="{{ route('password.request') }}"
                               class="
                                    text-sm
                                    text-blue-600
                                    hover:underline
                               ">

                                Forgot password?

                            </a>

                        @endif

                    </div>

                    {{-- BUTTON --}}
                    <button type="submit"
                            class="
                                w-full
                                bg-blue-700
                                hover:bg-blue-800
                                text-white
                                font-semibold
                                py-4
                                rounded-2xl
                                transition
                            ">

                        Login Dashboard

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</x-guest-layout>
