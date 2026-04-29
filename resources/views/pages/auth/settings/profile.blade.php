@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profile">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Profile</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    <x-layouts.settings title="Profile" description="Update your name and email address">
        @if (session('status'))
            <div class="mb-6">
                <x-ui.alert variant="success" :message="session('status')" />
            </div>
        @endif

        {{-- Tambahkan enctype="multipart/form-data" agar bisa upload file --}}
        <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                <div class="shrink-0">
                    @if($user->avatar)
                        <img class="h-16 w-16 object-cover rounded-full border dark:border-gray-700" 
                             src="{{ asset('storage/' . $user->avatar) }}" 
                             alt="Avatar">
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-xl font-bold text-gray-600 dark:text-gray-400">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile Picture</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300">
                </div>
            </div>

            <div>
                <x-forms.input
                    name="name"
                    label="Name"
                    type="text"
                    :value="$user->name"
                    required
                    autofocus
                />
            </div>

            <div>
                <x-forms.input
                    name="email"
                    label="Email"
                    type="email"
                    :value="$user->email"
                    required
                />
            </div>

            <div>
                <x-ui.button type="submit" variant="primary">
                    Save
                </x-ui.button>
            </div>
        </form>

        <div class="mt-8 border-t border-gray-200 pt-8 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete account</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Delete your account and all of its resources</p>

            <form method="POST" action="{{ route('settings.profile.destroy') }}" class="mt-4"
                  onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <x-ui.button
                    type="submit"
                    variant="primary"
                    className="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                >
                    Delete account
                </x-ui.button>
            </form>
        </div>
    </x-layouts.settings>
@endsection