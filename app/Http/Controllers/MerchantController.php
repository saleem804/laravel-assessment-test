<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\OrderService;
class MerchantController extends Controller
{
    private $merchantService;
    private $orderService;
    public function __construct(
        MerchantService $merchantService,
        OrderService $orderService
    ) {
        $this->merchantService = $merchantService;
        $this->orderService = $orderService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $noaff_commission = Order::where('affiliate_id', null)->sum('commission_owed');
        //print_r($order->toArray());
        $count = Order::whereBetween('created_at', [$from, $to])->count();
        $revenue = Order::whereBetween('created_at', [$from, $to])->sum('subtotal');
        $commissions_owed = Order::whereBetween('created_at', [$from, $to])->sum('commission_owed');
        $resp = ['count' => $count, 'revenue' => $revenue, 'commissions_owed' => $commissions_owed - $noaff_commission];
        return response()->json($resp);
    }
}
