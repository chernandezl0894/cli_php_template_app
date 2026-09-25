<?php

declare(strict_types=1);

namespace App\Tasks\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:shell',
    description: 'Starts interactive mode to manage tasks.'
)]
final class ShellCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Interactive Task Console');

        while (true) {
            // 📋 Menú principal de opciones
            $choice = $io->choice('What do you want to do?', [
                'create' => 'Create a new task',
                'list'   => 'List tasks',
                'exit'   => 'Exit'
            ], 'create');

            if ($choice === 'exit') {
                $io->info('See you later! 👋');
                break;
            }

            // 🔀 Ejecución de acciones según la selección
            match ($choice) {
                'create' => $this->handleCreateTask($io, $output),
                'list'   => $this->handleListTasks($output),
                default  => null,
            };

            $io->newLine();
        }

        return Command::SUCCESS;
    }

    private function handleCreateTask(SymfonyStyle $io, OutputInterface $output): void
    {
        $title = $io->ask('Task Title');
        if ($title === null) {
            $io->warning('Operation cancelled.');
            return;
        }

        $desc = $io->ask('Description (optional)');

        $command = $this->getApplication()?->find('app:task:create');
        if ($command === null) {
            $io->error('The app:task:create command is not available.');
            return;
        }

        $patternChoice = $io->choice(
            question: 'How often do you want the reminder?',
            choices: [
                'once' => 'Once',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
            ],
            default: 'once'
        );

        $reminderStr = $io->ask('Reminder date and time (e.g., "2026-10-01 10:00:00" or "+1 day")');

        $arguments = [
            'title'  => $title,
            '--desc' => $desc,
            '--pattern' => $patternChoice,
            '--reminder' => $reminderStr,
        ];

        if ($patternChoice !== 'once') {
            $arguments['--recurring'] = true;
        }

        $commandInput = new ArrayInput($arguments);
        $command->run($commandInput, $output);
    }

    private function handleListTasks(OutputInterface $output): void
    {
        $command = $this->getApplication()?->find('app:task:list');
        $command?->run(new ArrayInput([]), $output);
    }
}
