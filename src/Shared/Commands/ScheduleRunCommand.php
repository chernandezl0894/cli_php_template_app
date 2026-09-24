<?php

declare(strict_types=1);

namespace App\Shared\Commands;

use Core\Kernel;
use Core\Scheduler;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'schedule:run',
    description: 'Ejecuta las tareas programadas por el Scheduler.'
)]
final class ScheduleRunCommand extends Command
{
    protected static $defaultName = 'schedule:run';

    public function __construct(
        private Scheduler $scheduler,
        private Kernel $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>⏱️ Ejecutando ciclo del Scheduler...</info>');

        $app = $this->getApplication();

        $this->kernel->schedule($this->scheduler, $app);

        $this->scheduler->run();

        $output->writeln('<info>✅ Ciclo finalizado.</info>');

        return Command::SUCCESS;
    }
}
