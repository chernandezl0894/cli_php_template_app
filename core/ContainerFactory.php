<?php

declare(strict_types=1);

namespace Core;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use function DI\create;

final class ContainerFactory
{
    public static function create(): ContainerInterface
    {
        $builder = new ContainerBuilder();

        $builder->useAutowiring(true);

        // NOTE. Si quisiéramos definir alias para interfaces o configuraciones personalizadas:
        $builder->addDefinitions([
            Config::class => create(Config::class)->constructor(BASE_PATH . '/config'),
            Database::class => \DI\autowire(Database::class),
        ]);

        if (getenv('APP_ENV') === 'production') {
            $builder->enableCompilation(BASE_PATH . '/storage/cache');
        }

        return $builder->build();
    }
}
