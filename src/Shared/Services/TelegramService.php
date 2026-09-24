<?php

declare(strict_types=1);

namespace App\Shared\Services;

use Core\Config;
use Core\LoggerService;

final class TelegramService
{
    private ?string $botToken;
    private ?string $chatId;

    public function __construct(
        private Config $config,
        private LoggerService $logger
    ) {
        $this->botToken = $this->config->get('telegram.bot_token');
        $this->chatId = $this->config->get('telegram.chat_id');
    }

    public function send(string $message): bool
    {
        if (!$this->botToken || !$this->chatId) {
            $this->logger->warning('Telegram credentials not configured.');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $data = [
            'chat_id'    => $this->chatId,
            'text'       => $message,
            'parse_mode' => 'HTML',
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $this->logger->error('Failed to send Telegram notification.');
            return false;
        }

        $this->logger->info('Telegram notification sent successfully.');
        return true;
    }
}
