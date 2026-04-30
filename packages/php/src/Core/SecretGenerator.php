<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Core;

class SecretGenerator
{
    public static function generate(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function generateUrlSafe(int $bytes = 32): string
    {
        $secret = random_bytes($bytes);
        return rtrim(strtr(base64_encode($secret), '+/', '-_'), '=');
    }
}
