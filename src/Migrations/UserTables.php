<?php

namespace App\Migrations;

require __DIR__ . '/../Config/Bootstrap.php';
require __DIR__ . '/../Database/Database.php';

use App\Config\Bootstrap;
use App\Database\Database;
use PDO;

class UserTables {
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NULL,
                name VARCHAR(255) NULL,
                google_id VARCHAR(255) NULL,
                email_verified INT NULL
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

$migrate = new UserTables($pdo);
$migrate->up();