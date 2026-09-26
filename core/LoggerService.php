<?php

declare(strict_types=1);

namespace Core;

use DateTimeImmutable;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class LoggerService
{
    private Logger $appLogger;

    public function __construct(
        private Config $config,
    ) {
        $path = $this->config->get('logging.path');
        $days = $this->config->get('logging.days', 14);
        $environment = $this->config->get('app.env', 'production');

        $dateFormat = 'Y-m-d H:i:s';
        $output = "[%datetime%][%level_name%] - %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        $appHandler = new RotatingFileHandler("{$path}/app.log", $days, Level::Info);
        $appHandler->setFormatter($formatter);

        $this->appLogger = new Logger('app');
        $this->appLogger->pushHandler($appHandler);

        if ($environment === 'development') {
            $consoleHandler = new StreamHandler('php://stdout', Level::Debug);
            $consoleHandler->setFormatter($formatter);
            $this->appLogger->pushHandler($consoleHandler);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->appLogger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->appLogger->error($message, $context);
    }

    public function cron(string $message, array $context = []): void
    {
        $this->log('CRON', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->appLogger->warning($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->appLogger->critical($message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $path = $this->config->get('logging.path');
        $date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context) : '[]';

        $formattedMessage = sprintf(
            "[%s][%s] - %s %s\n",
            $date,
            $level, // 👈 Imprimirá [CRON], [INFO] o [CRITICAL]
            $message,
            $contextJson
        );

        file_put_contents("{$path}/app.log", $formattedMessage, FILE_APPEND);
    }
}
