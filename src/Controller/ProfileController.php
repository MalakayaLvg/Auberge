<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'app_profile')]
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
