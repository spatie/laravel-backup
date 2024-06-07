<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupConfig
{
    /**
     * @param class-string<CleanupStrategy> $strategy
     * @param positive-int $tries
     */
    protected function __construct(
        public string $strategy,
        public StrategyConfig $defaultStrategy,
        public int $tries,
        public int $retryDelay,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            strategy: $data['strategy'],
            defaultStrategy: StrategyConfig::fromArray($data['defaultStrategy']),
            tries: $data['tries'],
            retryDelay: $data['retryDelay'],
        );
    }
}
