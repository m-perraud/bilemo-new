<?php

namespace App\Controller;

use ErrorException;
use App\Entity\Product;
use App\Model\NotFound;
use App\Model\NotAuthorized;
use App\Service\CacheService;
use OpenApi\Annotations as OA;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des produits.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des produits",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"product:list"}))
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Pas de contenu dans cette page.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=NotAuthorized::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer. Par défaut : 1",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer. Par défaut : 3",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Produits")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'api_product', methods: ['GET'])]
    public function getProductsList(Request $request, SerializerInterface $serializer, CacheService $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);
        $methodName = "getProductsList-";

        $context = SerializationContext::create()->setGroups(['product:list']);

        $productsList = $cache->cacheProductService($methodName, $page, $limit);
        $allProductsList = $cache->cacheAllProductsService($methodName);
        $allProducts = count($allProductsList);

        $response = [];        
        $response['page'] = $page;
        $response['limit'] = $limit;
        $response['nbr pages'] = number_format($allProducts / $limit, 0);
        $response['total products'] = $allProducts;        
        $response['result'] = $productsList;

        if ($page > $response['nbr pages']){
            throw new ErrorException("Cette page est vide, veuillez vérifier le nombre de pages disponibles.");
        }

        $jsonProductsList = $serializer->serialize($response, 'json', $context);
        return new JsonResponse($jsonProductsList, 200, [], true);
    }



    /**
     * Cette méthode permet de récupérer le détail d'un produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"product:list"}))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="L'élément recherché n'existe pas.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=NotFound::class))
     *     )
     * )
     *
     * @OA\Tag(name="Produits")
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'api_product_details', methods: 'GET')]
    public function getProductDetails(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, 200, [], true);
    }
}
