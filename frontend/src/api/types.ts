export interface ErrorEnvelope {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
  };
  request_id: string;
}

export interface HealthResponse {
  ok: boolean;
  request_id: string;
  status: string;
  service: string;
  version: string;
  database?: {
    status: 'up' | 'down';
  };
}

export type Perfil = 'DIGITADOR' | 'ADMIN' | 'REPORTES';
export type GeoEstado = 'OK' | 'DENEGADA' | 'NO_DISPONIBLE' | 'ERROR';

export interface Presbiterio {
  id: number;
  codigo: string;
  nombre: string;
}

export interface Sede {
  id: number;
  presbiterio_id: number;
  nombre_oficial: string;
  ubicacion_referencia: string | null;
  advertencia?: string;
}

export interface BusquedaSedes {
  items: Sede[];
  similares: Sede[];
  advertencia: string | null;
}

export interface AuthMe {
  usuario_id: number;
  perfil: Perfil;
  presbiterio_id: number;
  sede_id: number | null;
  csrf_token: string;
  vence_inactividad_en: string;
  vence_absoluta_en: string;
  usuario_codigo?: string;
  usuario_nombre?: string;
  presbiterio_nombre?: string;
  sede_nombre?: string | null;
  geo_estado?: GeoEstado | null;
}

export interface LocationPayload {
  estado: GeoEstado;
  latitud?: number | null;
  longitud?: number | null;
  precision_m?: number | null;
  capturada_en: string;
}

export interface LocationResponse {
  estado: GeoEstado;
  latitud: number | null;
  longitud: number | null;
  precision_m: number | null;
  capturada_en: string;
  recibida_en: string;
}
