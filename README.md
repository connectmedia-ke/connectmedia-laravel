# Connect Media SMS for Laravel

[![Tests](https://github.com/connectmedia-ke/connectmedia-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/connectmedia-ke/connectmedia-laravel/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/connectmedia/laravel.svg)](https://packagist.org/packages/connectmedia/laravel)
[![License](https://img.shields.io/packagist/l/connectmedia/laravel.svg)](LICENSE)

Send SMS and SMS notifications from Laravel with [Connect Media](https://connectmedia.co.ke/), a Kenyan bulk SMS provider delivering to Safaricom, Airtel and Telkom numbers and to 190+ countries. Built on the official [`connectmedia/sms`](https://github.com/connectmedia-ke/connectmedia-php) PHP client.

- `ConnectMedia::send('0712345678', 'Hello')` from anywhere in your app
- A **notification channel** so `$user->notify(...)` can go out by SMS, queued or not
- Kenyan numbers normalised for you (`0712…`, `+254 712…`, `254712…`)
- Default sender ID from config, scheduling, balance, sent history and replies
- `php artisan connectmedia:test` to check your setup in seconds

Supports Laravel 10, 11 and 12 on PHP 8.1+.

## Installation

```bash
composer require connectmedia/laravel
```

Add your credentials to `.env`:

```dotenv
CONNECTMEDIA_API_KEY=your-64-character-api-key
CONNECTMEDIA_SENDER=YourBrand
```

Get an API key by creating a free account at [dashboard.connectmedia.co.ke](https://dashboard.connectmedia.co.ke/) and generating one under **Profile, then API keys**. Your sender ID must be registered first; see [sender IDs](https://connectmedia.co.ke/sender-id/).

Check everything works:

```bash
php artisan connectmedia:test 0712345678
php artisan connectmedia:balance
```

To customise settings, publish the config file:

```bash
php artisan vendor:publish --tag=connectmedia-config
```

## Sending SMS

```php
use ConnectMedia\Laravel\Facades\ConnectMedia;

ConnectMedia::send('0712345678', 'Your order has shipped.');

// Several recipients, a different sender ID and a scheduled time
ConnectMedia::send(['0712345678', '0733000111'], 'Fees are due on Friday.', [
    'sender'     => 'YourSchool',
    'scheduleAt' => now()->addDay()->setTime(9, 0),
]);

ConnectMedia::balance();
ConnectMedia::history(limit: 20);
ConnectMedia::inbox();
```

You can also type-hint `ConnectMedia\Laravel\ConnectMedia` for dependency injection.

## Notifications

Add `toConnectMedia()` to a notification and put `'connectmedia'` in `via()`:

```php
use ConnectMedia\Laravel\ConnectMediaMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $orderNumber) {}

    public function via($notifiable): array
    {
        return ['connectmedia'];
    }

    public function toConnectMedia($notifiable): ConnectMediaMessage
    {
        return ConnectMediaMessage::create("Order {$this->orderNumber} has shipped.")
            ->from('MYSHOP'); // optional, defaults to CONNECTMEDIA_SENDER
    }
}
```

`toConnectMedia()` may also return a plain string.

Tell Laravel which number to use on your notifiable model:

```php
public function routeNotificationForConnectmedia($notification): ?string
{
    return $this->phone;
}
```

If you don't define it, the channel uses the model's `phone_number` or `phone` attribute. Notifiables without a number are skipped.

On-demand notifications work too:

```php
use Illuminate\Support\Facades\Notification;

Notification::route('connectmedia', '0712345678')->notify(new OrderShipped('A1023'));
```

Implement `ShouldQueue` (as above) to send large batches through your queue workers.

## Errors

API errors throw `ConnectMedia\Sms\ConnectMediaException` with `$e->apiCode` and `$e->apiMessage`, for example `100` invalid API key, `101` insufficient balance, `104` invalid recipients. A missing API key throws a `RuntimeException` telling you to set `CONNECTMEDIA_API_KEY`.

## Testing

```bash
composer install
composer test
```

## Links

- [Connect Media developer docs](https://connectmedia.co.ke/developers/)
- [Bulk SMS API for Kenya](https://connectmedia.co.ke/bulk-sms-api-kenya/)
- [Pricing](https://connectmedia.co.ke/pricing/): no minimum top-up, credit never expires
- Other SDKs: [PHP](https://github.com/connectmedia-ke/connectmedia-php), [Python](https://github.com/connectmedia-ke/connectmedia-python), [Node.js](https://github.com/connectmedia-ke/connectmedia-node)

## License

MIT. See [LICENSE](LICENSE).
