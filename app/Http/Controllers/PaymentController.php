<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Payment;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use App\Models\Payment as PaymentModel;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.st_key'));
        $customer = Customer::create([
            'email' => $request->stripeEmail,
            'source' => $request->stripeToken
        ]);
        $charge = Charge::create([
            'customer' => $customer->id,
            'amount' => $request->amount,
            'currency' => "jpy"
        ]);
        $payment = PaymentModel::create([
            'payment_flg' => true, 'reservation_id' => 0]
        );
        return response()->json([
            'items' => $request->only(['date', 'time', 'number', 'course_id']),
            'payment_id' => $payment->id,
            'payment_flg' => $payment->payment_flg
        ], 201);
    }
}
