@extends('layouts.app')

@section('title', 'BHW President Portal - ReproCare')

@include('includes.portal-theme')

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content">
        <div class="mw-container">
            @yield('bhw-president-content')
        </div>
    </main>
</div>
@endsection
