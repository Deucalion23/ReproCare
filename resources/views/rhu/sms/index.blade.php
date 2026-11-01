@extends('rhu.layout')

@section('title', 'SMS Alerts - RHU Portal | ReproCare')

@push('styles')
@include('includes.sms-workspace-styles')
@endpush

@section('rhu-content')
@include('includes.sms-workspace', ['wsRoute' => 'rhu.sms', 'wsAccent' => 'secondary'])
@endsection
