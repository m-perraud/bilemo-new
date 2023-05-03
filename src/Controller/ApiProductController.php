<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiProductController extends AbstractController
{
    #[Route('/api/products', name: 'api_product', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);

        $idCache = "getProductsList-" . $page . "-" . $limit;
        
        $productsList = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit)
        {
            $item->tag("productsCache");
            return $productRepository->findAllProductsWithPagination($page, $limit);
        });

        return $this->json($productsList, 200, [], ['groups' => 'product:list']);

    }

    #[Route('/api/products/{id}', name: 'api_products_details', methods:'GET')]
    public function getProductDetails(Product $product): JsonResponse
    {
            return $this->json($product, 200, [], []);

    }
}
