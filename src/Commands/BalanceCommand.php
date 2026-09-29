<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel\Commands;

use ConnectMedia\Laravel\ConnectMedia;
use ConnectMedia\Sms\ConnectMediaException;
use Illuminate\Console\Command;

class BalanceCommand extends Command
{
    protected $signature = 'connectmedia:balance';

    protected $description = 'Show your Connect Media SMS balance';

    public function handle(ConnectMedia $sms): int
    {
        try {
            $data = $sms->balance();
        } catch (ConnectMediaException $e) {
            $this->error("Connect Media error {$e->apiCode}: {$e->apiMessage}");

            return self::FAILURE;
        }

        $this->line(json_encode($data['data'] ?? $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
