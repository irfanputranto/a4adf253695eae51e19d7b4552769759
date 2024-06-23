<?php

namespace App\Migrations;

require __DIR__ . '/../Config/Bootstrap.php';
require __DIR__ . '/../Database/Database.php';

use App\Config\Bootstrap;
use App\Database\Database;
use PDO;

class EmailTables {
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS emails (
                id SERIAL PRIMARY KEY,
                recipient VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NULL,
                body TEXT NULL,
                status VARCHAR(255) DEFAULT 'pending',
                user_id INT NULL
            )"
        ];

        foreach ($queries as $query) {
            $this->pdo->exec($query);
        }

        echo "Tables created successfully.\n";
    }
}

Bootstrap::loadEnv();

$db = new Database();
$pdo = $db->getPDO();

$migrate = new EmailTables($pdo);
$migrate->up();