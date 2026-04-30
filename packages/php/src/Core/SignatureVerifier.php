<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Core;

use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;

class SignatureVerifier
{
    public function verify(string $signature, string $body, Config $config, ?string $timestamp = null): bool
    {
        // Parse signature format: "sha256=hexstring" or "sha512=hexstring"
        if (!str_contains($signature, '=')) {
            throw new InvalidSignatureException('Invalid signature format');
        }

        [$algo, $providedHash] = explode('=', $signature, 2);
        
        if ($algo !== $config->algorithm) {
            throw new InvalidSignatureException('Algorithm mismatch');
        }

        // Validate timestamp if enabled
        if ($config->enableTimestamp) {
            if ($timestamp === null) {
                throw new ExpiredRequestException('Timestamp required');
            }

            $this->validateTimestamp($timestamp, $config->replayWindow);
            $message = $timestamp . '.' . $body;
        } else {
            $message = $body;
        }

        // Compute expected signature
        $expectedHash = hash_hmac($config->algorithm, $message, $config->secret);

        // Constant-time comparison
        if (!hash_equals($expectedHash, $providedHash)) {
            throw new InvalidSignatureException('Signature does not match');
        }

        return true;
    }

    private function validateTimestamp(string $timestamp, int $replayWindow): void
    {
        $now = (int) (microtime(true) * 1000);
        $requestTime = (int) $timestamp;
        $diffMinutes = abs($now - $requestTime) / 1000 / 60;

        if ($diffMinutes > $replayWindow) {
            throw new ExpiredRequestException(
                sprintf('Request timestamp is outside %d minute window', $replayWindow)
            );
        }
    }

    public function sign(string $body, Config $config, ?string $timestamp = null): string
    {
        if ($config->enableTimestamp) {
            $timestamp ??= (string) (int) (microtime(true) * 1000);
            $message = $timestamp . '.' . $body;
        } else {
            $message = $body;
        }

        $hash = hash_hmac($config->algorithm, $message, $config->secret);
        
        return $config->algorithm . '=' . $hash;
    }
}
