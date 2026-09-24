<?php

declare(strict_types=1);

namespace App\Greet\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:greet',
    description: 'Show custom greet.'
)]
final class GreetCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Persons name to greet', 'Guess')
            ->addOption('uppercase', 'u', InputOption::VALUE_NONE, 'If activated, it prints the greeting in uppercase.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $uppercase = $input->getOption('uppercase');

        $message = "¡Hello, {$name}! Welcome your first app.";

        if ($uppercase) {
            $message = mb_strtoupper($message);
        }

        $output->writeln("<info>{$message}</info>");

        return Command::SUCCESS; // Retorna 0 indicando éxito
    }
}
