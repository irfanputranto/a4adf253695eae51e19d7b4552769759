<?php

namespace App\Service;

use App\Database\Database;
use Exception;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class MailService {
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function queueEmail($to, $subject, $body)
    {
        $stmt = $this->db->getPDO()->prepare('INSERT INTO emails (recipient, subject, body, status) VALUES (?, ?, ?, ?)');
        $status = 'pending';
        $stmt->execute([$to, $subject, $body, $status]);

        return $this->db->getPDO()->lastInsertId();
    }

    public function send($to, $subject, $body)
    {
        $mail = new PHPMailer();

        try {
            $data = json_decode($body, true);
            $nmaBrang = str_replace('"', '', json_encode($data['nama_barang'], JSON_PRETTY_PRINT));
            $template = $this->loadTemplate(__DIR__ . '/../Email/email_template.html', [
                'subject' => $subject,
                'nama_barang' => $nmaBrang,
                'qty' => json_encode($data['qty'], JSON_PRETTY_PRINT),
                'harga' => json_encode($data['harga'], JSON_PRETTY_PRINT),
                'total' => json_encode($data['total'], JSON_PRETTY_PRINT),
            ]);
            
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USERNAME'];
            $mail->Password = $_ENV['EMAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['EMAIL_PORT'];
            $mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME']);

            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject  = $subject;
            $mail->Body     = $template;

            $mail->send();
        } catch (Exception $e) {
            throw new \Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    private function loadTemplate($templateFile, $placeholders)
    {
        $template = file_get_contents($templateFile);

        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
}