<?php

declare(strict_types=1);

namespace App\Shared\Commands;

use Core\Database;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate',
    description: 'Run the pending SQL migrations to update the database schema.'
)]
final class MigrateCommand extends Command
{
    public function __construct(
        private Database $db
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🛠️ SQL Migrations System');

        // 1. Crear tabla de control si no existe
        $this->db->query("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 2. Obtener migraciones ya ejecutadas
        $stmt = $this->db->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // 3. Leer archivos .sql en database/migrations/
        $migrationsDir = BASE_PATH . '/database/migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0777, true);
        }

        $files = glob($migrationsDir . '/*.sql');
        sort($files); // Asegura orden cronológico

        $pending = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, $executed, true)) {
                continue; // Ya fue ejecutada
            }

            $io->text("Ejecutando: <comment>{$filename}</comment>...");

            $sql = file_get_contents($file);

            // 4. Ejecutar migración dentro de una transacción 🛡️
            $pdo = $this->db->getConnection();
            try {
                $pdo->beginTransaction();
                $pdo->exec($sql);

                // Registrar en la tabla de control
                $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                $stmt->execute(['migration' => $filename]);

                $pdo->commit();
                $io->success("✅ Completed: {$filename}");
                $pending++;
            } catch (\Throwable $e) {
                $pdo->rollBack();
                $io->error("❌ Error at {$filename}: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        if ($pending === 0) {
            $io->info('There are no pending migrations. The database is up to date.');
        }

        return Command::SUCCESS;
    }
}
