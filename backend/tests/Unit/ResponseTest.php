<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Http\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testErrorPayloadMatchesOpenApiEnvelope(): void
    {
        $payload = Response::errorPayload('req-123', 'not_found', 'Recurso no encontrado.');

        $this->assertSame('not_found', $payload['error']['code']);
        $this->assertSame('Recurso no encontrado.', $payload['error']['message']);
        $this->assertSame('req-123', $payload['request_id']);
        $this->assertArrayNotHasKey('details', $payload['error']);
    }

    public function testErrorDetailsAreOptional(): void
    {
        $payload = Response::errorPayload('req-123', 'validation', 'Dato inválido.', ['campo' => 'documento']);

        $this->assertSame(['campo' => 'documento'], $payload['error']['details']);
    }

    public function testEncodeKeepsUnicodeReadable(): void
    {
        $json = Response::encode(['mensaje' => 'Presbiterio N.º 1 — Lima']);

        $this->assertStringContainsString('Presbiterio N.º 1 — Lima', $json);
        $this->assertStringNotContainsString('\\u', $json);
    }

    public function testErrorPayloadHasNoStackTraceKeys(): void
    {
        $payload = Response::errorPayload('req-123', 'internal_error', 'Error interno del servidor.');

        $this->assertArrayNotHasKey('trace', $payload);
        $this->assertArrayNotHasKey('file', $payload['error']);
    }
}
