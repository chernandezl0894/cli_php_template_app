<?php

declare(strict_types=1);

namespace App\Heartbeat\Commands;

use Core\LoggerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:heartbeat',
    description: 'Checks the system status and/or sends a heartbeat notification.'
)]
final class HeartbeatCommand extends Command
{
    public function __construct(
        private LoggerService $loggerService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('notify', 't', InputOption::VALUE_NONE, 'If specified, it registers a status notification for Cron.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /*
        * --------------------------------------------------------------------------
        * 1. VERIFICACIONES DE SALUD DEL SISTEMA (Health Checks Futuros)
        * --------------------------------------------------------------------------
        * Aquí se agregan las comprobaciones activas antes de emitir un latido exitoso:
        *
        *  a) Base de Datos (PDO / ORM):
        *     $dbStatus = $this->dbService->ping(); // SELECT 1
        *
        *  b) Memoria y Almacenamiento:
        *     $freeDiskSpace = disk_free_space(BASE_PATH);
        *     if ($freeDiskSpace < 500_000_000) { // < 500MB
        *         $this->loggerService->warning('Espacio en disco bajo.');
        *     }
        *
        *  c) Servicios de Caché o Queues:
        *     $redisStatus = $this->redisService->ping();
        */
        if ($input->getOption('notify')) {
            /*
            * ----------------------------------------------------------------------
            * 2. NOTIFICACIÓN EXTERNA / PROCESAMIENTO CRON
            * ----------------------------------------------------------------------
            * En entornos de producción, esta sección suele encargarse de:
            *
            *  a) "Dead Man's Switch" (Ping HTTP externo):
            *     $this->httpClient->get('https://hc-ping.com/tu-uuid-aqui');
            *     (Si esta petición deja de enviarse, el servicio externo activa alertas).
            *
            *  b) Envío de métricas a sistemas de monitoreo (Datadog / Prometheus / Loki).
            *
            *  c) Notificaciones directas a Slack / Discord si ocurre alguna anomalía.
            */
            $this->loggerService->info('Heartbeat runs successfully.');
            $io->success('Heartbeat notification successfully processed and sent.');
        } else {
            /*
            * ----------------------------------------------------------------------
            * 3. MODO CHEQUEO MANUAL / DIAGNÓSTICO
            * ----------------------------------------------------------------------
            * Uso interactivo en desarrollo o inspecciones por CLI.
            */
            $this->loggerService->info("System running successfully!");
            $io->info('Status check (heartbeat) completed.');
        }

        return Command::SUCCESS;
    }
}
