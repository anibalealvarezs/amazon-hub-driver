<?php

namespace Anibalealvarezs\AmazonHubDriver\Auth;

use Anibalealvarezs\ApiSkeleton\Interfaces\AuthProviderInterface;

class AmazonAuthProvider implements AuthProviderInterface
{
    private array $credentials = [];

    public function __construct(?array $config = [])
    {
        $this->credentials = [
            'access_token' => $config['amazon_api_key'] ?? $_ENV['AMAZON_API_KEY'] ?? '',
        ];
    }

    public function getAccessToken(): string
    {
        return $this->credentials['access_token'] ?? '';
    }

    public function isValid(): bool
    {
        return !empty($this->getAccessToken());
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function refresh(): bool
    {
        return false;
    }

    public function getScopes(): array
    {
        return [];
    }

    public function setAuthProvider(AuthProviderInterface $provider): void {}
}
