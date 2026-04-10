<?php

namespace Anibalealvarezs\AmazonHubDriver\Drivers;

use Anibalealvarezs\ApiDriverCore\Interfaces\SyncDriverInterface;
use Anibalealvarezs\ApiSkeleton\Interfaces\AuthProviderInterface;
use Anibalealvarezs\ApiSkeleton\Traits\HasUpdatableCredentials;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use DateTime;
use Anibalealvarezs\ApiDriverCore\Interfaces\SeederInterface;

class AmazonDriver implements SyncDriverInterface
{

    /**
     * Store credentials for this driver.
     * 
     * @param array $credentials
     * @return void
     */
    public static function storeCredentials(array $credentials): void
    {
        // No implementation needed for this driver
    }

    public static function getCommonConfigKey(): ?string
    {
        return null;
    }
    use HasUpdatableCredentials;

    private ?AuthProviderInterface $authProvider = null;
    private ?LoggerInterface $logger = null;

    public function __construct(?AuthProviderInterface $authProvider = null, ?LoggerInterface $logger = null)
    {
        $this->authProvider = $authProvider;
        $this->logger = $logger;
    }

    public function setAuthProvider(AuthProviderInterface $provider): void
    {
        $this->authProvider = $provider;
    }

    public function getAuthProvider(): ?AuthProviderInterface
    {
        return $this->authProvider;
    }

    public function getChannel(): string
    {
        return 'amazon';
    }

    public function sync(DateTime $startDate, DateTime $endDate, array $config = []): Response
    {
        if ($this->logger) {
            $this->logger->info("AmazonDriver (Modular): No native implementation yet. Sync skipped.");
        }
        
        return new Response(json_encode([
            'status' => 'skipped',
            'message' => 'Amazon modular driver placeholder executed successfully.'
        ]));
    }

    public function getApi(array $config = []): mixed
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getConfigSchema(): array
    {
        return [
            'global' => [
                'enabled' => true,
                'cache_history_range' => '1 year',
                'cache_aggregations' => false,
            ],
            'entity' => [
                'id' => '',
                'name' => '',
                'enabled' => true,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateConfig(array $config): array
    {
        return $config;
    }

    /**
     * @inheritdoc
     */
    public function seedDemoData(SeederInterface $seeder, array $config = []): void
    {
        // Placeholder for future implementation
    }
    public function boot(): void
    {
    }

    public function getAssetPatterns(): array
    {
        return [];
    }
}

