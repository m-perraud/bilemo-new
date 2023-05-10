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

class ApiProductController extends AbstractController
{
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




    #[Route('/api/products/{id}', name: 'api_product_details', methods:'GET')]
    public function getProductDetails(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, 200, [], true);
    }
}
