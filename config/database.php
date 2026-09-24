<?php

declare(strict_types=1);

return [
    'driver'   => $_ENV['DB_DRIVER'] ?? 'sqlite',
    'database' => $_ENV['DB_DATABASE'] ?? BASE_PATH . '/database/database.sqlite',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Transforma errores SQL en Excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna arreglos asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Consultas preparadas reales
    ],
];
