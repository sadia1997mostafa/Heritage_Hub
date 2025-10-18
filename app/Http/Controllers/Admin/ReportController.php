<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // Show products which contributed to platform revenue
    public function platformRevenueProducts(Request $req)
    {
        // Aggregate vendor_earnings by product via order_items -> orders
        // vendor_earnings have order_id; order_items link order_id -> product_id

        $q = DB::table('vendor_earnings as ve')
            ->join('order_items as oi', 've.order_id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->select(
                'p.id as product_id',
                'p.title as product_title',
                DB::raw('SUM(ve.gross_amount) as total_gross'),
                DB::raw('SUM(COALESCE(ve.platform_fee, ve.gross_amount * 0.10)) as total_platform_fee'),
                DB::raw('COUNT(DISTINCT ve.id) as earnings_count')
            )
            ->groupBy('p.id', 'p.title')
            ->orderByDesc('total_platform_fee');

        $products = $q->paginate(50);

        return view('admin.reports.platform-revenue', compact('products'));
    }
}
