<?php declare(strict_types=1);

namespace App\Repository;

class ProviderRepository
{
    private const PROVIDER_TOPICS_KEY = 'provider_topics';
    private ?array $cachedData = null;

    public function __construct(private readonly string $filePath)
    {
    }

    /**
     * Return providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        $decodedData = $this->loadConfigurationFile();
        return $decodedData[self::PROVIDER_TOPICS_KEY] ?? [];
    }

    /**
     * Get providers from JSON file
     *
     * @return array
     */
    private function loadConfigurationFile(): array
    {
        // If cached data is already available, return it to avoid reading the file again.
        if ($this->cachedData !== null) {
            return $this->cachedData;
        }

        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("The configuration file does not exist: {$this->filePath}");
        }

        if (!is_readable($this->filePath)) {
            throw new \RuntimeException("The configuration file is not readable: {$this->filePath}");
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

        // Cache the decoded data to avoid repeated file and JSON parsing operations.
        $this->cachedData = $decodedData;

        return $this->cachedData;
    }
}
