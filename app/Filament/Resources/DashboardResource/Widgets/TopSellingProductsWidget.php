<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class TopSellingProductsWidget extends Widget
{
    protected static string $view = 'filament.resources.dashboard-resource.widgets.top-selling-products-widget';

    public function render(): view
    {
        $topSellingProducts = DB::table('transaksi_detail')
            ->select('produk_id', DB::raw('SUM(jumlah) as total_quantity'))
            ->groupBy('produk_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Get product names and details
        $products = DB::table('produk')->whereIn('id', $topSellingProducts->pluck('produk_id'))->get()->keyBy('id');

        $topSellingProducts = $topSellingProducts->map(function ($item) use ($products) {
            return [
                'produk_id' => $item->produk_id,
                'produk_name' => $products->get($item->produk_id)->nama_produk ?? 'Unknown',
                'total_quantity' => $item->total_quantity,
            ];
        });

        return view('filament.widgets.top-selling-products-widget', [
            'topSellingProducts' => $topSellingProducts,
        ]);
    }
}
