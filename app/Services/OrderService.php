<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);
        $order = Order::where('external_order_id', $data['order_id'])->first();
        $get_aff = Affiliate::latest()->first();
        
        if (!$order && $merchant && $affiliate) {
            $order = new Order;
            $order->external_order_id = $data['order_id'];
            $order->merchant_id = $merchant->id;
            $order->affiliate_id = $get_aff->id;
            $order->subtotal = $data['subtotal_price'];
            $order->commission_owed = $get_aff->commission_rate * $data['subtotal_price'];
            $order->save();
        }
        return $order;
    }
}
