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
    @stack('custom-css')
@endsection

@section('js')
    @stack('custom-js')
@endsection