<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use OpenApi\Attributes as OA;

class PaymentController extends AbstractController
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }


    #[Route("/api/payment/create/intent", name:"app_create_payment_intent", methods:["POST"])]
    #[OA\Post(
        path: "/api/payment/create/intent",
        description: "Create a payment intent using Stripe API.",
        summary: "Create a payment intent",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "amount", type: "integer", description: "The amount to be charged in cents")
                ]
            )
        ),
        tags: ["Payment"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Payment intent created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "clientSecret", type: "string", description: "The client secret of the payment intent")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid request data"
            )
        ]
    )]
    public function createPaymentIntent(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'];

        $paymentIntent = $this->stripeService->createPaymentIntent($amount);

        return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
