<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Attribute\Groups;

use OpenApi\Attributes as OA;

class ReviewController extends AbstractController
{
    #[Route('/api/review/', name: 'app_review', methods: ['GET'])]
    #[OA\Get(
        path: '/api/review/',
        description: 'Retrieve all reviews in the system.',
        summary: 'Get all reviews',
        tags: ['Review'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of reviews',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Review')
                )
            )
        ]
    )]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findAll();

        return $this->json([$reviews], 200,[],["Groups" => ["reviewJson"]]);
    }

    #[Route('/api/review/create/booking/{id}', name: 'app_review_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/review/create/booking/{id}',
        description: 'Create a review linked to a specific booking.',
        summary: 'Create a review for a booking',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', description: 'The content of the review'),
                    new OA\Property(property: 'rating', type: 'integer', description: 'The rating of the review (1-5)')
                ]
            )
        ),
        tags: ['Review'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Review created successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data'
            )
        ]
    )]
    public function create(Booking $booking, Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['content']) || !isset($data['rating'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        $review = new Review();
        $review->setContent($data['content']);
        $review->setRating($data['rating']);
        $review->setAuthor($this->getUser());

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }
        $review->setBooking($booking);

        $em->persist($review);
        $em->flush();

        return $this->json(['message' => 'Review created successfully'], 201);
    }

    #[Route('/api/review/moderate/{id}', name: 'app_review_moderate')]
    #[OA\Delete(
        path: '/api/review/moderate/{id}',
        description: 'Delete a review by its ID.',
        summary: 'Moderate (delete) a review',
        tags: ['Review'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Review deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Review deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Review not found'
            )
        ]
    )]
    public function moderate($id, EntityManagerInterface $em): Response
    {
        $review = $em->getRepository(Review::class)->find($id);

        if (!$review) {
            return $this->json(['error' => 'Review not found'], 404);
        }

        $em->remove($review);
        $em->flush();

        return $this->json(['message' => 'Review deleted successfully'], 200);
    }
}
