@extends('layouts.app')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-3xl">

    <h2 class="text-xl font-semibold mb-4">Edit Kode Rekomendasi</h2>

    <form action="{{ route('kode-rekomendasi.update', $data->id) }}" method="POST">
        @method('PUT')
        @include('pages.kode-rekomendasi.create')
    </form>

</div>

@endsection