<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(UserRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/user', name: 'app_user_create', methods: ['POST'])] 
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $request->toArray();
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json($user);
    }

    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(UserRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $repo->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $em->remove($user);
        $em->flush();

        return $this->json(['status' => 'deleted']);
    }

    #[Route('/user/{id}', name: 'app_user_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, UserRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $repo->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = $request->toArray();
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setFirstName($data['firstName'] ?? $user->getFirstName());
        $user->setLastName($data['lastName'] ?? $user->getLastName());

        $em->flush();

        return $this->json($user);
    }
}
