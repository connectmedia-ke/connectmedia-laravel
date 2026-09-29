<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel\Tests;

use ConnectMedia\Laravel\ConnectMedia as ConnectMediaService;
use ConnectMedia\Laravel\ConnectMediaChannel;
use ConnectMedia\Laravel\ConnectMediaMessage;
use ConnectMedia\Laravel\Facades\ConnectMedia;
use ConnectMedia\Sms\ConnectMediaException;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ConnectMediaTest extends TestCase
{
    public function test_facade_sends_with_default_sender_and_normalised_number(): void
    {
        ConnectMedia::send('0712 345 678', 'Hello');

        $this->assertSame('send', $this->requests[0]['action']);
        $this->assertSame('254712345678', $this->requests[0]['to']);
        $this->assertSame('TESTCO', $this->requests[0]['sender']);
    }

    public function test_explicit_sender_overrides_default(): void
    {
        ConnectMedia::send(['0712345678', '+254733000111'], 'Hi', ['sender' => 'OTHER']);

        $this->assertSame('254712345678,254733000111', $this->requests[0]['to']);
        $this->assertSame('OTHER', $this->requests[0]['sender']);
    }

    public function test_api_error_is_raised(): void
    {
        $this->response = ['code' => '101', 'message' => 'Insufficient balance'];

        $this->expectException(ConnectMediaException::class);
        ConnectMedia::send('0712345678', 'Hi');
    }

    public function test_notification_uses_route_method(): void
    {
        (new RoutedUser())->notify(new OrderShipped());

        $this->assertSame('254700000001', $this->requests[0]['to']);
        $this->assertSame('Order #42 has shipped.', $this->requests[0]['message']);
        $this->assertSame('MYSHOP', $this->requests[0]['sender']);
    }

    public function test_notification_falls_back_to_phone_number_attribute_and_string_message(): void
    {
        $user = new PhoneUser();
        $user->phone_number = '0711000222';
        $user->notify(new PlainText());

        $this->assertSame('254711000222', $this->requests[0]['to']);
        $this->assertSame('Plain text', $this->requests[0]['message']);
        $this->assertSame('TESTCO', $this->requests[0]['sender']);
    }

    public function test_on_demand_notification(): void
    {
        NotificationFacade::route('connectmedia', '0799000111')->notify(new PlainText());

        $this->assertSame('254799000111', $this->requests[0]['to']);
    }

    public function test_notifiable_without_number_sends_nothing(): void
    {
        (new PhoneUser())->notify(new PlainText());

        $this->assertSame([], $this->requests);
    }

    public function test_channel_class_name_works_in_via(): void
    {
        $channel = $this->app->make(ConnectMediaChannel::class);
        $channel->send((new AnonymousNotifiable())->route(ConnectMediaChannel::class, '0700111222'), new PlainText());

        $this->assertSame('254700111222', $this->requests[0]['to']);
    }

    public function test_schedule_is_passed_through(): void
    {
        $this->app->make(ConnectMediaService::class)->send('0712345678', 'Later', [
            'scheduleAt' => new \DateTimeImmutable('2026-12-01 09:00:00'),
        ]);

        $this->assertSame(1, $this->requests[0]['schedule']);
        $this->assertSame('2026-12-01 09:00:00', $this->requests[0]['schedule_datetime']);
    }

    public function test_test_command(): void
    {
        $this->artisan('connectmedia:test', ['number' => '0712345678'])
            ->expectsOutputToContain('Test SMS sent')
            ->assertSuccessful();

        $this->assertSame('254712345678', $this->requests[0]['to']);
    }

    public function test_test_command_reports_api_error(): void
    {
        $this->response = ['code' => '100', 'message' => 'Invalid API key'];

        $this->artisan('connectmedia:test', ['number' => '0712345678'])
            ->expectsOutputToContain('Invalid API key')
            ->assertFailed();
    }

    public function test_missing_api_key_gives_clear_error(): void
    {
        $this->app['config']->set('connectmedia.api_key', null);
        (new \ConnectMedia\Laravel\ConnectMediaServiceProvider($this->app))->register();
        $this->app->forgetInstance(\ConnectMedia\Sms\Client::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CONNECTMEDIA_API_KEY');
        $this->app->make(\ConnectMedia\Sms\Client::class);
    }
}

class RoutedUser
{
    use Notifiable;

    public function routeNotificationForConnectmedia(): string
    {
        return '0700000001';
    }
}

class PhoneUser
{
    use Notifiable;

    public ?string $phone_number = null;
}

class OrderShipped extends Notification
{
    public function via($notifiable): array
    {
        return ['connectmedia'];
    }

    public function toConnectMedia($notifiable): ConnectMediaMessage
    {
        return ConnectMediaMessage::create('Order #42 has shipped.')->from('MYSHOP');
    }
}

class PlainText extends Notification
{
    public function via($notifiable): array
    {
        return ['connectmedia'];
    }

    public function toConnectMedia($notifiable): string
    {
        return 'Plain text';
    }
}
