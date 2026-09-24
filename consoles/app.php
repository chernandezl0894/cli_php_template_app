<?php

declare(strict_types=1);

require __DIR__ . "/../bootstrap.php";

use App\Greet\Commands\GreetCommand;
use App\Heartbeat\Commands\HeartbeatCommand;
use App\Shared\Commands\ScheduleRunCommand;
use Core\ContainerFactory;
use Symfony\Component\Console\Application;

$container = ContainerFactory::create();

$application = new Application('Mi Microframework CLI', '1.0.0');

$application->addCommand($container->get(GreetCommand::class));
$application->addCommand($container->get(HeartbeatCommand::class));
$application->addCommand($container->get(ScheduleRunCommand::class));

$application->addCommand($container->get(\App\Tasks\Commands\CreateTaskCommand::class));
$application->addCommand($container->get(\App\Tasks\Commands\ListTasksCommand::class));
$application->addCommand($container->get(\App\Tasks\Commands\ProcessRemindersCommand::class));

$application->run();
