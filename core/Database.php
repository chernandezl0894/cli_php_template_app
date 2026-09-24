<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;

final class Database
{
    private ?PDO $connection = null;

    public function __construct(
        private Config $config
    ) {}

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $driver = $this->config->get('database.driver', 'sqlite');
            $database = $this->config->get('database.database');
            $options = $this->config->get('database.options', []);

            $dir = dirname($database);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $dsn = "{$driver}:{$database}";
            $this->connection = new PDO($dsn, null, null, $options);

            $this->connection->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                status TEXT NOT NULL DEFAULT 'inbox',
                due_date DATETIME NULL,
                reminder_at DATETIME NULL,
                reminder_sent INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        }

        return $this->connection;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
