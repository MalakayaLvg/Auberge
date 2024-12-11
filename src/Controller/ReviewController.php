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

class ReviewController extends AbstractController
{
    #[Route('/api/review/', name: 'app_review', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findAll();

        return $this->json([$reviews], 200,[],["Groups" => ["reviewJson"]]);
    }

    #[Route('/api/review/create/booking/{id}', name: 'app_review_create', methods: ['POST'])]
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
