<?php 
use Application\EntityManager;
use Application\Repository;

[Route: '/users/{id}', methods: ['DELETE']]
public function delete(int $id, EntityManager: $em, Repository: $repo): JsonResponse
{
    $user = $repo->find($id);

    if (!$user) {
        {return $this->json(['error' => 'not found'], 404);}
    }

    $em->remove($user);
    $em->flush()

    return $this->json(['status' => 'deleted']);
}