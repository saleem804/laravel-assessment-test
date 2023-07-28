<?php

namespace App\Jobs;

use App\Models\Affiliate;
use App\Models\Order;
use App\Services\ApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        
        try{
            $affiliate = Affiliate::find($this->order->affiliate_id);
            $email = $affiliate->user->email;
            $commission_owed = $this->order->commission_owed;
            $apiService->sendPayout($email, $commission_owed);
            $this->order->payout_status = Order::STATUS_PAID;
            $this->order->save();
        }catch(Exception $e){
            //print_r($e);exit;
            $this->order->payout_status = Order::STATUS_UNPAID;
            $this->order->save();
            return throw new RuntimeException();
        }
        
        
    }
}
