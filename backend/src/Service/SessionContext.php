<?php

declare(strict_types=1);

namespace Pcd\Service;

use Pcd\Config\Env;
use Pcd\Support\Clock;

final class SessionContext
{
    public function __construct(
        public readonly int $sesionId,
        public readonly int $usuarioId,
        public readonly string $usuarioCodigo,
        public readonly string $usuarioNombre,
        public readonly string $perfil,
        public readonly int $presbiterioId,
        public readonly string $presbiterioNombre,
        public readonly ?int $sedeId,
        public readonly ?string $sedeNombre,
        public readonly string $fechaIngreso,
        public readonly string $lastSeen,
        public readonly ?string $geoEstado,
    ) {
    }

    /** @return array<string, mixed> */
    public function toAuthMe(string $csrfToken): array
    {
        $idleSeconds = Env::int('PCD_SESSION_IDLE_MINUTES', 45) * 60;
        $absoluteSeconds = Env::int('PCD_SESSION_ABSOLUTE_HOURS', 12) * 3600;

        return [
            'usuario_id' => $this->usuarioId,
            'perfil' => $this->perfil,
            'presbiterio_id' => $this->presbiterioId,
            'sede_id' => $this->sedeId,
            'csrf_token' => $csrfToken,
            'vence_inactividad_en' => gmdate('Y-m-d\TH:i:s\Z', Clock::timestamp($this->lastSeen) + $idleSeconds),
            'vence_absoluta_en' => gmdate('Y-m-d\TH:i:s\Z', Clock::timestamp($this->fechaIngreso) + $absoluteSeconds),
            // Extensiones aditivas documentadas en OpenAPI para que React muestre
            // identidad/contexto y no repita la solicitud GPS tras recargar.
            'usuario_codigo' => $this->usuarioCodigo,
            'usuario_nombre' => $this->usuarioNombre,
            'presbiterio_nombre' => $this->presbiterioNombre,
            'sede_nombre' => $this->sedeNombre,
            'geo_estado' => $this->geoEstado,
        ];
    }
}
