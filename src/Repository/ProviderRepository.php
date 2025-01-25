<?php declare(strict_types=1);

namespace App\Repository;

class ProviderRepository
{
    private const PROVIDER_TOPICS_KEY = 'provider_topics';
    private ?array $cachedData = null;

    public function __construct(private readonly string $filePath)
    {
    }

    public function getProviders(): array
    {
        $decodedData = $this->loadConfigurationFile();
        return $decodedData[self::PROVIDER_TOPICS_KEY] ?? [];
    }

    public function getTopicsByProvider(string $provider): ?string
    {
        $providers = $this->getProviders();
        return $providers[$provider] ?? null;
    }

    private function loadConfigurationFile(): array
    {
        if ($this->cachedData !== null) {
            return $this->cachedData;
        }

        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("The configuration file does not exist: {$this->filePath}");
        }

        $fileContents = file_get_contents($this->filePath);
        if ($fileContents === false || trim($fileContents) === '') {
            throw new \RuntimeException("The configuration file is empty or could not be read: {$this->filePath}");
        }

        $decodedData = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON parsing error: ' . json_last_error_msg());
        }

        if (!isset($decodedData[self::PROVIDER_TOPICS_KEY]) || !is_array($decodedData[self::PROVIDER_TOPICS_KEY])) {
            throw new \RuntimeException(sprintf(
                "Invalid configuration file format: missing or invalid key '%s'",
                self::PROVIDER_TOPICS_KEY
            ));
        }

        $this->cachedData = $decodedData;

        return $this->cachedData;
    }
}
