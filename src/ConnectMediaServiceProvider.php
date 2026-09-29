<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel;

use ConnectMedia\Laravel\Commands\BalanceCommand;
use ConnectMedia\Laravel\Commands\TestCommand;
use ConnectMedia\Sms\Client;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class ConnectMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/connectmedia.php', 'connectmedia');

        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['connectmedia'];
            if (empty($config['api_key'])) {
                throw new \RuntimeException('Set CONNECTMEDIA_API_KEY in your .env file (see config/connectmedia.php).');
            }

            return new Client($config['api_key'], $config['base_url'], (int) $config['timeout']);
        });

        $this->app->singleton(ConnectMedia::class, fn ($app) => new ConnectMedia(
            fn () => $app->make(Client::class),
            $app['config']['connectmedia.sender']
        ));
        $this->app->alias(ConnectMedia::class, 'connectmedia');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/connectmedia.php' => config_path('connectmedia.php'),
            ], 'connectmedia-config');

            $this->commands([TestCommand::class, BalanceCommand::class]);
        }

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('connectmedia', fn ($app) => $app->make(ConnectMediaChannel::class));
        });
    }
}
