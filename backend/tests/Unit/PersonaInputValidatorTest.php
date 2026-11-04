<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Http\HttpException;
use Pcd\Service\PersonaInputValidator;
use PHPUnit\Framework\TestCase;

final class PersonaInputValidatorTest extends TestCase
{
    private static function valid(array $override = []): array
    {
        return array_merge([
            'nombres' => 'PRUEBA', 'apellido_paterno' => 'APELLIDO',
            'apellido_materno' => 'MATERNO', 'tipo_documento' => 'DNI',
            'numero_documento' => '00123456', 'telefono' => '999888777',
        ], $override);
    }

    public function testDniPreservesLeadingZeros(): void
    {
        $data = PersonaInputValidator::normalize(self::valid(['tipo_documento' => 'DNI', 'numero_documento' => '00123456']));
        self::assertSame('DNI:00123456', $data['clave_identidad']);
        self::assertSame('00123456', $data['numero_norm']);
    }

    public function testCeDoesNotFuseBeforeOfficialRule(): void
    {
        $data = PersonaInputValidator::normalize(self::valid(['tipo_documento' => 'CE', 'numero_documento' => 'AB12345']));
        self::assertNull($data['clave_identidad']);
        self::assertNotEmpty($data['advertencias']);
    }

    public function testOtroDoesNotFuse(): void
    {
        $data = PersonaInputValidator::normalize(self::valid(['tipo_documento' => 'OTRO', 'numero_documento' => 'XZ9']));
        self::assertNull($data['clave_identidad']);
    }

    public function testH24RequiresSurnameAndPhone(): void
    {
        foreach (['nombres', 'apellido_paterno', 'apellido_materno', 'tipo_documento', 'numero_documento', 'telefono'] as $field) {
            try {
                PersonaInputValidator::normalize(self::valid([$field => '']));
                self::fail('H-24 debe rechazar campo obligatorio: ' . $field);
            } catch (HttpException $e) {
                self::assertSame(422, $e->status);
            }
        }
    }

    public function testSourceJsonStrictAndVersioned(): void
    {
        $data = PersonaInputValidator::normalize(self::valid([
            'conyuge' => ['tipo_documento' => 'DNI', 'numero_documento' => '00000001'],
            'responsabilidades' => ['es_pastor_sede' => false, 'fecha_desde' => '2026-02-02'],
        ]));
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
        $data = PersonaInputValidator::normalize(self::valid(['fecha_nacimiento' => '2024-02-29']));
        self::assertSame('2024-02-29', $data['persona']['fecha_nacimiento']);
    }

    public function testSchemaFreeOfUnrecognizedDetails(): void
    {
        $data = PersonaInputValidator::normalize(self::valid(['responsabilidades' => ['es_presbitero' => true]]));
        $json = json_decode((string) $data['datos_fuente'], true);
        self::assertSame(['schema_version' => '1.0', 'responsabilidades' => ['es_presbitero' => true]], $json);
    }
}
