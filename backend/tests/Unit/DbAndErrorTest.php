<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Database\Db;
use Pcd\Http\ErrorHandler;
use Pcd\Http\HttpException;
use PHPUnit\Framework\TestCase;

final class DbAndErrorTest extends TestCase
{
    public function testDsnUsesUtf8mb4AndPort(): void
    {
        $dsn = Db::buildDsn('127.0.0.1', 3307, 'pcd_test', 'utf8mb4');

        $this->assertSame('mysql:host=127.0.0.1;port=3307;dbname=pcd_test;charset=utf8mb4', $dsn);
    }

    public function testHttpExceptionMapsToItsStatusAndCode(): void
    {
        $exception = new HttpException(403, 'csrf_invalid', 'Token inválido.');

        [$status, $code, $message] = ErrorHandler::describe($exception);

        $this->assertSame(403, $status);
        $this->assertSame('csrf_invalid', $code);
        $this->assertSame('Token inválido.', $message);
    }

    public function testGenericExceptionHidesDetails(): void
    {
        [$status, $code, $message] = ErrorHandler::describe(new \RuntimeException('SQLSTATE secreto'));

        $this->assertSame(500, $status);
        $this->assertSame('internal_error', $code);
        $this->assertSame('Error interno del servidor.', $message);
    }
}
