<?php

namespace App\Workers;

require __DIR__ . '/../Config/Bootstrap.php';
require __DIR__ . '/../Database/Database.php';

use App\Config\Bootstrap;
use App\Database\Database;
use App\Service\MailService;
use PDO;

class EmailWorkers
{
    private $pdo;

    public function __construct()
    {
        $databases = new Database();
        $this->pdo = $databases->getPDO();

    }

    public function process()
    {
        $stmt = $this->pdo->prepare('SELECT * FROM emails WHERE status = ?');
        $stmt->execute(['pending']);
        $pendingEmails = $stmt->fetchAll(PDO::FETCH_OBJ);

        $mailService = new MailService();
        echo "Worker running..." . PHP_EOL;

        foreach ($pendingEmails as $email) {
            try {
                $mailService->send($email->recipient, $email->subject, $email->body);
                $stmtUpdate = $this->pdo->prepare('UPDATE emails SET status = ? WHERE id = ?');
                $stmtUpdate->execute(['sent', $email->id]);
                echo "Email processed successfully." . PHP_EOL;
            } catch (\Exception $e) {
                $stmtUpdate = $this->pdo->prepare('UPDATE emails SET status = ? WHERE id = ?');
                $stmtUpdate->execute(['failed', $email->id]);
                echo "Email processed failed." . PHP_EOL;
            }
        }

    }
}

Bootstrap::loadEnv();

$worker = new EmailWorkers();
$worker->process();