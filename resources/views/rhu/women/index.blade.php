@extends('rhu.layout')

@section('title', 'All Women - RHU Portal | ReproCare')

@section('rhu-content')
    @include('includes.women-directory', [
        'womenRoute' => 'rhu.women.index',
        'addWomanRoute' => 'rhu.patients.create',
        'addWomanLabel' => 'Add Woman',
    ])
@endsection
