import type { ErrorEnvelope } from './types';

const BASE_URL = (import.meta.env.VITE_API_BASE_URL ?? '/api/v1').replace(/\/$/, '');
const TIMEOUT_MS = 10_000;

let csrfToken: string | null = null;
let unauthorizedHandler: ((error: ApiError, path: string) => void) | null = null;

/**
 * El token CSRF se mantiene en memoria (nunca en localStorage).
 * I01 lo obtiene de /auth/me o /auth/login y lo entrega con setCsrfToken.
 */
export function setCsrfToken(token: string | null): void {
  csrfToken = token;
}

export function getCsrfToken(): string | null {
  return csrfToken;
}

/**
 * Permite que la aplicación reaccione a una sesión expirada/revocada sin
 * duplicar el manejo de 401 en cada pantalla.
 */
export function setUnauthorizedHandler(
  handler: ((error: ApiError, path: string) => void) | null,
): void {
  unauthorizedHandler = handler;
}

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly code: string,
    message: string,
    public readonly requestId: string | null = null,
    public readonly details?: Record<string, unknown>,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

function isErrorEnvelope(value: unknown): value is ErrorEnvelope {
  if (typeof value !== 'object' || value === null) {
    return false;
  }

  const candidate = value as Partial<ErrorEnvelope>;

  return (
    typeof candidate.error === 'object' &&
    candidate.error !== null &&
    typeof candidate.error.code === 'string' &&
    typeof candidate.error.message === 'string'
  );
}

function parseBody(text: string): unknown {
  if (text.trim() === '') {
    return null;
  }

  try {
    return JSON.parse(text) as unknown;
  } catch {
    return null;
  }
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), TIMEOUT_MS);
  const headers = new Headers(init.headers);

  headers.set('Accept', 'application/json');

  if (init.body !== undefined && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  if (csrfToken !== null) {
    headers.set('X-CSRF-Token', csrfToken);
  }

  try {
    const response = await fetch(`${BASE_URL}${path}`, {
      ...init,
      headers,
      credentials: 'include',
      signal: controller.signal,
    });
    const body = parseBody(await response.text());

    if (!response.ok) {
      const envelope = isErrorEnvelope(body) ? body : null;
      const error = new ApiError(
        response.status,
        envelope?.error.code ?? 'http_error',
        envelope?.error.message ?? `HTTP ${response.status}`,
        envelope?.request_id ?? null,
        envelope?.error.details,
      );

      if (response.status === 401 && path !== '/auth/login') {
        unauthorizedHandler?.(error, path);
      }

      throw error;
    }

    return body as T;
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    if (error instanceof DOMException && error.name === 'AbortError') {
      throw new ApiError(0, 'timeout', 'La solicitud excedió el tiempo de espera.', null);
    }

    throw new ApiError(0, 'network_error', 'No se pudo conectar con la API.', null);
  } finally {
    window.clearTimeout(timeoutId);
  }
}

type ExtraHeaders = Record<string, string>;

export const api = {
  get: <T>(path: string): Promise<T> => request<T>(path, { method: 'GET' }),
  post: <T>(path: string, body: unknown, extraHeaders?: ExtraHeaders): Promise<T> =>
    request<T>(path, {
      method: 'POST',
      body: JSON.stringify(body),
      headers: extraHeaders,
    }),
  put: <T>(path: string, body: unknown, extraHeaders?: ExtraHeaders): Promise<T> =>
    request<T>(path, {
      method: 'PUT',
      body: JSON.stringify(body),
      headers: extraHeaders,
    }),
  patch: <T>(path: string, body: unknown, extraHeaders?: ExtraHeaders): Promise<T> =>
    request<T>(path, {
      method: 'PATCH',
      body: JSON.stringify(body),
      headers: extraHeaders,
    }),
  delete: <T>(path: string): Promise<T> => request<T>(path, { method: 'DELETE' }),
};
