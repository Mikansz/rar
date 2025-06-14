<?php

namespace App\Filament\Widgets\SuperAdmin;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Pengguna Berdasarkan Role';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    protected function getData(): array
    {
        $roleStats = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];

        foreach ($roleStats as $index => $stat) {
            $labels[] = ucfirst($stat->name);
            $data[] = $stat->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pengguna',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
