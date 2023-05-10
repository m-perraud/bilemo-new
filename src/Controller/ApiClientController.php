<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_client', methods:'GET')]
    public function getClientsList(ClientRepository $clientRepository): JsonResponse
    {

        return $this->json($clientRepository->findAll(), 200, [], ['groups' => 'client:list']);

    }
}
