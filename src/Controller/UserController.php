<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ], $users);

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_user_new', methods: ['POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Récupère les données JSON envoyées
    $data = json_decode($request->getContent(), true);

    dump('voici les infos', $data);

    $username = $data['username'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$username || !$email || !$password) {
        return new JsonResponse(['error' => 'Missing fields'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $user = new User();
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
    $user->setCreatedAt(new \DateTimeImmutable());

    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse([
        'message' => 'User created successfully',
        'id' => $user->getId(),
    ], JsonResponse::HTTP_CREATED);
}

    #[Route('/{id<\d+>}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'profilePicture' => $user->getProfilePicture(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }


    #[Route('/user/{id}/edit', name: 'app_user_edit', methods: ['PUT', 'POST'])]
    public function edit(Request $request, UserRepository $userRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $userRepository->find($id);


        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            $data = $request->request->all(); 
        }

       

        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return new JsonResponse(['message' => 'User updated successfully']);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully']);
    }
}
