<?php

namespace Laravel\Cashier\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Tests\Fixtures\User;
use Laravel\Cashier\Tests\TestCase;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Square\HttpClient\CurlClient as SquareCurlClient;
use Square\SquareClient;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase, WithLaravelMigrations;

    protected function setUp(): void
    {
        if (! getenv('SQUARE_ACCESS_TOKEN')) {
            $this->markTestSkipped('Square access token not set.');
        }

        parent::setUp();

        $curl = new SquareCurlClient([CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1]);
        $curl->setEnableHttp2(false);
    }

    protected static function square(array $options = []): SquareClient
    {
        return Cashier::square(array_merge(['accessToken' => getenv('SQUARE_ACCESS_TOKEN')], $options));
    }

    protected function createCustomer($description = 'taylor', array $options = []): User
    {
        return User::create(array_merge([
            'email' => "{$description}@cashier-test.com",
            'name' => 'Taylor Otwell',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ], $options));
    }
}
