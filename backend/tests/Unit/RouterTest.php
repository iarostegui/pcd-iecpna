<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Http\Request;
use Pcd\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function request(string $method, string $path): Request
    {
        return new Request($method, $path, [], '', [], 'req-test-0001');
    }

    private function router(): Router
    {
        $router = new Router();
        $router->add('GET', '/health', static function (): void {});
        $router->add('GET', '/personas/{id}', static function (): void {});
        $router->add('POST', '/auth/logout', static function (): void {}, true);

        return $router;
    }

    public function testStaticRouteResolves(): void
    {
        $match = $this->router()->resolve($this->request('GET', '/health'));

        $this->assertSame(200, $match['status']);
        $this->assertIsCallable($match['handler']);
    }

    public function testDynamicParameterIsCaptured(): void
    {
        $match = $this->router()->resolve($this->request('GET', '/personas/42'));

        $this->assertSame(200, $match['status']);
        $this->assertSame('42', $match['params']['id']);
    }

    public function testTrailingSlashIsIgnored(): void
    {
        $match = $this->router()->resolve($this->request('GET', '/health/'));

        $this->assertSame(200, $match['status']);
    }

    public function testUnknownPathReturns404(): void
    {
        $match = $this->router()->resolve($this->request('GET', '/no-existe'));

        $this->assertSame(404, $match['status']);
    }

    public function testWrongMethodReturns405AndAllowList(): void
    {
        $match = $this->router()->resolve($this->request('DELETE', '/health'));

        $this->assertSame(405, $match['status']);
        $this->assertContains('GET', $match['allow']);
    }

    public function testHeadUsesGetRoute(): void
    {
        $match = $this->router()->resolve($this->request('HEAD', '/health'));

        $this->assertSame(200, $match['status']);
    }

    public function testCsrfFlagIsPropagated(): void
    {
        $match = $this->router()->resolve($this->request('POST', '/auth/logout'));

        $this->assertTrue($match['csrf']);
    }
}
