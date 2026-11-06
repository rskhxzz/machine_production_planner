<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ?: Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->input('end_date')
            ?: Carbon::now()->endOfMonth()->toDateString();

        $request->merge([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($validated['start_date'])->toDateString();
        $endDate = Carbon::parse($validated['end_date'])->toDateString();

        $summary = $this->dashboardService->getSummary(
            $startDate,
            $endDate
        );

        return view('dashboard.index', compact(
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
