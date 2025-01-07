<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use OpenApi\Attributes as OA;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'app_profile')]
    #[OA\Get(
        path: '/api/profile',
        description: 'Retrieve the profile information of the currently authenticated user.',
        summary: 'Get user profile',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'username', type: 'string', description: 'The username of the user'),
                        new OA\Property(property: 'phone', type: 'string', description: 'The phone number of the user'),
                        new OA\Property(property: 'address', type: 'string', description: 'The address of the user'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    public function profile(Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        return $this->json([
            'username' => $user->getUsername(),
            'phone' => $user->getPhone(),
            'address' => $user->getAdress(),
        ]);
    }

    #[Route('/api/profile/update', name: 'app_profile_update', methods: 'PUT')]
    #[OA\Put(
        path: '/api/profile/update',
        description: 'Update the profile information of the currently authenticated user.',
        summary: 'Update user profile',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: 'The new username of the user'),
                    new OA\Property(property: 'phone', type: 'string', description: 'The new phone number of the user'),
                    new OA\Property(property: 'address', type: 'string', description: 'The new address of the user'),
                ]
            )
        ),
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated!'),
                        new OA\Property(property: 'username', type: 'string', description: 'The updated username of the user'),
                        new OA\Property(property: 'phone', type: 'string', description: 'The updated phone number of the user'),
                        new OA\Property(property: 'address', type: 'string', description: 'The updated address of the user'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    public function updateProfile(Request $request, EntityManagerInterface $manager, Security $security):Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $user->setUsername($data['name']);
        }
        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }
        if (isset($data['address'])) {
            $user->setAdress($data['address']);
        }

        $manager->persist($user);
        $manager->flush();

        return $this->json([$user,"profile updated !"],201);
    }
}
