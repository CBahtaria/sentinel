<?php
// Sentinel database config — all credentials from environment.
// Never hardcode credentials here; use Docker secrets or .env.
return [
    'host'     => $_ENV['SENTINEL_DB_HOST'] ?? getenv('SENTINEL_DB_HOST') ?? 'sentinel-mysql',
    'username' => $_ENV['SENTINEL_DB_USER'] ?? getenv('SENTINEL_DB_USER') ?? 'sentinel',
    'password' => $_ENV['SENTINEL_DB_PASS'] ?? getenv('SENTINEL_DB_PASS') ?? '',
    'database' => $_ENV['SENTINEL_DB_NAME'] ?? getenv('SENTINEL_DB_NAME') ?? 'uedf_sentinel',
    'port'     => (int) ($_ENV['SENTINEL_DB_PORT'] ?? getenv('SENTINEL_DB_PORT') ?? 3306),
    'charset'  => 'utf8mb4',
];
