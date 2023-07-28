<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public $affiliate_id;
    public $commission_rate;
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        try {
            // TODO: Complete this method
            $user = User::where('email', $email)->where('type', User::TYPE_AFFILIATE)->first();
            if (!$user) {
                $user = new User;
                $user->name = $name;
                $user->email = $email;
                $user->type = User::TYPE_AFFILIATE;
                $user->save();
            }

            $affliate = new Affiliate;
            $affliate->user_id = $user->id;
            $affliate->merchant_id = $merchant->id;
            $affliate->commission_rate = $commissionRate;
            $affliate->discount_code = $this->apiService->createDiscountCode($merchant)['code'];
            $affliate->save();
            $this->affiliate_id = $affliate->id;
            $this->commission_rate = $affliate->commission_rate;
            Mail::to($user)->send(new AffiliateCreated($affliate));
            
            return $affliate;
        } catch (Exception $e) {

            return throw new AffiliateCreateException();
        }
    }

    public function get_affl_id() {
        return $this->affiliate_id;
    }
    public function get_affl_commission() {
        return $this->commission_rate;
    }
}
