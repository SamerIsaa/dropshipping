<?php

declare(strict_types=1);

namespace App\Infrastructure\Fulfillment\Clients;

use App\Services\Api\ApiClient;
use App\Services\Api\ApiException;
use App\Services\Api\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use App\Infrastructure\Fulfillment\Clients\CJ\CjAuthApi;
use App\Infrastructure\Fulfillment\Clients\CJ\CjProductApi;
use App\Infrastructure\Fulfillment\Clients\CJ\CjSettingsApi;

class CJDropshippingClient
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private ApiClient $client;
    private ?CjAuthApi $authApi = null;
    private ?CjSettingsApi $settingsApi = null;
    private ?CjProductApi $productApi = null;

    public function __construct()
    {
        $config = config('services.cj', []);
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->timeout = (int) ($config['timeout'] ?? 10);

        if (! $this->apiKey) {
            throw new RuntimeException('CJ API key is not configured.');
        }

        $this->client = new ApiClient($this->baseUrl, [], $this->timeout);
    }

    public function getAccessToken(bool $forceRefresh = false): string
    {
        $cacheKey = 'cj.access_token';
        $refreshKey = 'cj.refresh_token';
        $cached = $forceRefresh ? null : Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        $refreshToken = Cache::get($refreshKey);
        if ($refreshToken && ! $forceRefresh) {
            $token = $this->refreshAccessToken($refreshToken);
            if ($token) {
                return $token;
            }
        }

        $resp = $this->client->post('/v1/authentication/getAccessToken', [
            'apiKey' => $this->apiKey,
        ]);

        $data = $resp->data ?? [];
        $accessToken = $data['accessToken'] ?? null;
        $accessExpiry = $data['accessTokenExpiryDate'] ?? null;
        $refresh = $data['refreshToken'] ?? null;
        $refreshExpiry = $data['refreshTokenExpiryDate'] ?? null;

        if (! $accessToken) {
            throw new RuntimeException('CJ getAccessToken missing accessToken.');
        }

        Cache::put($cacheKey, $accessToken, $this->ttlFromDate($accessExpiry, 60 * 60 * 24 * 10));

        if ($refresh) {
            Cache::put($refreshKey, $refresh, $this->ttlFromDate($refreshExpiry, 60 * 60 * 24 * 120));
        }

        return $accessToken;
    }

    public function refreshAccessToken(string $refreshToken): ?string
    {
        $cacheKey = 'cj.access_token';
        $refreshKey = 'cj.refresh_token';

        try {
            $resp = $this->client->post('/v1/authentication/refreshAccessToken', [
                'refreshToken' => $refreshToken,
            ]);
        } catch (ApiException) {
            return null;
        }

        $data = $resp->data ?? [];
        $accessToken = $data['accessToken'] ?? null;
        $accessExpiry = $data['accessTokenExpiryDate'] ?? null;
        $refresh = $data['refreshToken'] ?? null;
        $refreshExpiry = $data['refreshTokenExpiryDate'] ?? null;

        if ($accessToken) {
            Cache::put($cacheKey, $accessToken, $this->ttlFromDate($accessExpiry, 60 * 60 * 24 * 10));
        }

        if ($refresh) {
            Cache::put($refreshKey, $refresh, $this->ttlFromDate($refreshExpiry, 60 * 60 * 24 * 120));
        }

        return $accessToken;
    }

    public function logout(): ApiResponse
    {
        return $this->auth()->logout();
    }

    public function getSettings(): ApiResponse
    {
        return $this->settings()->getSettings();
    }

    public function updateAccount(?string $openName = null, ?string $openEmail = null): ApiResponse
    {
        return $this->settings()->updateAccount($openName, $openEmail);
    }

    public function getProduct(string $pid): ApiResponse
    {
        return $this->products()->getProduct($pid);
    }

    public function getProductBy(array $criteria): ApiResponse
    {
        return $this->products()->getProductBy($criteria);
    }

    public function listProducts(array $filters = []): ApiResponse
    {
        return $this->products()->listProducts($filters);
    }

    public function listProductsV2(array $filters = []): ApiResponse
    {
        return $this->products()->listProductsV2($filters);
    }

    public function listGlobalWarehouses(): ApiResponse
    {
        return $this->products()->listGlobalWarehouses();
    }

    public function getWarehouseDetail(string $id): ApiResponse
    {
        return $this->products()->getWarehouseDetail($id);
    }

    public function listCategories(): ApiResponse
    {
        return $this->products()->listCategories();
    }

    public function getVariantsByPid(string $pid): ApiResponse
    {
        return $this->products()->getVariantsByPid($pid);
    }

    public function getVariantByVid(string $vid): ApiResponse
    {
        return $this->products()->getVariantByVid($vid);
    }

    public function getStockByVid(string $vid): ApiResponse
    {
        return $this->products()->getStockByVid($vid);
    }

    public function getStockBySku(string $sku): ApiResponse
    {
        return $this->products()->getStockBySku($sku);
    }

    public function getStockByPid(string $pid): ApiResponse
    {
        return $this->products()->getStockByPid($pid);
    }

    public function getProductReviews(string $pid, int $pageNum = 1, int $pageSize = 20): ApiResponse
    {
        return $this->products()->getProductReviews($pid, $pageNum, $pageSize);
    }

    public function createSourcing(string $productUrl, ?string $note = null, ?string $sourceId = null): ApiResponse
    {
        return $this->products()->createSourcing($productUrl, $note, $sourceId);
    }

    public function querySourcing(?string $sourcingId = null, int $pageNum = 1, int $pageSize = 20): ApiResponse
    {
        return $this->products()->querySourcing($sourcingId, $pageNum, $pageSize);
    }

    public function addToMyProducts(string $pid): ApiResponse
    {
        return $this->products()->addToMyProducts($pid);
    }

    public function listMyProducts(array $filters = []): ApiResponse
    {
        return $this->products()->listMyProducts($filters);
    }

    public function withToken(): string
    {
        return $this->getAccessToken();
    }

    public function authClient(): ApiClient
    {
        $token = $this->getAccessToken();
        return $this->client->withToken($token, 'CJ-Access-Token');
    }

    public function auth(): CjAuthApi
    {
        return $this->authApi ??= new CjAuthApi($this);
    }

    public function settings(): CjSettingsApi
    {
        return $this->settingsApi ??= new CjSettingsApi($this);
    }

    public function products(): CjProductApi
    {
        return $this->productApi ??= new CjProductApi($this);
    }

    private function ttlFromDate(?string $date, int $fallbackSeconds): int
    {
        if (! $date) {
            return $fallbackSeconds;
        }

        try {
            $expiresAt = Carbon::parse($date);
            $seconds = $expiresAt->diffInSeconds(now(), false);
            return $seconds > 0 ? $seconds : $fallbackSeconds;
        } catch (\Exception) {
            return $fallbackSeconds;
        }
    }
}
