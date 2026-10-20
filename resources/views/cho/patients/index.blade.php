@extends('cho.layout')

@section('title', 'All Women - CHO Portal | ReproCare')

@section('cho-content')
    @include('includes.women-directory', [
        'womenRoute' => 'cho.patients.index',
        'subtitle' => 'City-wide directory of registered and unregistered women. Read-only oversight — verification and archiving stay with RHU.',
    ])
@endsection
