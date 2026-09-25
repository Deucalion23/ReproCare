@extends('layouts.app')

@section('title', 'Midwife Portal - ReproCare')

@include('includes.portal-theme')

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content">
        <div class="mw-container">
            @yield('midwife-content')
        </div>
    </main>
</div>
@endsection
