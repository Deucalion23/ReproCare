<?php

namespace App\Http\Controllers;

use App\Models\Pregnancy;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin Dashboard for Health Officials
     */
    public function index(Request $request)
    {
        // Date range filter
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(6);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // 1. Number of pregnant women
        $totalPregnantWomen = Pregnancy::active()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        // 2. High-risk cases
        $highRiskCases = HealthRecord::where('risk_level', 'High')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $mediumRiskCases = HealthRecord::where('risk_level', 'Medium')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        // 3. Missed appointments
        $missedAppointments = Checkup::where('status', 'Missed')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->count();

        $completedAppointments = Checkup::where('status', 'Completed')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->count();

        $totalAppointments = $completedAppointments + $missedAppointments;
        $missedRate = $totalAppointments > 0 ? round(($missedAppointments / $totalAppointments) * 100, 1) : 0;

        // 4. Immunization coverage rate
        $totalPregnancies = Pregnancy::active()->count();
        $immunizedCount = HealthRecord::where('immunization_status', 'Complete')
            ->distinct('user_id')
            ->count('user_id');
        $immunizationCoverage = $totalPregnancies > 0 ? round(($immunizedCount / $totalPregnancies) * 100, 1) : 0;

        // 5. Knowledge improvement scores (from quizzes)
        $quizMaterials = LearningMaterial::where('material_type', 'quiz')->count();
        $avgQuizScore = 0;
        if ($quizMaterials > 0) {
            // Calculate average from quiz data (stored as JSON in quiz_data field)
            $allQuizzes = LearningMaterial::where('material_type', 'quiz')->get();
            $totalScore = 0;
            $count = 0;
            foreach ($allQuizzes as $quiz) {
                if ($quiz->quiz_data && isset($quiz->quiz_data['score'])) {
                    $totalScore += $quiz->quiz_data['score'];
                    $count++;
                }
            }
            $avgQuizScore = $count > 0 ? round($totalScore / $count, 1) : 0;
        }

        // Additional stats for charts
        $pregnancyTrend = $this->getPregnancyTrend($startDate, $endDate);
        $riskDistribution = $this->getRiskDistribution();
        $appointmentTrend = $this->getAppointmentTrend($startDate, $endDate);

        return view('admin.dashboard', compact(
            'totalPregnantWomen',
            'highRiskCases',
            'mediumRiskCases',
            'missedAppointments',
            'completedAppointments',
            'missedRate',
            'immunizationCoverage',
            'avgQuizScore',
            'pregnancyTrend',
            'riskDistribution',
            'appointmentTrend',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get pregnancy trend data for chart
     */
    private function getPregnancyTrend($startDate, $endDate)
    {
        $trend = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $month = $current->format('Y-m');
            $count = Pregnancy::active()
                ->whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->distinct('user_id')
                ->count('user_id');
            $trend[$month] = $count;
            $current->addMonth();
        }
        return $trend;
    }

    /**
     * Get risk distribution data for chart
     */
    private function getRiskDistribution()
    {
        return [
            'Low' => HealthRecord::where('risk_level', 'Low')->distinct('user_id')->count('user_id'),
            'Medium' => HealthRecord::where('risk_level', 'Medium')->distinct('user_id')->count('user_id'),
            'High' => HealthRecord::where('risk_level', 'High')->distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Get appointment trend data for chart
     */
    private function getAppointmentTrend($startDate, $endDate)
    {
        $trend = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $month = $current->format('Y-m');
            $completed = Checkup::where('status', 'Completed')
                ->whereYear('scheduled_date', $current->year)
                ->whereMonth('scheduled_date', $current->month)
                ->count();
            $missed = Checkup::where('status', 'Missed')
                ->whereYear('scheduled_date', $current->year)
                ->whereMonth('scheduled_date', $current->month)
                ->count();
            $trend[$month] = ['completed' => $completed, 'missed' => $missed];
            $current->addMonth();
        }
        return $trend;
    }
}
