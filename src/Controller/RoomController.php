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

use OpenApi\Attributes as OA;

class RoomController extends AbstractController
{

    #[OA\Get(
        path: '/api/room',
        description: 'Retrieve a list of all rooms in the system.',
        summary: 'Get all rooms',
        security: [['Bearer' => []]],
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of rooms',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Room')
                )
            )
        ]
    )]
    #[Route('/api/room', name: 'app_room', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findAll();
        return $this->json($rooms,200, [], ['groups' => ['roomJson']]);
    }

    #[OA\Post(
        path: '/api/room/create',
        description: 'Create a new room in the system.',
        summary: 'Create a new room',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: 'The name of the room')
                ],
                type: 'object'
            )
        ),
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Room created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    #[Route('/api/room/create', name: 'app_room_create', methods: ["POST"])]
    public function create(RoomRepository $roomRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        $room = $serializer->deserialize($request->getContent(),Room::class,"json");
        $author = $security->getUser();
        if (!$author){
            throw new AccessDeniedException('You must be logged in to create a room.');
        }
        $manager->persist($room);
        $manager->flush();

        return $this->json($room,201, [], ['groups' => ['roomJson']]);
    }

    #[OA\Put(
        path: '/api/room/edit/{id}',
        description: 'Edit an existing room by its ID.',
        summary: 'Edit a room',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: 'The new name of the room')
                ],
                type: 'object'
            )
        ),
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Room updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OA\Response(
                response: 404,
                description: 'Room not found'
            )
        ]
    )]
    #[Route('/api/room/edit/{id}', name: 'app_room_edit', methods: 'PUT')]
    public function edit(Request $request, Room $room, RoomRepository $roomRepository, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        if (!$room)
        {
            return $this->json(['error' => 'Room not found'], 404);
        }
//        $user = $security->getUser();
//        if ($sandwich->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to edit this room.');
//        }

        $serializer->deserialize($request->getContent(), Room::class, 'json', ['object_to_populate' => $room]);

        $manager->flush();

        return $this->json($room, 201);

    }

    #[OA\Delete(
        path: '/api/room/delete/{id}',
        description: 'Delete a room by its ID.',
        summary: 'Delete a room',
        security: [['Bearer' => []]],
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Room deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Room deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Room not found'
            )
        ]
    )]
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
