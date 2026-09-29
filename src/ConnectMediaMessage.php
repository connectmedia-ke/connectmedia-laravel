<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel;

/**
 * SMS returned from a notification's toConnectMedia() method.
 *
 *     return ConnectMediaMessage::create('Your order has shipped.')->from('MYSHOP');
 */
class ConnectMediaMessage
{
    public ?string $sender = null;

    public ?\DateTimeInterface $scheduleAt = null;

    /** @var string|string[]|null Overrides the notifiable's routed number when set. */
    public string|array|null $to = null;

    public function __construct(public string $content = '')
    {
    }

    public static function create(string $content = ''): static
    {
        return new static($content);
    }

    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /** Sender ID, 11 characters or fewer. */
    public function from(string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function scheduleAt(\DateTimeInterface $when): static
    {
        $this->scheduleAt = $when;

        return $this;
    }

    /** @param string|string[] $to */
    public function to(string|array $to): static
    {
        $this->to = $to;

        return $this;
    }

    /** @return array{sender?: string, scheduleAt?: \DateTimeInterface} */
    public function options(): array
    {
        return array_filter(['sender' => $this->sender, 'scheduleAt' => $this->scheduleAt], fn ($v) => $v !== null && $v !== '');
    }
}
