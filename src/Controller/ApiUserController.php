<?php

namespace App\Controller;

use ErrorException;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ApiUserController extends AbstractController
{
    #[Route('/api/users', name: 'api_user_index', methods:'GET')]
    public function getUsersList(UserRepository $userRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getUsersList-" . $page . "-" . $limit;
        
        $usersList = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit)
        {
            $client = $this->getUser();
            $item->tag("usersCache");
            return $userRepository->findAllUsersWithPagination($client, $page, $limit);
        });

        return $this->json($usersList, 200, [], ['groups' => 'client:list']);
    }


    #[Route('/api/users/{id}', name: 'api_user_details', methods:'GET')]
    public function getUserDetails(User $user): JsonResponse
    {
        if($user->getClient() == $this->getUser()){
            //dd($user);
            return $this->json($user, 200, [], ['groups' => 'client:details']);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }


    #[Route('/api/users/{id}', name: 'api_user_delete', methods:['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $manager): JsonResponse
    {
        if($user->getClient() == $this->getUser()){
            $manager->remove($user);
            $manager->flush();

            return new JsonResponse(null, 204);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }


    #[Route('/api/users', name: 'api_user_post', methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {

            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            $user->setClient($this->getUser());

        $errors = $validator->validate($user);

        if ($errors->count() > 0){
            return $this->json($errors, 400);
        }
            $manager->persist($user);
            $manager->flush();
    
            return $this->json($user, 201, [], ['groups' => 'client:details']);
    }
}
