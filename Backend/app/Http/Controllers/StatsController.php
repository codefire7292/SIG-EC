<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Module;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Display detailed statistics.
     */
    public function index(): Response
    {
        return Inertia::render('Stats/Index', [
            'growth_data' => $this->getGrowthData(),
            'module_performance' => $this->getModulePerformance(),
            'attendance_trends' => $this->getAttendanceTrends(),
        ]);
    }

    private function getGrowthData(): array
    {
        // Users created per month for the last 6 months
        return User::select(DB::raw("to_char(created_at, 'Mon') as month"), DB::raw("count(*) as count"))
            ->groupBy('month')
            ->orderBy(DB::raw("min(created_at)"))
            ->take(6)
            ->get()
            ->toArray();
    }

    private function getModulePerformance(): array
    {
        return Module::withCount('certificates')
            ->get()
            ->map(fn($m) => [
                'name' => $m->titre,
                'certificates' => $m->certificates_count
            ])->toArray();
    }

    private function getAttendanceTrends(): array
    {
        // Static mockup for now as real trend data might be sparse in dev
        return [
            ['week' => 'Semaine 1', 'rate' => 82],
            ['week' => 'Semaine 2', 'rate' => 85],
            ['week' => 'Semaine 3', 'rate' => 78],
            ['week' => 'Semaine 4', 'rate' => 91],
        ];
    }
}
