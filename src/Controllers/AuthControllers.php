<?php

namespace App\Controllers;

use App\Database\Database;
use App\Service\MailService;
use PHPMailer\PHPMailer\PHPMailer;

class AuthControllers {
    private $db;
    private $mailer;

    public function __construct()
    {
        $this->db = new Database();
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['EMAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['EMAIL_USERNAME'];
        $this->mailer->Password = $_ENV['EMAIL_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['EMAIL_PORT'];
        $this->mailer->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME']);
    }

    public function register($email, $password) {
        $stmt = $this->db->getPDO()->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);

        // kirim email
        $mailService = new MailService();
        $mailService->queueEmail($email, 'New Registration User', 'Selamat anda sudah terdaftar di aplikasi email API.');
    }

    public function login($email, $password) {
        $stmt = $this->db->getPDO()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return null;
        }
    }
}