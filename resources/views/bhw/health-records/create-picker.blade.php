@extends('bhw.layout')
@section('title', 'Choose Patient - ReproCare')
@section('bhw-content')
<div class="py-4" style="max-width:720px;margin:0 auto;">
    <h2 class="page-title">Add Health Record</h2>
    <p class="page-subtitle">Choose a patient first — records need an owner.</p>
    <div class="card"><div class="card-body">
        <form method="GET" action="">
            <label class="form-label fw-bold">Enrolled patient</label>
            <select class="form-control mb-3" id="pickUser" onchange="if(this.value)window.location='{{ url('bhw/health-records/create') }}/'+this.value">
                <option value="">Select woman...</option>
                @foreach($women as $w)<option value="{{ $w->id }}">{{ $w->name }} — {{ $w->barangay }}</option>@endforeach
            </select>
            <label class="form-label fw-bold">Unlinked patient</label>
            <select class="form-control" onchange="if(this.value)window.location='{{ url('bhw/health-records/create') }}/0/'+this.value">
                <option value="">Select walk-in...</option>
                @foreach($walkIns as $w)<option value="{{ $w->id }}">{{ $w->full_name }} — {{ $w->barangay }}</option>@endforeach
            </select>
        </form>
    </div></div>
</div>
@endsection
