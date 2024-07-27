<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesProfitsCharts extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Sales Vs Profits';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
       //monthly orders
       $data = [];
       $profits = [];

        $orders = Order::where('status', 'paid')->whereBetween(
            'created_at', [now()->subYear(), now()]
        )->orderBy('created_at')->get();

        // Group orders by month
        $ordersByMonth = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        });

        // Calculate total order price for each month
        foreach ($ordersByMonth as $month => $orders) {
            $totalPrice = $orders->sum(function ($order) {
                return $order->orderItems->sum('total_price');
            });
            $data[$month] = $totalPrice;
        }

        // Calculate total profits for each month
        foreach ($ordersByMonth as $month => $orders) {
            $totalPrice = $orders->sum(function ($order) {
                return $order->orderItems->sum('profit');
            });
            $profits[$month] = $totalPrice;
        }

        return [
        'datasets' => [
            [
                'label' => 'Sales',
                'data' => $data ,
                'backgroundColor' => '#4ade80',

            ],
            [
                'type' => 'bar',
                'label' => 'Profits',
                'data' => $profits,
                'backgroundColor' => '#6366f1',
                'borderColor' => '#818cf8',
            ]
        ],
        // 'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
     ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
