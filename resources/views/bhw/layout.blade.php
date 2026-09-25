@extends('layouts.app')

@section('title', 'BHW Portal - ReproCare')

@include('includes.portal-theme')

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content">
        <div class="mw-container">
            @yield('bhw-content')
        </div>
    </main>
</div>
@endsection
