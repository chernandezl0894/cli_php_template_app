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
