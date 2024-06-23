<?php

namespace App\Controllers;

use App\Database\Database;

class UserControllers {
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getUserById($id) {
        $stmt = $this->db->getPDO()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}