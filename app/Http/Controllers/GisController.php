<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsRequest;
use App\Services\AnalyticsMap;
use App\Services\AnalyticsScope;
use App\Services\MaternalAnalyticsService;

/** Legacy map URLs now lead to the scoped analytics report. */
class GisController extends Controller
{
    public function index(AnalyticsRequest $request)
    {
        $filters = app(AnalyticsScope::class)->forUser($request->user(), $request->validated());
        return redirect()->to(route($request->user()->role.'.analytics', $filters).'#risk-map');
    }

    public function data(AnalyticsRequest $request)
    {
        $filters = app(AnalyticsScope::class)->forUser($request->user(), $request->validated());
        $report = app(MaternalAnalyticsService::class)->report($filters);
        return response()->json(app(AnalyticsMap::class)->build($report));
    }
}
