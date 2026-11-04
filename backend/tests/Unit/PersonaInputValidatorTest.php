<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Http\HttpException;
use Pcd\Service\PersonaInputValidator;
use PHPUnit\Framework\TestCase;

final class PersonaInputValidatorTest extends TestCase
{
    public function testDniPreservesLeadingZeros(): void
    {
        $data = PersonaInputValidator::normalize(['tipo_documento' => 'DNI', 'numero_documento' => '00123456']);
        self::assertSame('DNI:00123456', $data['clave_identidad']);
        self::assertSame('00123456', $data['numero_norm']);
    }

    public function testCeDoesNotFuseBeforeOfficialRule(): void
    {
        $data = PersonaInputValidator::normalize(['tipo_documento' => 'CE', 'numero_documento' => 'AB12345']);
        self::assertNull($data['clave_identidad']);
        self::assertNotEmpty($data['advertencias']);
    }

    public function testOtroDoesNotFuse(): void
    {
        $data = PersonaInputValidator::normalize(['tipo_documento' => 'OTRO', 'numero_documento' => 'XZ9']);
        self::assertNull($data['clave_identidad']);
    }

    public function testMissingDocumentIsAllowedWhileH24Pending(): void
    {
        $data = PersonaInputValidator::normalize(['nombres' => 'PRUEBA']);
        self::assertNull($data['clave_identidad']);
    }

    public function testSourceJsonStrictAndVersioned(): void
    {
        $data = PersonaInputValidator::normalize([
            'conyuge' => ['tipo_documento' => 'DNI', 'numero_documento' => '00000001'],
            'responsabilidades' => ['es_pastor_sede' => false, 'fecha_desde' => '2026-02-02'],
        ]);
        $json = json_decode((string) $data['datos_fuente'], true);
        self::assertSame('1.0', $json['schema_version']);
        self::assertSame('00000001', $json['conyuge']['numero_documento']);
    }

    public function testDateImpossibleRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['fecha_papel' => '2026-02-30']);
    }

    public function testDniLengthRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['tipo_documento' => 'DNI', 'numero_documento' => '1234567']);
    }

    public function testDocumentPairRequired(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['numero_documento' => '00000001']);
    }

    public function testUnknownPropertyRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['subtipo_documento' => 'LIBRETA']);
    }

    public function testUnknownJsonPropertyRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['conyuge' => ['campo_no_aprobado' => 'x']]);
    }

    public function testInvalidBooleanRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['es_lider_gc_intencion' => 'true']);
    }

    public function testInvalidEmailRejected(): void
    {
        $this->expectException(HttpException::class);
        PersonaInputValidator::normalize(['correo' => 'no-es-correo']);
    }

    public function testValidLeapDayAccepted(): void
    {
        $data = PersonaInputValidator::normalize(['fecha_nacimiento' => '2024-02-29']);
        self::assertSame('2024-02-29', $data['persona']['fecha_nacimiento']);
    }

    public function testSchemaFreeOfUnrecognizedDetails(): void
    {
        $data = PersonaInputValidator::normalize(['responsabilidades' => ['es_presbitero' => true]]);
        $json = json_decode((string) $data['datos_fuente'], true);
        self::assertSame(['schema_version' => '1.0', 'responsabilidades' => ['es_presbitero' => true]], $json);
    }
}
