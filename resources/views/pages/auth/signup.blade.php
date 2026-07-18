@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col lg:w-1/2">
                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div class="mb-5 sm:mb-8">
                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                            Sign Up
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Enter your details to create an account!
                        </p>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="space-y-5">
                                <!-- Full Name -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Full Name<span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                        placeholder="Enter your full name" autofocus
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('name') border-error-500 @enderror" />
                                    @error('name')
                                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Email -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Email<span class="text-error-500">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-error-500 @enderror" />
                                    @error('email')
                                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Password<span class="text-error-500">*</span>
                                    </label>
                                    <div x-data="{ showPassword: false }" class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Enter your password"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('password') border-error-500 @enderror" />
                                        <span @click="showPassword = !showPassword"
                                            class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                            <x-heroicon-o-eye x-show="!showPassword" class="w-5 h-5" aria-hidden="true" />
                                            <x-heroicon-o-eye-slash x-show="showPassword" class="w-5 h-5" aria-hidden="true" />
                                        </span>
                                    </div>
                                    @error('password')
                                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Confirm Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Confirm Password<span class="text-error-500">*</span>
                                    </label>
                                    <div x-data="{ showPassword: false }" class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" placeholder="Confirm your password"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                        <span @click="showPassword = !showPassword"
                                            class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                            <x-heroicon-o-eye x-show="!showPassword" class="w-5 h-5" aria-hidden="true" />
                                            <x-heroicon-o-eye-slash x-show="showPassword" class="w-5 h-5" aria-hidden="true" />
                                        </span>
                                    </div>
                                </div>
                                <!-- Checkbox -->
                                <div>
                                    <div x-data="{ checkboxToggle: false }">
                                        <label for="terms"
                                            class="flex cursor-pointer items-start text-sm font-normal text-gray-700 select-none dark:text-gray-400">
                                            <div class="relative">
                                                <input type="checkbox" id="terms" name="terms" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                                                <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                                                    'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                    <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="inline-block font-normal text-gray-500 dark:text-gray-400">
                                                By creating an account means you agree to the
                                                <span class="text-gray-800 dark:text-white/90">
                                                    Terms and Conditions,
                                                </span>
                                                and our
                                                <span class="text-gray-800 dark:text-white">
                                                    Privacy Policy
                                                </span>
                                            </p>
                                        </label>
                                    </div>
                                </div>
                                <!-- Button -->
                                <div>
                                    <button type="submit"
                                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                        Sign Up
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="mt-5">
                            <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-brand-950 relative hidden h-full w-full items-center lg:grid lg:w-1/2 dark:bg-white/5">
                <div class="z-1 flex items-center justify-center">
                    <!-- ===== Common Grid Shape Start ===== -->
                    <x-common.common-grid-shape />
                    <div class="flex max-w-xs flex-col items-center">
                        <a href="{{ route('dashboard') }}" class="mb-4 block">
                            <img src="{{ asset('images/logo/auth-logo.svg') }}" alt="Logo" />
                        </a>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            Free and Open-Source Tailwind CSS Admin Dashboard Template
                        </p>
                    </div>
                </div>
            </div>
            <!-- Toggler -->
            <div class="fixed right-6 bottom-6 z-50">
                <button
                    class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors"
                    @click.prevent="$store.theme.toggle()">
                    <x-heroicon-o-moon class="hidden w-5 h-5 dark:block" aria-hidden="true" />
                    <x-heroicon-o-sun class="w-5 h-5 dark:hidden" aria-hidden="true" />
                </button>
            </div>
        </div>
    </div>
@endsection
