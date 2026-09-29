<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array send(string|array $to, string $message, array $options = [])
 * @method static array balance()
 * @method static array history(int $limit = 50, int $offset = 0, ?string $startDate = null, ?string $endDate = null)
 * @method static array inbox(int $limit = 50)
 * @method static \ConnectMedia\Sms\Client client()
 *
 * @see \ConnectMedia\Laravel\ConnectMedia
 */
class ConnectMedia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ConnectMedia\Laravel\ConnectMedia::class;
    }
}
