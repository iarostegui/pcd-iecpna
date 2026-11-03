import { vi } from 'vitest';
import type { AuthMe, Sede } from '../api/types';

export function jsonResponse(status: number, body: unknown): Response {
  return {
    ok: status >= 200 && status < 300,
    status,
    headers: new Headers({ 'content-type': 'application/json' }),
    text: async () => (body === null ? '' : JSON.stringify(body)),
  } as Response;
}

export function emptyResponse(status = 204): Response {
  return {
    ok: status >= 200 && status < 300,
    status,
    headers: new Headers(),
    text: async () => '',
  } as Response;
}

export function authMe(overrides: Partial<AuthMe> = {}): AuthMe {
  const inOneHour = new Date(Date.now() + 60 * 60 * 1000).toISOString();
  const inTwelveHours = new Date(Date.now() + 12 * 60 * 60 * 1000).toISOString();

  return {
    usuario_id: 13,
    perfil: 'DIGITADOR',
    presbiterio_id: 1,
    sede_id: null,
    csrf_token: 'csrf-test-token',
    vence_inactividad_en: inOneHour,
    vence_absoluta_en: inTwelveHours,
    usuario_codigo: 'LIM-DIG-01',
    usuario_nombre: 'Digitador LIM 01',
    presbiterio_nombre: 'Lima',
    sede_nombre: null,
    geo_estado: 'DENEGADA',
    ...overrides,
  };
}

export function sede(overrides: Partial<Sede> = {}): Sede {
  return {
    id: 1,
    presbiterio_id: 1,
    nombre_oficial: 'Sede QA Lima',
    ubicacion_referencia: null,
    ...overrides,
  };
}

export function errorEnvelope(code: string, message: string): {
  error: { code: string; message: string };
  request_id: string;
} {
  return { error: { code, message }, request_id: 'req-test-0001' };
}

export interface Route {
  method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  path: string;
  response: (init?: RequestInit) => Response | Promise<Response>;
}

export function mockApi(routes: Route[]): ReturnType<typeof vi.fn> {
  const fetchMock = vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
    const url = String(input);
    const method = (init?.method ?? 'GET').toUpperCase();
    const route = routes.find((candidate) => candidate.path === url && candidate.method === method);

    if (route === undefined) {
      throw new Error(`Unexpected fetch ${method} ${url}`);
    }

    return route.response(init);
  });

  vi.stubGlobal('fetch', fetchMock);

  return fetchMock;
}
