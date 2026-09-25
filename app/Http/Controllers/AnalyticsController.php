<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsRequest;
use App\Services\AIInsightService;
use App\Services\AnalyticsScope;
use App\Services\MaternalAnalyticsService;
use App\Services\AnalyticsMap;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticsController extends Controller
{
    public function index(AnalyticsRequest $request)
    {
        $filters = app(AnalyticsScope::class)->forUser($request->user(), $request->validated());
        $analytics = app(MaternalAnalyticsService::class);
        $report = $analytics->report($filters);
        $suggestions = app(AIInsightService::class)->suggestions($report);
        $aiStatus = app(AIInsightService::class)->status();
        $areaOptions = $analytics->areaOptions($filters['rhu'] ?? null);
        $portal = $request->user()->role;
        $rhuOptions = AnalyticsScope::RHUS;
        $mapData = app(AnalyticsMap::class)->build($report);
        $queue = new LengthAwarePaginator(
            $report['queue']->forPage(max(1, $request->integer('page', 1)), 15)->values(),
            $report['queue']->count(), 15, max(1, $request->integer('page', 1)),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('cho.analytics', compact('report', 'suggestions', 'aiStatus', 'areaOptions', 'portal', 'rhuOptions', 'mapData', 'queue'));
    }

    public function chat(AnalyticsRequest $request)
    {
        $filters = app(AnalyticsScope::class)->forUser($request->user(), $request->validated());
        $report = app(MaternalAnalyticsService::class)->report($filters);
        return response()->json(app(AIInsightService::class)->chat($request->validated('question'), $report));
    }

    public function pregnancy(\Illuminate\Http\Request $request, int $id)
    {
        $scope = app(AnalyticsScope::class);
        $filters = $scope->forUser($request->user(), []);
        $pregnancy = \App\Models\Pregnancy::with(['woman', 'walkInPatient'])->findOrFail($id);
        $area = $scope->key($pregnancy->woman?->barangay ?? $pregnancy->walkInPatient?->barangay);
        abort_unless(in_array($area, $scope->areas($filters['rhu']) ?? [], true), 404);
        $pregnancy->load('healthRecords.recordedBy');
        return view('cho.pregnancies.show', ['pregnancy' => $pregnancy, 'portal' => 'rhu']);
    }
}
