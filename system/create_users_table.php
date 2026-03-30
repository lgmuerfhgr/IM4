<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';

try {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

    $pdo->exec($sql);
    echo "OK: users table is ready.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR creating users table: " . $e->getMessage() . "\n";
}

