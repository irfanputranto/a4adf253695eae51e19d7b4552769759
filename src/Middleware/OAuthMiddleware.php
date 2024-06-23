<?php

namespace App\Middleware;

use App\Database\Database;
use PDO;

class OAuthMiddleware {
    protected $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getPDO();
    }

    public function handle($request)
    {
        $token = $request['Authorization'];

        if (empty($token)) {
            return json_encode(['error' => 'Unauthorized']);
        }

        $token = trim(str_replace('Bearer', '', $token));

        $stmt = $this->pdo->prepare('SELECT * FROM oauth_access_tokens WHERE access_token = ?');
        $stmt->execute([$token]);
        $accessToken = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$accessToken) {
            return json_encode(['error' => 'Invalid token']);
        }

        $_SESSION['googleId'] = $accessToken['google_id'];
        $exp = (isset($accessToken['expires']))? date("Y-m-d H:i:s", $accessToken['expires']) : null;
        $dateNow = date("Y-m-d H:i:s");

        if ($dateNow > $exp) {
            $stmt = $this->pdo->prepare('DELETE FROM oauth_access_tokens WHERE access_token = ?');
            $stmt->execute([$token]);
        }

        return ['error' => true];
    }
}