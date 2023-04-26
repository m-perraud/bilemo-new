<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiProductController extends AbstractController
{
    #[Route('/api/products', name: 'api_product', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository): JsonResponse
    {

        return $this->json($productRepository->findAll(), 200, [], ['groups' => 'product:list']);

    }

    #[Route('/api/products/{id}', name: 'api_products_details', methods:'GET')]
    public function getProductDetails(Product $product): JsonResponse
    {
            return $this->json($product, 200, [], []);

    }
}
