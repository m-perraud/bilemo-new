<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheService
{
    public function __construct(
        private readonly TagAwareCacheInterface $cachePool,
        private readonly UserRepository $userRepository,
        private readonly ProductRepository $productRepository,
        private readonly Security $security
    ) {
    }


    public function cacheUserService($methodName, $page, $limit)
    {

        $idCache = $methodName . $page . "-" . $limit;

        $cache = $this->cachePool->get($idCache, function (ItemInterface $item) use ($page, $limit) {
            $client = $this->security->getUser();
            $item->tag("usersCache");
            $item->expiresAfter(60);
            return $this->userRepository->findAllUsersWithPagination($client, $page, $limit);
        });
        return $cache;
    }

    public function cacheProductService($methodName, $page, $limit)
    {

        $idCache = $methodName . $page . "-" . $limit;

        $cache = $this->cachePool->get($idCache, function (ItemInterface $item) use ($page, $limit) {
            $item->tag("productsCache");
            $item->expiresAfter(60);
            return $this->productRepository->findAllProductsWithPagination($page, $limit);
        });
        return $cache;
    }
}
