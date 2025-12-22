<?php

declare(strict_types=1);

namespace App\Infrastructure\Fulfillment\Clients\CJ;

use App\Services\Api\ApiResponse;

class CjProductApi extends CjBaseApi
{
    public function listCategories(): ApiResponse
    {
        return $this->client()->get('/v1/product/getCategory');
    }

    public function listProductsV2(array $filters = []): ApiResponse
    {
        $params = array_filter([
            'pageNum' => $filters['pageNum'] ?? null,
            'pageSize' => $filters['pageSize'] ?? null,
            'categoryId' => $filters['categoryId'] ?? null,
            'productSku' => $filters['productSku'] ?? null,
            'productName' => $filters['productName'] ?? null,
            'materialKey' => $filters['materialKey'] ?? null,
            'storeProductId' => $filters['storeProductId'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->get('/v1/product/listV2', $params);
    }

    public function listGlobalWarehouses(): ApiResponse
    {
        return $this->client()->get('/v1/product/globalWarehouse/list');
    }

    public function getWarehouseDetail(string $id): ApiResponse
    {
        return $this->client()->get('/v1/warehouse/detail', ['id' => $id]);
    }

    public function listProducts(array $filters = []): ApiResponse
    {
        $params = array_filter([
            'pageNum' => $filters['pageNum'] ?? null,
            'pageSize' => $filters['pageSize'] ?? null,
            'categoryId' => $filters['categoryId'] ?? null,
            'productSku' => $filters['productSku'] ?? null,
            'productName' => $filters['productName'] ?? null,
            'materialKey' => $filters['materialKey'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->get('/v1/product/list', $params);
    }

    public function getProduct(string $pid): ApiResponse
    {
        return $this->getProductBy(['pid' => $pid]);
    }

    public function getProductBy(array $criteria): ApiResponse
    {
        $params = array_filter([
            'pid' => $criteria['pid'] ?? null,
            'productSku' => $criteria['productSku'] ?? null,
            'variantSku' => $criteria['variantSku'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->get('/v1/product/query', $params);
    }

    public function addToMyProducts(string $pid): ApiResponse
    {
        return $this->client()->post('/v1/product/addMyProduct', ['pid' => $pid]);
    }

    public function listMyProducts(array $filters = []): ApiResponse
    {
        $params = array_filter([
            'pageNum' => $filters['pageNum'] ?? null,
            'pageSize' => $filters['pageSize'] ?? null,
            'productSku' => $filters['productSku'] ?? null,
            'productName' => $filters['productName'] ?? null,
            'storeProductId' => $filters['storeProductId'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->get('/v1/product/myProduct/query', $params);
    }

    public function getVariantsByPid(string $pid): ApiResponse
    {
        return $this->client()->get('/v1/product/variant/query', ['pid' => $pid]);
    }

    public function getVariantByVid(string $vid): ApiResponse
    {
        return $this->client()->get('/v1/product/variant/queryByVid', ['vid' => $vid]);
    }

    public function getStockByVid(string $vid): ApiResponse
    {
        return $this->client()->get('/v1/product/stock/queryByVid', ['vid' => $vid]);
    }

    public function getStockBySku(string $sku): ApiResponse
    {
        return $this->client()->get('/v1/product/stock/queryBySku', ['sku' => $sku]);
    }

    public function getStockByPid(string $pid): ApiResponse
    {
        return $this->client()->get('/v1/product/stock/queryByPid', ['pid' => $pid]);
    }

    public function getProductReviews(string $pid, int $pageNum = 1, int $pageSize = 20): ApiResponse
    {
        $params = [
            'pid' => $pid,
            'pageNum' => $pageNum,
            'pageSize' => $pageSize,
        ];

        return $this->client()->get('/v1/product/productComments', $params);
    }

    public function createSourcing(string $productUrl, ?string $note = null, ?string $sourceId = null): ApiResponse
    {
        $payload = array_filter([
            'productUrl' => $productUrl,
            'note' => $note,
            'sourceId' => $sourceId,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->post('/v1/product/sourcing/create', $payload);
    }

    public function querySourcing(?string $sourcingId = null, int $pageNum = 1, int $pageSize = 20): ApiResponse
    {
        $payload = array_filter([
            'sourcingId' => $sourcingId,
            'pageNum' => $pageNum,
            'pageSize' => $pageSize,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->client()->post('/v1/product/sourcing/query', $payload);
    }
}
