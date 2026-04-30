<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class XSign extends BaseConfig
{
    /**
     * Secret key for signing/verification
     */
    public string $secret = '';

    /**
     * Algorithm: sha256 or sha512
     */
    public string $algorithm = 'sha256';

    /**
     * Enable timestamp validation (replay protection)
     */
    public bool $enableTimestamp = true;

    /**
     * Replay window in minutes
     */
    public int $replayWindow = 10;

    /**
     * Routes to exclude from signature verification
     */
    public array $excludeRoutes = [];
}
