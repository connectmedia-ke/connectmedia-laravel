<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel\Commands;

use ConnectMedia\Laravel\ConnectMedia;
use ConnectMedia\Sms\ConnectMediaException;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'connectmedia:test
        {number : Phone number to send the test SMS to, e.g. 0712345678}
        {--message=Test message from your Laravel app via Connect Media. : Message text}
        {--sender= : Sender ID to use instead of the configured default}';

    protected $description = 'Send a test SMS through Connect Media to check your configuration';

    public function handle(ConnectMedia $sms): int
    {
        try {
            $options = $this->option('sender') ? ['sender' => (string) $this->option('sender')] : [];
            $sms->send((string) $this->argument('number'), (string) $this->option('message'), $options);
        } catch (ConnectMediaException $e) {
            $this->error("Connect Media error {$e->apiCode}: {$e->apiMessage}");

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Test SMS sent to ' . $this->argument('number') . '.');

        return self::SUCCESS;
    }
}
