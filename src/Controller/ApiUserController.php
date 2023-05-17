<?php

namespace App\Controller;

use ErrorException;
use App\Entity\User;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ApiUserController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer la liste des utilisateurs liés au client.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs liés au client",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"client:list"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Utilisateurs")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'api_user_index', methods:'GET')]
    public function getUsersList(UserRepository $userRepository, Request $request, TagAwareCacheInterface $cachePool, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getUsersList-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['client:list']);

        $usersList = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit) {
            $client = $this->getUser();
            $item->tag("usersCache");
            return $userRepository->findAllUsersWithPagination($client, $page, $limit);
        });

        $jsonUsersList = $serializer->serialize($usersList, 'json', $context);
        return new JsonResponse($jsonUsersList, 200, [], true);
    }

        /**
     * Cette méthode permet de récupérer les informations détaillées d'un utilisateur.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne les informations de l'utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"client:details"}))
     *     )
     * )
     * @OA\Tag(name="Utilisateurs")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'api_user_details', methods:'GET')]
    public function getUserDetails(User $user, SerializerInterface $serializer): JsonResponse
    {
        if($user->getClient() == $this->getUser()) {
            $context = SerializationContext::create()->setGroups(['client:details']);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, 200, [], true);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }

        /**
     * Cette méthode permet de supprimer un utilisateur lié au client.
     *
     * @OA\Response(
     *     response=204,
     *     description="Supprime l'utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"client:details"}))
     *     )
     * )
     * @OA\Tag(name="Utilisateurs")
     *
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'api_user_delete', methods:['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $manager, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if($user->getClient() == $this->getUser()) {

            $cachePool->invalidateTags(["usersCache"]);
            $manager->remove($user);
            $manager->flush();

            return new JsonResponse(null, 204);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
    }

        /**
     * Cette méthode permet de créer un utilisateur lié au client.
     *
     * @OA\Response(
     *     response=201,
     *     description="Crée l'utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"client:details"}))
     * ))

     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="Firstname", description="Le prénom de l'utilisateur à créer",type="string", example="Sam"),
     *         @OA\Property(property="Lastname", description="Le nom de famille", type="string", example="Oussa"),
     *         @OA\Property(property="Username", description="Le nom d'utilisateur", type="string", example="samoussa92"),
     *         @OA\Property(property="Email", description="L'adresse email", type="string", format="email", example="samoussa92@miamiam.fr")
     *       )
     *     )
     * )
     *
     * @OA\Tag(name="Utilisateurs")
     *
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'api_user_post', methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $jsonUser = $serializer->deserialize($request->getContent(), User::class, 'json');
        $jsonUser->setClient($this->getUser());

        $errors = $validator->validate($jsonUser);
        if ($errors->count() > 0) {
            return new JsonResponse($jsonUser, 400, []);
        }
        $manager->persist($jsonUser);
        $manager->flush();

        return new JsonResponse($jsonUser, 201, []);
    }
}
