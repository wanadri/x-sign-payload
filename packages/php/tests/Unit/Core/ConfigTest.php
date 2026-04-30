<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Wanadri\XSignPayload\Core\Config;
use Wanadri\XSignPayload\Exceptions\XSignException;

class ConfigTest extends TestCase
{
    public function test_constructor_requires_secret(): void
    {
        $this->expectException(XSignException::class);
        $this->expectExceptionMessage('Secret is required');

        new Config([]);
    }

    public function test_constructor_rejects_empty_secret(): void
    {
        $this->expectException(XSignException::class);
        $this->expectExceptionMessage('Secret cannot be empty');

        new Config(['secret' => '']);
    }

    public function test_default_algorithm_is_sha256(): void
    {
        $config = new Config(['secret' => 'test-secret']);

        $this->assertEquals('sha256', $config->algorithm);
    }

    public function test_validates_algorithm(): void
    {
        $this->expectException(XSignException::class);
        $this->expectExceptionMessage('Algorithm must be sha256 or sha512');

        new Config(['secret' => 'test', 'algorithm' => 'md5']);
    }

    public function test_accepts_sha512(): void
    {
        $config = new Config(['secret' => 'test', 'algorithm' => 'sha512']);

        $this->assertEquals('sha512', $config->algorithm);
    }

    public function test_default_enable_timestamp_is_true(): void
    {
        $config = new Config(['secret' => 'test']);

        $this->assertTrue($config->enableTimestamp);
    }

    public function test_default_replay_window_is_10(): void
    {
        $config = new Config(['secret' => 'test']);

        $this->assertEquals(10, $config->replayWindow);
    }

    public function test_validates_replay_window(): void
    {
        $this->expectException(XSignException::class);
        $this->expectExceptionMessage('Replay window must be at least 1 minute');

        new Config(['secret' => 'test', 'replay_window' => 0]);
    }
}
