<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupConfig extends Data
{
    /**
     * @param  class-string<CleanupStrategy>  $strategy
     */
    protected function __construct(
        public string $strategy,
        public StrategyConfig $defaultStrategy,
        public int $tries,
        public int $retryDelay,
    ) {
        if ($this->tries < 1) {
            throw InvalidConfig::integerMustBePositive('cleanup tries');
        }

        if (! class_exists($this->strategy)) {
            throw InvalidConfig::invalidStrategy($this->strategy);
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            strategy: $data['strategy'],
            defaultStrategy: StrategyConfig::fromArray($data['default_strategy']),
            tries: $data['tries'],
            retryDelay: $data['retry_delay'],
        );
    }
}
