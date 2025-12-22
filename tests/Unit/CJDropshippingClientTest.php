<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Fulfillment\Clients\CJDropshippingClient;
use App\Services\Api\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CJDropshippingClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.cj', [
            'api_key' => 'test-api-key',
            'base_url' => 'https://example.test',
            'timeout' => 5,
        ]);

        Cache::forget('cj.access_token');
        Cache::forget('cj.refresh_token');
        Cache::put('cj.access_token', 'token', 600);
    }

    /**
     * @dataProvider endpointProvider
     */
    public function test_it_hits_expected_endpoint(
        string $method,
        array $args,
        string $httpMethod,
        string $expectedPath,
        string $payloadType,
        array $expectedPayload
    ): void {
        $captured = null;

        Http::fake([
            '*' => function ($request) use (&$captured) {
                $captured = $request;
                return Http::response([
                    'result' => true,
                    'code' => 200,
                    'message' => 'Success',
                    'data' => [],
                ], 200);
            },
        ]);

        $client = app(CJDropshippingClient::class);

        /** @var ApiResponse $resp */
        $resp = $client->{$method}(...$args);

        $this->assertTrue($resp->ok, 'API response should be ok');
        $this->assertNotNull($captured, 'Request should be captured');
        $this->assertSame(strtoupper($httpMethod), $captured->method());
        $this->assertStringStartsWith($expectedPath, $captured->url());
        $this->assertSame('token', $captured->header('CJ-Access-Token')[0] ?? null);

        if ($payloadType === 'query') {
            $query = [];
            parse_str(parse_url($captured->url(), PHP_URL_QUERY) ?? '', $query);
            foreach ($expectedPayload as $key => $value) {
                $this->assertEquals((string) $value, (string) ($query[$key] ?? null), "Query param {$key} should match");
            }
        } elseif ($payloadType === 'json') {
            $data = $captured->data();
            foreach ($expectedPayload as $key => $value) {
                $this->assertEquals($value, $data[$key] ?? null, "JSON payload {$key} should match");
            }
        }
    }

    public static function endpointProvider(): array
    {
        return [
            ['listCategories', [], 'GET', 'https://example.test/v1/product/getCategory', 'query', []],
            ['listProductsV2', [['pageNum' => 1, 'pageSize' => 10]], 'GET', 'https://example.test/v1/product/listV2', 'query', ['pageNum' => 1, 'pageSize' => 10]],
            ['listGlobalWarehouses', [], 'GET', 'https://example.test/v1/product/globalWarehouse/list', 'query', []],
            ['listProducts', [['pageNum' => 2, 'pageSize' => 5, 'productSku' => 'SKU123']], 'GET', 'https://example.test/v1/product/list', 'query', ['pageNum' => 2, 'pageSize' => 5, 'productSku' => 'SKU123']],
            ['getProduct', ['PID123'], 'GET', 'https://example.test/v1/product/query', 'query', ['pid' => 'PID123']],
            ['addToMyProducts', ['PID123'], 'POST', 'https://example.test/v1/product/addMyProduct', 'json', ['pid' => 'PID123']],
            ['listMyProducts', [['productSku' => 'SKU1']], 'GET', 'https://example.test/v1/product/myProduct/list', 'query', ['productSku' => 'SKU1']],
            ['getVariantsByPid', ['PID123'], 'GET', 'https://example.test/v1/product/variant/query', 'query', ['pid' => 'PID123']],
            ['getVariantByVid', ['VID123'], 'GET', 'https://example.test/v1/product/variant/queryByVid', 'query', ['vid' => 'VID123']],
            ['getStockByVid', ['VID123'], 'GET', 'https://example.test/v1/product/stock/queryByVid', 'query', ['vid' => 'VID123']],
            ['getStockBySku', ['SKU123'], 'GET', 'https://example.test/v1/product/stock/queryBySku', 'query', ['sku' => 'SKU123']],
            ['getStockByPid', ['PID123'], 'GET', 'https://example.test/v1/product/stock/queryByPid', 'query', ['pid' => 'PID123']],
            ['getProductReviews', ['PID123', 1, 5], 'GET', 'https://example.test/v1/product/productComments', 'query', ['pid' => 'PID123', 'pageNum' => 1, 'pageSize' => 5]],
            ['createSourcing', ['https://example.com/product', 'note'], 'POST', 'https://example.test/v1/product/sourcing/create', 'json', ['productUrl' => 'https://example.com/product', 'note' => 'note']],
            ['querySourcing', [null, 2, 10], 'POST', 'https://example.test/v1/product/sourcing/query', 'json', ['pageNum' => 2, 'pageSize' => 10]],
        ];
    }
}
