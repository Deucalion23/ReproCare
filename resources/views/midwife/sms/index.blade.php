@extends('midwife.layout')

@section('title', 'SMS Alerts | ReproCare')

@push('styles')
@include('includes.sms-workspace-styles')
@endpush

@section('midwife-content')
@include('includes.sms-workspace', ['wsRoute' => 'midwife.sms', 'wsAccent' => 'primary'])
@endsection
