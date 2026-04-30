<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Core;

use Wanadri\XSignPayload\Exceptions\XSignException;

class Config
{
    public readonly string $secret;
    public readonly string $algorithm;
    public readonly bool $enableTimestamp;
    public readonly int $replayWindow;

    public function __construct(array $config)
    {
        $this->secret = $config['secret'] ?? throw new XSignException('Secret is required');
        
        if (empty($this->secret)) {
            throw new XSignException('Secret cannot be empty');
        }

        $this->algorithm = $config['algorithm'] ?? 'sha256';
        
        if (!in_array($this->algorithm, ['sha256', 'sha512'], true)) {
            throw new XSignException('Algorithm must be sha256 or sha512');
        }

        $this->enableTimestamp = $config['enable_timestamp'] ?? true;
        $this->replayWindow = $config['replay_window'] ?? 10;

        if ($this->replayWindow < 1) {
            throw new XSignException('Replay window must be at least 1 minute');
        }
    }

    public static function fromEnv(): self
    {
        return new self([
            'secret' => $_ENV['X_SIGN_SECRET'] ?? '',
            'algorithm' => $_ENV['X_SIGN_ALGORITHM'] ?? 'sha256',
            'enable_timestamp' => filter_var(
                $_ENV['X_SIGN_ENABLE_TIMESTAMP'] ?? 'true',
                FILTER_VALIDATE_BOOLEAN
            ),
            'replay_window' => (int) ($_ENV['X_SIGN_REPLAY_WINDOW'] ?? 10),
        ]);
    }
}
