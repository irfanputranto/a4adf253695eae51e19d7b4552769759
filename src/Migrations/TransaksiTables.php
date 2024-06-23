<?php

namespace App\Migrations;

require __DIR__ . '/../Config/Bootstrap.php';
require __DIR__ . '/../Database/Database.php';

use App\Config\Bootstrap;
use App\Database\Database;
use PDO;

class TransaksiTables {
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS transaksi (
                id SERIAL PRIMARY KEY,
                nama_barang VARCHAR(255) NOT NULL,
                qty INT NOT NULL,
                harga INT NOT NULL,
                total INT NOT NULL,
                google_id TEXT NULL
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

$migrate = new TransaksiTables($pdo);
$migrate->up();