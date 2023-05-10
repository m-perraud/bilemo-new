<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

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
     * @OA\Tag(name="Produits")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'api_product', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository, Request $request, TagAwareCacheInterface $cachePool, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);

        $idCache = "getProductsList-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['product:list']);
        
        $productsList = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit)
        {
            $item->tag("productsCache");
            return $productRepository->findAllProductsWithPagination($page, $limit);
        });

        $jsonProductsList = $serializer->serialize($productsList, 'json', $context);
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
     *   
     * @OA\Tag(name="Produits")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'api_products_details', methods:'GET')]
    public function getProductDetails(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, 200, [], true);
    }
}
