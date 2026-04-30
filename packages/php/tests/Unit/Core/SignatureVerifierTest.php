<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Wanadri\XSignPayload\Core\Config;
use Wanadri\XSignPayload\Core\SignatureVerifier;
use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;

class SignatureVerifierTest extends TestCase
{
    private SignatureVerifier $verifier;
    private Config $config;

    protected function setUp(): void
    {
        $this->verifier = new SignatureVerifier();
        $this->config = new Config([
            'secret' => 'test-secret-32bytes-long!!!',
            'algorithm' => 'sha256',
            'enable_timestamp' => false,
            'replay_window' => 10,
        ]);
    }

    public function test_sign_without_timestamp(): void
    {
        $body = '{"test":"data"}';
        $signature = $this->verifier->sign($body, $this->config);

        $this->assertMatchesRegularExpression('/^sha256=[a-f0-9]{64}$/', $signature);
    }

    public function test_verify_valid_signature(): void
    {
        $body = '{"test":"data"}';
        $signature = $this->verifier->sign($body, $this->config);

        $result = $this->verifier->verify($signature, $body, $this->config);

        $this->assertTrue($result);
    }

    public function test_verify_rejects_tampered_body(): void
    {
        $body = '{"test":"data"}';
        $signature = $this->verifier->sign($body, $this->config);

        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Signature does not match');

        $this->verifier->verify($signature, '{"test":"tampered"}', $this->config);
    }

    public function test_verify_rejects_invalid_format(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Invalid signature format');

        $this->verifier->verify('invalid-format', 'body', $this->config);
    }

    public function test_verify_rejects_algorithm_mismatch(): void
    {
        $body = '{"test":"data"}';
        $signature = $this->verifier->sign($body, $this->config);

        $sha512Config = new Config([
            'secret' => 'test-secret-32bytes-long!!!',
            'algorithm' => 'sha512',
            'enable_timestamp' => false,
        ]);

        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Algorithm mismatch');

        $this->verifier->verify($signature, $body, $sha512Config);
    }

    public function test_sign_with_timestamp(): void
    {
        $config = new Config([
            'secret' => 'test-secret-32bytes-long!!!',
            'algorithm' => 'sha256',
            'enable_timestamp' => true,
            'replay_window' => 10,
        ]);

        $body = '{"test":"data"}';
        $timestamp = (string) (time() * 1000);
        $signature = $this->verifier->sign($body, $config, $timestamp);

        $this->assertMatchesRegularExpression('/^sha256=[a-f0-9]{64}$/', $signature);

        $result = $this->verifier->verify($signature, $body, $config, $timestamp);
        $this->assertTrue($result);
    }

    public function test_verify_rejects_expired_timestamp(): void
    {
        $config = new Config([
            'secret' => 'test-secret-32bytes-long!!!',
            'algorithm' => 'sha256',
            'enable_timestamp' => true,
            'replay_window' => 1,
        ]);

        $body = '{"test":"data"}';
        $oldTimestamp = (string) ((time() - 120) * 1000); // 2 minutes ago

        $this->expectException(ExpiredRequestException::class);
        $this->expectExceptionMessage('outside 1 minute window');

        // Create a valid signature with old timestamp
        $signature = $this->verifier->sign($body, $config, $oldTimestamp);
        $this->verifier->verify($signature, $body, $config, $oldTimestamp);
    }

    public function test_sha512_algorithm(): void
    {
        $config = new Config([
            'secret' => 'test-secret-32bytes-long!!!',
            'algorithm' => 'sha512',
            'enable_timestamp' => false,
        ]);

        $body = '{"test":"data"}';
        $signature = $this->verifier->sign($body, $config);

        $this->assertMatchesRegularExpression('/^sha512=[a-f0-9]{128}$/', $signature);

        $result = $this->verifier->verify($signature, $body, $config);
        $this->assertTrue($result);
    }
}
