<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\SettingController;
use ReflectionMethod;

class SsrfGuardTest extends TestCase
{
    protected function guard(): ReflectionMethod
    {
        $method = new ReflectionMethod(SettingController::class, 'isPublicHttpUrl');
        $method->setAccessible(true);
        return $method;
    }

    private function check(string $url): bool
    {
        return $this->guard()->invoke(app(SettingController::class), $url);
    }

    public function test_it_blocks_loopback_and_private_ranges()
    {
        $this->assertFalse($this->check('http://127.0.0.1/admin'));
        $this->assertFalse($this->check('http://localhost/admin'));
        $this->assertFalse($this->check('http://169.254.169.254/latest/meta-data')); // cloud metadata
        $this->assertFalse($this->check('http://10.0.0.5/internal'));
        $this->assertFalse($this->check('http://192.168.1.1/'));
    }

    public function test_it_blocks_non_http_schemes()
    {
        $this->assertFalse($this->check('file:///etc/passwd'));
        $this->assertFalse($this->check('ftp://example.com/'));
        $this->assertFalse($this->check('gopher://example.com/'));
    }

    public function test_it_allows_public_https_url()
    {
        // A literal public IP avoids DNS flakiness in CI.
        $this->assertTrue($this->check('https://8.8.8.8/health'));
    }
}
