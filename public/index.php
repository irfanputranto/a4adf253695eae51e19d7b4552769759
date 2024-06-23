<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

require __DIR__ . '/../src/Config/Bootstrap.php';

use App\Config\Bootstrap;
use App\Controllers\AuthControllers;
use App\Controllers\TransaksiControllers;
use App\Database\Database;
use App\Middleware\OAuthMiddleware;
use GuzzleHttp\Psr7\Request;

header('Content-Type: application/json');

Bootstrap::loadEnv();
require __DIR__ . '/../src/Config/Oauth2Client.php';
require __DIR__ . '/../src/Middleware/OAuthMiddleware.php';

$db = new Database();
$pdo = $db->getPDO();

$authController = new AuthControllers();
$transaksiController = new TransaksiControllers();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$authorizationHeader = (isset($_SERVER['HTTP_AUTHORIZATION'])) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/register' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $pass = $data['password'];

    try {
        $authController->register($email, $pass);
        echo json_encode(['message' => 'Registrasi berhasil, silakan cek email anda']);
    } catch (\Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

if ($path === '/login' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $password = $data['password'];

    $user = $authController->login($email, $password);
    if ($user) {
        echo json_encode(['message' => 'Login berhasil', 'user' => $user]);
    } else {
        echo json_encode(['error' => 'Email atau password salah']);
    }
    exit;
}

if ($path === '/oauth2' && $requestMethod === 'GET') {
    $oauthClient = $provider;
    $autUrl = $oauthClient->getAuthorizationUrl();
    $_SESSION['oauthState'] = $oauthClient->getState();
    
    echo json_encode([
        'urlOauth' => $autUrl,
    ]);
    exit;
}

if ($path === '/callback' && $requestMethod === 'GET') {
    if (!isset($_GET['code'])) {
        echo json_encode(['error' => 'Code parameter missing']);
        exit;
    }

    try {
        $token = $provider->getAccessToken('authorization_code',  [
            'code' => $_GET['code']
        ]);

        $ownerDetails = $provider->getResourceOwner($token);
        $user = $ownerDetails->toArray();

        if ($user) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE google_id = ?');
            $stmt->execute([$user['sub']]);
            $userFirst = $stmt->fetch();
            $_SESSION['user'] = $userFirst;
            
            if ($userFirst) {
                $stmt = $pdo->prepare('INSERT INTO oauth_access_tokens (access_token, google_id, expires) VALUES (?, ?, ?)');
                $stmt->execute([$token->getToken(), $user['sub'], $token->getExpires()]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (email, name, google_id, email_verified) VALUES (?, ?, ?, ?)');
                $stmt->execute([$user['email'], $user['name'], $user['sub'], $user['email_verified']]);

                $oat = $pdo->prepare('INSERT INTO oauth_access_tokens (access_token, google_id, expires) VALUES (?, ?, ?)');
                $oat->execute([$token->getToken(), $user['sub'], $token->getExpires()]);
            }
        }

        echo json_encode([
        'message' => 'Login berhasil dengan OAuth2',
        'token' => $token,
        'user' => $user
        ]);
        exit;
    } catch (Exception $e) {
        exit('Failed to get user details: ' . $e->getMessage());
    }
}

if ($path === '/transaksi' && $requestMethod === 'GET') {
    if ($authorizationHeader && strpos($authorizationHeader, 'Bearer ') !== false) {
        $request = getallheaders();
        $middleware = new OAuthMiddleware();
        $middlewares = $middleware->handle($request);

        if (empty($middlewares['error'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($middlewares['error'] === true) {
            $data = $transaksiController->index();
            echo json_encode(['status' => 'Ok', 'data' => $data]);
            exit;
        } else {
            http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

if ($path === '/transaksi/create' && $requestMethod === 'POST') {
    if ($authorizationHeader && strpos($authorizationHeader, 'Bearer ') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $nama_barang = $data['nama_barang'];
        $qty = $data['qty'];
        $harga = $data['harga'];

        $request = getallheaders();
        $middleware = new OAuthMiddleware();
        $middlewares = $middleware->handle($request);

        if (empty($middlewares['error'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($middlewares['error'] === true) {
            $data = $transaksiController->insert($nama_barang, $qty, $harga);
            echo json_encode(['status' => 'Ok', 'data' => $data]);
            exit;
        } else {
            http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$url_components = explode('/', $path);
$id = isset($url_components[2]) ? $url_components[2] : null;

if ($path === '/transaksi/' . $id && $requestMethod === 'GET') {
    if ($authorizationHeader && strpos($authorizationHeader, 'Bearer ') !== false) {

        $request = getallheaders();
        $middleware = new OAuthMiddleware();
        $middlewares = $middleware->handle($request);

        if (empty($middlewares['error'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($middlewares['error'] === true) {
            $data = $transaksiController->first($id);
            echo json_encode(['status' => 'Ok', 'data' => $data]);
            exit;
        } else {
            http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

if ($path === '/transaksi/' . $id && $requestMethod === 'PUT') {
    if ($authorizationHeader && strpos($authorizationHeader, 'Bearer ') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $nama_barang = $data['nama_barang'];
        $qty = $data['qty'];
        $harga = $data['harga'];

        $request = getallheaders();
        $middleware = new OAuthMiddleware();
        $middlewares = $middleware->handle($request);

        if (empty($middlewares['error'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($middlewares['error'] === true) {
            $data = $transaksiController->update($id, $nama_barang, $qty, $harga);
            echo json_encode(['status' => 'Ok', 'data' => $data]);
            exit;
        } else {
            http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

if ($path === '/transaksi/' . $id && $requestMethod === 'DELETE') {
    if ($authorizationHeader && strpos($authorizationHeader, 'Bearer ') !== false) {

        $request = getallheaders();
        $middleware = new OAuthMiddleware();
        $middlewares = $middleware->handle($request);

        if (empty($middlewares['error'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($middlewares['error'] === true) {
            $data = $transaksiController->delete($id);
            echo json_encode(['status' => 'Ok', 'data' => $data]);
            exit;
        } else {
            http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

echo json_encode(['error' => 'Invalid endpoint']);