<?php

namespace App\Controller;

use App\Entity\Bed;
use App\Entity\Booking;
use App\Entity\Room;
use App\Repository\BedRepository;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BookingController extends AbstractController
{
    #[Route('/api/booking', name: 'app_booking', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        $bookings = $bookingRepository->findAll();
        return $this->json($bookings,201, [], ['groups' => ['bookingJson']]);
    }

    #[Route('/api/booking/create', name: 'create_booking', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, BedRepository $bedRepository, Security $security,EmailService $emailService) : Response
    {
        $data = json_decode($request->getContent(), true);

        $booking = $serializer->deserialize($request->getContent(),Booking::class,"json");
        $user = $security->getUser();
        if (!$user){
            throw new AccessDeniedException('You must be logged in to make a reservation.');
        }
        $bedId = $data['bed_id'];
        $bed = $bedRepository->find($bedId);
        $startingDate = new \DateTime($data['startingDate']);
        $endingDate = new \DateTime($data['endingDate']);
        if (!$bed) {
            return $this->json(['error' => 'Bed not found'], 404);
        }

        if ($bed->isBooked()){
            return $this->json(['error' => 'Bed already booked !'], 404);
        }
        $bed->setBooked(true);
        $booking->setCustomer($user);
        $booking->setStatus('pending');
        $booking->setBed($bed);

        // Calcul du prix total en fonction des nuits réservées
        $interval = $startingDate->diff($endingDate)->days;
        $totalPrice = $bed->getPricePerNight() * $interval;
        $booking->setTotalPrice($totalPrice);

        $manager->persist($booking);
        $manager->flush();

        $emailService->sendBookingConfirmationEmail('malakaya3902@gmail.com', "ouais c'est greg");

        return $this->json($booking,201, [], ['groups' => ['bookingJson']]);
    }

    #[Route('/api/booking/delete/{id}', name: 'app_booking_delete', methods: ['DELETE'])]
    public function delete(Request $request, Booking $booking, Security $security, EntityManagerInterface $manager): Response
    {
        if (!$booking) {
            return $this->json(['error' => 'booking not found'], 404);
        }

//        $user = $security->getUser();
//        if ($bed->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to delete this sandwich.');
//        }

        $manager->remove($booking);
        $manager->flush();


        return $this->json(['message' => 'Booking deleted successfully'], 200);

    }

    #[Route('/api/booking/edit/{id}', name: 'app_booking_edit', methods: 'PUT')]
    public function edit(Request $request, Booking $booking, BookingRepository $bookingRepository, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        if (!$booking)
        {
            return $this->json(['error' => 'Booking not found'], 404);
        }
//        $user = $security->getUser();
//        if ($sandwich->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to edit this sandwich.');
//        }

        $serializer->deserialize($request->getContent(), Booking::class, 'json', ['object_to_populate' => $booking]);

        $manager->flush();

        return $this->json($booking, 201, [], ['Groups' => ['bookingJson']]);

    }


    #[Route("/api/booking/cancel/{id}", name: "app_booking_cancel", methods: ["POST"])]
    public function cancel($id,Booking $booking, EntityManagerInterface $manager, Security $security)
    {
        $user = $security->getUser();
        if ($booking->getCustumer() !== $user) {
            throw new AccessDeniedException('You are not allowed to delete this booking.');
        }
        $booking = $manager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        $booking->setStatus('cancelled');

        // Rendre la chambre disponible à nouveau
        $bed = $booking->getBed();
        $bed->setBooked(false);

        $manager->persist($bed);
        $manager->flush();

        return $this->json(['message' => 'Réservation annulée avec succès']);
    }

    #[Route("/api/booking/history", name: "app_booking_history", methods: ["GET"])]
    public function history(Security $security)
    {
        $user = $security->getUser();
        $bookings= $user->getBookings();

        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'bed_number' => $booking->getBed()->getNumber(),
                'startingDate' => $booking->getStartingDate()->format('Y-m-d'),
                'endingDate' => $booking->getEndingDate()->format('Y-m-d'),
                'status' => $booking->getStatus(),
                'total_price' => $booking->getTotalPrice(),
            ];
        }

        return $this->json($data, 200);
    }
}
