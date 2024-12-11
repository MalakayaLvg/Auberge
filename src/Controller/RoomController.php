<?php

namespace App\Controller;

use App\Entity\Bed;
use App\Entity\Room;
use App\Repository\BedRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RoomController extends AbstractController
{
    #[Route('/api/room', name: 'app_room')]
    public function index(RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findAll();
        return $this->json($rooms,201, [], ['groups' => ['roomJson']]);
    }

    #[Route('/api/room/create', name: 'app_room_create')]
    public function create(RoomRepository $roomRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        $room = $serializer->deserialize($request->getContent(),Room::class,"json");
        $author = $security->getUser();
        if (!$author){
            throw new AccessDeniedException('You must be logged in to create a sandwich.');
        }
        $manager->persist($room);
        $manager->flush();

        return $this->json($room,200, [], ['groups' => ['roomJson']]);
    }

    #[Route('/api/room/edit/{id}', name: 'app_room_edit', methods: 'PUT')]
    public function edit(Request $request, Room $room, RoomRepository $roomRepository, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        if (!$room)
        {
            return $this->json(['error' => 'Room not found'], 404);
        }
//        $user = $security->getUser();
//        if ($sandwich->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to edit this sandwich.');
//        }

        $serializer->deserialize($request->getContent(), Room::class, 'json', ['object_to_populate' => $room]);

        $manager->flush();

        return $this->json($room, 201);

    }

    #[Route('/api/room/delete/{id}', name: 'app_room_delete', methods: ['DELETE'])]
    public function delete(Request $request, Room $room, Security $security, EntityManagerInterface $manager): Response
    {
        if (!$room) {
            return $this->json(['error' => 'room not found'], 404);
        }

//        $user = $security->getUser();
//        if ($bed->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to delete this sandwich.');
//        }

        $manager->remove($room);
        $manager->flush();


        return $this->json(['message' => 'Room deleted successfully'], 200);

    }


}
