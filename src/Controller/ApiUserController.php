<?php

namespace App\Controller;

use ErrorException;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiUserController extends AbstractController
{
    #[Route('/api/users', name: 'api_user_index', methods:'GET')]
    public function getUsersList(UserRepository $userRepository, Request $request, TagAwareCacheInterface $cachePool, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getUsersList-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['client:list']);  

        $usersList = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit)
        {
            $client = $this->getUser();
            $item->tag("usersCache");
            return $userRepository->findAllUsersWithPagination($client, $page, $limit);
        });

        $jsonUsersList = $serializer->serialize($usersList, 'json', $context);
        return new JsonResponse($jsonUsersList, 200, [], true);
    }


    #[Route('/api/users/{id}', name: 'api_user_details', methods:'GET')]
    public function getUserDetails(User $user, SerializerInterface $serializer): JsonResponse
    {
        if($user->getClient() == $this->getUser()){
            $context = SerializationContext::create()->setGroups(['client:details']);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, 200, [], true);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }


    #[Route('/api/users/{id}', name: 'api_user_delete', methods:['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $manager, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if($user->getClient() == $this->getUser()){

            $cachePool->invalidateTags(["usersCache"]);
            $manager->remove($user);
            $manager->flush();

            return new JsonResponse(null, 204);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }


    #[Route('/api/users', name: 'api_user_post', methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $jsonUser = $serializer->deserialize($request->getContent(), User::class, 'json');
        $jsonUser->setClient($this->getUser());

        $errors = $validator->validate($jsonUser);
        if ($errors->count() > 0){
            return new JsonResponse($jsonUser, JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
            $manager->persist($jsonUser);
            $manager->flush();

            return new JsonResponse($jsonUser, 201, []);
    }
}
