<?php

declare(strict_types=1);

$container = require __DIR__ . '/../bootstrap.php';

use App\Greet\Commands\GreetCommand;
use App\Heartbeat\Commands\HeartbeatCommand;
use App\Shared\Commands\MigrateCommand;
use App\Shared\Commands\ScheduleRunCommand;
use App\Tasks\Commands\CreateTaskCommand;
use App\Tasks\Commands\ListTasksCommand;
use App\Tasks\Commands\ProcessRemindersCommand;
use App\Tasks\Commands\ShellCommand;
use App\Tasks\Commands\ShowTaskCommand;
use Symfony\Component\Console\Application;

$application = new Application('Mi Microframework CLI', '1.0.0');

$application->addCommand($container->get(GreetCommand::class));
$application->addCommand($container->get(HeartbeatCommand::class));
$application->addCommand($container->get(CreateTaskCommand::class));
$application->addCommand($container->get(ShellCommand::class));
$application->addCommand($container->get(ShowTaskCommand::class));
$application->addCommand($container->get(ListTasksCommand::class));
$application->addCommand($container->get(ProcessRemindersCommand::class));
$application->addCommand($container->get(ScheduleRunCommand::class));
$application->addCommand($container->get(MigrateCommand::class));

$application->run();
