<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

use OpenApi\Attributes as OA;
class RegistrationController extends AbstractController
{
    #[OA\Post(
        path: '/register',
        description: 'Creates a new user account and a related profile.',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
                    new OA\Property(property: 'password', type: 'string', example: 'mypassword123'),
                ],
                type: 'object'
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 300,
                description: 'Username already exists',
                content: new OA\JsonContent(
                    type: 'string',
                    example: 'username already exists'
                )
            )
        ]
    )]
    #[Route('/register', name: 'app_register', methods: 'POST')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        $user = $serializer->deserialize($request->getContent(),User::class,'json');
        $userExists = $userRepository->findOneBy(["username"=>$user->getUsername()]);

        if ($userExists) {
            return $this->json("username already exits",300);
        }

        $plainPassword = $user->getPassword();

        $user->setPassword($userPasswordHasher->hashPassword($user,$plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: '/api/login',
        description: 'Logs in a user and returns a JWT token if credentials are valid.',
        summary: 'Authenticate a user',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
                    new OA\Property(property: 'password', type: 'string', example: 'mypassword123'),
                ],
                type: 'object'
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful login, returns JWT token.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    type: 'string',
                    example: 'Invalid username or password.'
                )
            )
        ]
    )]
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {

    }
}
