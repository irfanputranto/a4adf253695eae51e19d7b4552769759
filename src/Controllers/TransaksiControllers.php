<?php

namespace App\Controllers;

use App\Database\Database;
use App\Service\MailService;
use PDO;

class TransaksiControllers {
    private $db;

    public function __construct()
    {
        $pdo = new Database;
        $this->db = $pdo->getPDO();
    }

    public function index()
    {
        $stmt = $this->db->prepare('SELECT * FROM transaksi WHERE google_id = ?');
        $stmt->execute([$_SESSION['googleId']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first($id = null)
    {
        if ($id) {
            $stmt = $this->db->prepare('SELECT * FROM transaksi WHERE google_id = ? AND id = ?');
            $stmt->execute([$_SESSION['googleId'], $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return json_encode(['message' => `Transaksi dengan Id {$id} Tidak ada`, 'error' => 'Not Found']);
    }

    public function insert($namaBarang, $qty, $harga) {
        $total = $qty * $harga;
        $stmt = $this->db->prepare('INSERT INTO transaksi (nama_barang, qty, harga, total, google_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$namaBarang, $qty, $harga, $total, $_SESSION['googleId']]);
        $lastIds = $this->db->lastInsertId();
        $stmtSelect = $this->db->prepare('SELECT * FROM transaksi WHERE id = ?');
        $stmtSelect->execute([$lastIds]);
        $newTransaksi = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $user = $this->db->prepare('SELECT * FROM users WHERE google_id = ?');
        $user->execute([$_SESSION['googleId']]);
        $dtUser = $user->fetch(PDO::FETCH_ASSOC);

        if (isset($dtUser['email'])) {
            $mailService = new MailService();
            $mailService->queueEmail($dtUser['email'], 'Transaksi Baru #' . $newTransaksi['nama_barang'], json_encode($newTransaksi, true));
        }

        return $newTransaksi;
    }

    public function update($id, $namaBarang, $qty, $harga) {
        $stmtSelect = $this->db->prepare('SELECT * FROM transaksi WHERE id = ?');
        $stmtSelect->execute([$id]);
        $updateTransaksi = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($updateTransaksi) {
            $total = $qty * $harga;
            $stmt = $this->db->prepare('UPDATE transaksi SET nama_barang = ?, qty = ?, harga = ?, total = ? WHERE id = ?');
            $stmt->execute([$namaBarang, $qty, $harga, $total, $id]);
            
            $user = $this->db->prepare('SELECT * FROM users WHERE google_id = ?');
            $user->execute([$_SESSION['googleId']]);
            $dtUser = $user->fetch(PDO::FETCH_ASSOC);
            
            if (isset($dtUser['email'])) {
                $mailService = new MailService();
                $mailService->queueEmail($dtUser['email'], 'Transaksi Update #' . $updateTransaksi['nama_barang'], json_encode($updateTransaksi, true));
            }
            
            return $updateTransaksi;
        }

        return ['message' => 'Transaksi dengan Id ' . $id . ' Tidak ada', 'error' => 'Not Found'];
    }

    public function delete($id = null)
    {
        if ($id > 0) {
        $stmtCheck = $this->db->prepare('SELECT * FROM transaksi WHERE id = ?');
        $stmtCheck->execute([$id]);
        $data = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $stmt = $this->db->prepare('DELETE FROM transaksi WHERE id = ?');
            $stmt->execute([$id]);
            exit(json_encode($stmt));
            return ['message' => 'Data berhasil dihapus'];
        } else {
            return ['message' => 'Transaksi dengan Id ' . $id . ' Tidak ada', 'error' => 'Not Found'];
        }
    } else {
        return ['error' => 'ID tidak valid'];
    }
    }

}