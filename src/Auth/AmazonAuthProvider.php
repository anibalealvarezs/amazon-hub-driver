<?php

namespace Anibalealvarezs\AmazonHubDriver\Auth;

use Anibalealvarezs\ApiDriverCore\Interfaces\AuthProviderInterface;

class AmazonAuthProvider implements AuthProviderInterface
{
    private array $credentials = [];
    private ?string $tokenPath;
    private array $config = [];

    public function __construct(?string $tokenPath = null, array $config = [])
    {
        $this->config = $config;
        $projectDir = dirname(__DIR__, 2);
        
        // Priority: Passed arg -> Config -> ENV -> Default
        $this->tokenPath = $tokenPath 
            ?? $config['amazon']['token_path'] 
            ?? $_ENV['AMAZON_TOKEN_PATH'] 
            ?? $projectDir . '/storage/tokens/amazon_tokens.json';

        $this->loadCredentials();

        // Fallback to config if tokens are not loaded or missing fields
        if (empty($this->credentials['client_id'])) {
            $this->credentials['client_id'] = $config['amazon']['client_id'] 
                ?? $_ENV['AMAZON_CLIENT_ID'] 
                ?? '';
        }

        if (empty($this->credentials['client_secret'])) {
            $this->credentials['client_secret'] = $config['amazon']['client_secret'] 
                ?? $_ENV['AMAZON_CLIENT_SECRET'] 
                ?? '';
        }

        if (empty($this->credentials['refresh_token'])) {
            $this->credentials['refresh_token'] = $config['amazon']['refresh_token'] 
                ?? $_ENV['AMAZON_REFRESH_TOKEN'] 
                ?? '';
        }
    }

    private function loadCredentials(): void
    {
        if ($this->tokenPath && file_exists($this->tokenPath)) {
            $tokens = json_decode(file_get_contents($this->tokenPath), true) ?? [];
            $this->credentials = $tokens['amazon_auth'] ?? [];
        }
    }

    public function getAccessToken(): string
    {
        if ($this->isExpired()) {
            $this->refresh();
        }
        return $this->credentials['access_token'] ?? '';
    }

    public function getUserId(): string
    {
        return $this->credentials['user_id'] ?? '';
    }

    public function isValid(): bool
    {
        return !empty($this->credentials['access_token']) || !empty($this->credentials['refresh_token']);
    }

    public function isExpired(): bool
    {
        if (empty($this->credentials['expires_at'])) {
            return true;
        }
        return strtotime($this->credentials['expires_at']) <= (time() + 60);
    }

    public function refresh(): bool
    {
        $refreshToken = $this->credentials['refresh_token'] ?? null;
        if (!$refreshToken) {
            return false;
        }

        $url = "https://api.amazon.com/auth/o2/token";
        $params = [
            'client_id' => $this->credentials['client_id'] ?? '',
            'client_secret' => $this->credentials['client_secret'] ?? '',
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $this->credentials['access_token'] = $data['access_token'];
            $this->credentials['expires_at'] = date('Y-m-d H:i:s', time() + ($data['expires_in'] ?? 3600));
            $this->saveCredentials();
            return true;
        }

        return false;
    }

    public function getScopes(): array
    {
        return $this->credentials['scopes'] ?? $this->config['amazon']['scopes'] ?? [];
    }

    public function setAccessToken(string $token): void
    {
        $this->credentials['access_token'] = $token;
        $this->saveCredentials();
    }

    public function setAuthProvider(AuthProviderInterface $provider): void {}

    public function updateCredentials(array $credentials): void
    {
        $this->credentials = array_merge($this->credentials, $credentials);
        $this->saveCredentials();
    }

    private function saveCredentials(): void
    {
        if (!$this->tokenPath) return;

        $tokens = file_exists($this->tokenPath) ? (json_decode(file_get_contents($this->tokenPath), true) ?? []) : [];
        $tokens['amazon_auth'] = array_merge($tokens['amazon_auth'] ?? [], $this->credentials);
        $tokens['amazon_auth']['updated_at'] = date('Y-m-d H:i:s');
        
        file_put_contents($this->tokenPath, json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
