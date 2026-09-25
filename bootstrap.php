<?php

declare(strict_types=1);

use Core\ContainerFactory;
use Core\LoggerService;
use Dotenv\Dotenv;

define("BASE_PATH", __DIR__);

require __DIR__ . "/vendor/autoload.php";

$container = ContainerFactory::create();

mb_internal_encoding('UTF-8');

if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();

    $dotenv->required(['APP_ENV'])->notEmpty();
    $dotenv->required('APP_ENV')->allowedValues(['development', 'staging', 'production']);
}

$timezone = $_ENV['APP_TIMEZONE'] ?? $_SERVER['APP_TIMEZONE'] ?? 'America/Lima';
date_default_timezone_set($timezone);

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception) use ($container): void {
    try {
        $logger = $container->get(LoggerService::class);
        $logger->critical($exception->getMessage(), [
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    } catch (Throwable $e) {
        error_log("Critical failure in Exception Handler: " . $e->getMessage());
    }

    fwrite(STDERR, "[CRITICAL ERROR]: {$exception->getMessage()}\n");
    fwrite(STDERR, "\tAt: {$exception->getFile()}:{$exception->getLine()}\n");
    exit(1);
});

return $container;
