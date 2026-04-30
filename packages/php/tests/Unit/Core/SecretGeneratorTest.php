<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Wanadri\XSignPayload\Core\SecretGenerator;

class SecretGeneratorTest extends TestCase
{
    public function test_generate_creates_hex_string(): void
    {
        $secret = SecretGenerator::generate(32);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $secret);
    }

    public function test_generate_different_lengths(): void
    {
        $secret16 = SecretGenerator::generate(16);
        $secret32 = SecretGenerator::generate(32);
        $secret64 = SecretGenerator::generate(64);

        $this->assertEquals(32, strlen($secret16)); // 16 bytes = 32 hex chars
        $this->assertEquals(64, strlen($secret32)); // 32 bytes = 64 hex chars
        $this->assertEquals(128, strlen($secret64)); // 64 bytes = 128 hex chars
    }

    public function test_generate_produces_unique_values(): void
    {
        $secret1 = SecretGenerator::generate(32);
        $secret2 = SecretGenerator::generate(32);

        $this->assertNotEquals($secret1, $secret2);
    }

    public function test_generate_url_safe(): void
    {
        $secret = SecretGenerator::generateUrlSafe(32);

        // Should not contain base64 chars that need encoding
        $this->assertDoesNotMatchRegularExpression('/[+/]/', $secret);
        // Should not have padding
        $this->assertDoesNotMatchRegularExpression('/=$/', $secret);
    }
}
