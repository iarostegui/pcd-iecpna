<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Http\HttpException;
use Pcd\Support\Validation;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    public function testRequiredStringTrims(): void
    {
        $this->assertSame('Sede Lima', Validation::requiredString(['nombre' => '  Sede Lima  '], 'nombre', 2, 200));
    }

    public function testMissingFieldThrows422(): void
    {
        $this->expectException(HttpException::class);

        try {
            Validation::requiredString([], 'nombre', 2, 200);
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->status);
            $this->assertSame('validation_error', $exception->errorCode);
            $this->assertSame(['campo' => 'nombre'], $exception->details);

            throw $exception;
        }
    }

    public function testIntegerAcceptsNumericString(): void
    {
        $this->assertSame(4, Validation::requiredInt(['presbiterio_id' => '4'], 'presbiterio_id'));
    }

    public function testFloatRangeIsEnforced(): void
    {
        $this->expectException(HttpException::class);

        Validation::optionalFloat(['latitud' => 91], 'latitud', -90.0, 90.0);
    }

    public function testDateTimeNormalizesToUtcIso(): void
    {
        $this->assertSame(
            '2026-11-02T12:00:00Z',
            Validation::requiredDateTime(['capturada_en' => '2026-11-02T07:00:00-05:00'], 'capturada_en'),
        );
    }
}
