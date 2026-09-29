<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel\Tests;

use ConnectMedia\Laravel\ConnectMediaServiceProvider;
use ConnectMedia\Sms\Client;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var list<array<string, mixed>> Decoded request bodies sent to the fake API. */
    protected array $requests = [];

    /** @var array<string, mixed> Response the fake API returns. */
    protected array $response = ['code' => '201', 'message' => 'Sent'];

    protected function getPackageProviders($app): array
    {
        return [ConnectMediaServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['ConnectMedia' => \ConnectMedia\Laravel\Facades\ConnectMedia::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('connectmedia.api_key', str_repeat('a', 64));
        $app['config']->set('connectmedia.sender', 'TESTCO');

        $app->singleton(Client::class, fn () => new Client(
            str_repeat('a', 64),
            Client::DEFAULT_BASE_URL,
            30,
            function (string $url, array $headers, string $body, int $timeout): string {
                $this->requests[] = json_decode($body, true);

                return (string) json_encode($this->response);
            }
        ));
    }
}
