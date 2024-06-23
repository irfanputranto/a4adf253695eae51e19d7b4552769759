<?php

namespace App\Migrations;

require __DIR__ . '/../Config/Bootstrap.php';
require __DIR__ . '/../Database/Database.php';


use App\Config\Bootstrap;
use App\Database\Database;
use PDO;

class CreateOAuthTables {
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS oauth_clients (
                client_id TEXT NOT NULL,
                client_secret TEXT,
                redirect_uri TEXT,
                grant_types TEXT,
                scope TEXT,
                user_id TEXT
            )",
            "CREATE TABLE IF NOT EXISTS oauth_access_tokens (
                access_token TEXT NULL,
                google_id TEXT NULL,
                expires TEXT NULL,
                scope TEXT
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

$migrate = new CreateOAuthTables($pdo);
$migrate->up();