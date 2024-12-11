<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }


    #[Route("/api/payment/create/intent", name:"app_create_payment_intent", methods:["POST"])]
    public function createPaymentIntent(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'];

        $paymentIntent = $this->stripeService->createPaymentIntent($amount);

        return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
