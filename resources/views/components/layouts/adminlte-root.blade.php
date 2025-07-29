{{-- File: resources/views/layouts/adminlte-root.blade.php --}}
@include('partials.datatables')

@extends('adminlte::page')

@section('title', $title ?? 'Base UI App')

@section('content_header')
    @isset($contentHeader)
        <h1>{{ $contentHeader }}</h1>
    @endisset
@endsection

@section('content')
    {!! $slot !!}
@endsection

@section('css')
    {{-- Custom CSS stack --}}
    @stack('css')
    @stack('custom-css')
@endsection

@section('js')
    {{-- Custom JS stack --}}
    @stack('js')
    @stack('custom-js')
@endsection