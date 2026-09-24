<?php

declare(strict_types=1);

return [
    'bot_token' => $_ENV['TELEGRAM_BOT_TOKEN'] ?? null,
    'chat_id'   => $_ENV['TELEGRAM_CHAT_ID'] ?? null,
];
