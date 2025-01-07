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

class BedController extends AbstractController
{

    #[Route('/api/bed', name: 'app_bed', methods: ['GET'])]
    #[OA\Get(
        path: '/api/bed',
        description: 'Retrieve a list of all beds in the system.',
        summary: 'Get all beds',
        tags: ['Bed'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of beds',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Bed')
                )
            )
        ]
    )]
    #[Route('/api/bed', name: 'app_bed')]
    public function index(BedRepository $bedRepository): Response
    {
        $beds = $bedRepository->findAll();
        return $this->json($beds,201, [], ['groups' => ['bedJson']]);
    }

    #[OA\Post(
        path: '/api/bed/create',
        description: 'Create a new bed and associate it with a room.',
        summary: 'Create a new bed',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'room_id', type: 'integer', description: 'ID of the room'),
                    new OA\Property(property: 'booked', type: 'boolean', description: 'Whether the bed is booked')
                ]
            )
        ),
        tags: ['Bed'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Bed created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Bed')
            ),
            new OA\Response(
                response: 404,
                description: 'Room not found'
            )
        ]
    )]
    #[Route('/api/bed/create', name: 'app_bed_create', methods: 'POST')]
    public function create(BedRepository $bedRepository,RoomRepository $roomRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        $data = json_decode($request->getContent(), true);

        $bed = $serializer->deserialize($request->getContent(),Bed::class,"json");
//        $author = $security->getUser();
//        if (!$author){
//            throw new AccessDeniedException('You must be logged in to create a sandwich.');
//        }
        $roomId = $data['room_id'];
        $room = $roomRepository->find($roomId);

        if (!$room) {
            return $this->json(['error' => 'Room not found'], 404);
        }
        $bed->setBooked(false);
        $bed->setRoom($room);

        $manager->persist($bed);
        $manager->flush();

        return $this->json($bed,201, [], ['groups' => ['bedJson']]);
    }

    #[OA\Put(
        path: '/api/bed/edit/{id}',
        description: 'Edit an existing bed by its ID.',
        summary: 'Edit a bed',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Bed')
        ),
        tags: ['Bed'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bed updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Bed')
            ),
            new OA\Response(
                response: 404,
                description: 'Bed not found'
            )
        ]
    )]
    #[Route('/api/bed/edit/{id}', name: 'app_bed_edit', methods: 'PUT')]
    public function edit(Request $request, Bed $bed, BedRepository $bedRepository, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): Response
    {
        if (!$bed)
        {
            return $this->json(['error' => 'Bed not found'], 404);
        }
//        $user = $security->getUser();
//        if ($sandwich->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to edit this sandwich.');
//        }

        $serializer->deserialize($request->getContent(), Bed::class, 'json', ['object_to_populate' => $bed]);

        $manager->flush();

        return $this->json($bed, 201);

    }

    #[OA\Delete(


        path: '/api/bed/delete/{id}',
        description: 'Delete a bed by its ID.',
        summary: 'Delete a bed',
        tags: ['Bed'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bed deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Bed deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Bed not found'
            )
        ]
    )]
    #[Route('/api/bed/delete/{id}', name: 'app_bed_delete', methods: ['DELETE'])]
    public function delete(Request $request, Bed $bed, Security $security, EntityManagerInterface $manager): Response
    {
        if (!$bed) {
            return $this->json(['error' => 'bed not found'], 404);
        }

//        $user = $security->getUser();
//        if ($bed->getAuthor() !== $user) {
//            throw new AccessDeniedException('You are not allowed to delete this sandwich.');
//        }

        $manager->remove($bed);
        $manager->flush();


        return $this->json(['message' => 'Bed deleted successfully'], 200);

    }
}
