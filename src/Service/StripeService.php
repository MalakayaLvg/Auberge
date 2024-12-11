<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
public function __construct(string $stripeSecretKey)
{
Stripe::setApiKey($stripeSecretKey);
}

public function createPaymentIntent(int $amount, string $currency = 'eur')
{
return PaymentIntent::create([
'amount' => $amount,
'currency' => $currency,
]);
}
}