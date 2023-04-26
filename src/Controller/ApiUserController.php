<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiUserController extends AbstractController
{
    #[Route('/api/users', name: 'api_user_index', methods:'GET')]
    public function getUsersList(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), 200, [], ['groups' => 'client:list']);
    }

    #[Route('/api/users/{id}', name: 'api_user_details', methods:'GET')]
    public function getUserDetails(User $user): JsonResponse
    {
            return $this->json($user, 200, [], ['groups' => 'client:details']);
    }

    #[Route('/api/users/{id}', name: 'api_user_delete', methods:['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $manager): JsonResponse
    {
        
        $manager->remove($user);
        $manager->flush();

        return new JsonResponse(null, 204);
    }


    #[Route('/api/users', name: 'api_user_post', methods:'POST')]
    public function storeUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {

            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            $user->setClient($this->getUser());

        $errors = $validator->validate($user);

        if ($errors->count() > 0){
            return $this->json($errors, 400);
        }
            $manager->persist($user);
            $manager->flush();
    
            return $this->json($user, 201, [], ['groups' => 'client:create']);
    }
}
