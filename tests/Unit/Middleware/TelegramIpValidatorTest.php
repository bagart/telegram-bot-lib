<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Middleware;

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use PHPUnit\Framework\TestCase;

class TelegramIpValidatorTest extends TestCase
{
    private TelegramIpValidator $validator;

    public function test_it_validates_telegram_ips(): void
    {
        // Based on the ranges in the code: 149.154.160.0/20 and 91.108.4.0/22
        $this->assertTrue($this->validator->validate('149.154.160.1'));
        $this->assertTrue($this->validator->validate('91.108.4.1'));
    }

    public function test_it_rejects_non_telegram_ips(): void
    {
        $this->assertFalse($this->validator->validate('8.8.8.8'));
        $this->assertFalse($this->validator->validate('1.1.1.1'));
        $this->assertFalse($this->validator->validate('127.0.0.1'));
    }

    public function test_it_handles_invalid_ip_format(): void
    {
        $this->assertFalse($this->validator->validate('not-an-ip'));
        $this->assertFalse($this->validator->validate('256.256.256.256'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TelegramIpValidator();
    }
}
