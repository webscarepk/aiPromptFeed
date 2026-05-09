<?php

namespace App\Services\ModelWorkers;

use Exception;

class ModelWorkerRegistry
{
    /**
     * @var array<string, ModelWorker>
     */
    protected array $workers = [];

    public function __construct()
    {
        // Register default mock worker
        $this->register('custom', new MockWorker());
        $this->register('nano_banana', new MockWorker());
        $this->register('dalle', new MockWorker());
        $this->register('midjourney', new MockWorker());
    }

    public function register(string $providerType, ModelWorker $worker): void
    {
        $this->workers[$providerType] = $worker;
    }

    public function getWorker(string $providerType): ModelWorker
    {
        if (!isset($this->workers[$providerType])) {
            throw new Exception("No worker registered for provider type: {$providerType}");
        }

        return $this->workers[$providerType];
    }
}
