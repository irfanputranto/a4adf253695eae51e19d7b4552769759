<?php

namespace App\OAuth;

require __DIR__ . '/../../vendor/autoload.php';

use OAuth2\GrantType\ClientCredentials;
use OAuth2\Server;
use OAuth2\Storage\Pdo as OAuthPdo;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuth2Server
{
    private $server;
    private $provider;

    public function __construct($pdo)
    {
        $storage = new OAuthPdo($pdo);

        $this->server = new Server($storage);
        $this->server->addGrantType(new ClientCredentials($storage));

        $this->provider = new GenericProvider([
            'clientId'                 => $_ENV['OAUTH2_CLIENT_ID'],
            'clientSecret'             => $_ENV['OAUTH2_CLIENT_SECRET'],
            'redirectUri'              => $_ENV['OAUTH2_REDIRECT_URI'],
            'urlAuthorize'             => 'https://provider.com/oauth2/authorize',
            'urlAccessToken'           => 'https://provider.com/oauth2/token',
            'urlResourceOwnerDetails'  => 'https://provider.com/oauth2/resource'
        ]);
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getAuthorizationUrl() {
        return $this->provider->getAuthorizationUrl();
    }

    public function getAccessToken($code) {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
    }

    public function getResourceOwner($accessToken) {
        return $this->provider->getResourceOwner($accessToken);
    }
}