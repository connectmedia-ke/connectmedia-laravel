<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel;

use ConnectMedia\Sms\Client;

/**
 * Laravel-friendly wrapper around the Connect Media PHP client that applies the
 * configured default sender ID.
 *
 *     ConnectMedia::send('0712345678', 'Your order has shipped.');
 */
class ConnectMedia
{
    /** @var \Closure(): Client */
    private \Closure $resolver;

    private ?Client $client = null;

    /** @param \Closure(): Client $resolver */
    public function __construct(\Closure $resolver, private ?string $defaultSender = null)
    {
        $this->resolver = $resolver;
    }

    public function client(): Client
    {
        return $this->client ??= ($this->resolver)();
    }

    /**
     * @param string|string[] $to
     * @param array{sender?: string, scheduleAt?: \DateTimeInterface} $options
     * @return array<string, mixed>
     */
    public function send(string|array $to, string $message, array $options = []): array
    {
        if (empty($options['sender']) && !empty($this->defaultSender)) {
            $options['sender'] = $this->defaultSender;
        }

        return $this->client()->send($to, $message, $options);
    }

    /** @return array<string, mixed> */
    public function balance(): array
    {
        return $this->client()->balance();
    }

    /** @return array<string, mixed> */
    public function history(int $limit = 50, int $offset = 0, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->client()->history($limit, $offset, $startDate, $endDate);
    }

    /** @return array<string, mixed> */
    public function inbox(int $limit = 50): array
    {
        return $this->client()->inbox($limit);
    }
}
