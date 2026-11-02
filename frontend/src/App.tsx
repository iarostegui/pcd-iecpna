import { useCallback, useEffect, useState } from 'react';
import { ApiError, api } from './api/client';
import type { HealthResponse } from './api/types';

type LoadState = 'loading' | 'ready' | 'error';

export default function App() {
  const [health, setHealth] = useState<HealthResponse | null>(null);
  const [error, setError] = useState<ApiError | null>(null);
  const [state, setState] = useState<LoadState>('loading');

  const load = useCallback(async (): Promise<void> => {
    setState('loading');
    setError(null);

    try {
      const data = await api.get<HealthResponse>('/health');
      setHealth(data);
      setState('ready');
    } catch (caught) {
      setHealth(null);
      setError(
        caught instanceof ApiError
          ? caught
          : new ApiError(0, 'unknown', 'Error inesperado del cliente.', null),
      );
      setState('error');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  return (
    <main className="shell">
      <header>
        <p className="eyebrow">PCD IECPNA · Andamiaje P01</p>
        <h1>Consola de verificación</h1>
        <p className="lede">
          Comprueba que el frontend compila y que el cliente HTTP centralizado habla con la API.
          Los procesos funcionales (login, captura, consulta) pertenecen a I01–I05 y aún no están
          implementados.
        </p>
      </header>

      <section className="card" aria-live="polite">
        <h2>Estado del servicio</h2>

        {state === 'loading' && <p className="status loading">Consultando la API…</p>}

        {state === 'error' && error !== null && (
          <div className="status error" role="alert">
            <p>
              <strong>{error.code}</strong> · {error.message}
            </p>
            {error.requestId !== null && <p className="meta">request_id: {error.requestId}</p>}
            <button type="button" onClick={() => void load()}>
              Reintentar
            </button>
          </div>
        )}

        {state === 'ready' && health !== null && (
          <dl className="facts">
            <div>
              <dt>Servicio</dt>
              <dd>{health.service}</dd>
            </div>
            <div>
              <dt>Versión</dt>
              <dd>{health.version}</dd>
            </div>
            <div>
              <dt>Entorno</dt>
              <dd>{health.env}</dd>
            </div>
            <div>
              <dt>Base de datos</dt>
              <dd className={health.database.status === 'up' ? 'ok' : 'warn'}>
                {health.database.status}
              </dd>
            </div>
            <div>
              <dt>request_id</dt>
              <dd className="mono">{health.request_id}</dd>
            </div>
          </dl>
        )}
      </section>

      <section className="card">
        <h2>Preparación de seguridad</h2>
        <ul>
          <li>El cliente adjunta <code>X-CSRF-Token</code> cuando existe token en memoria.</li>
          <li>Las credenciales viajan con <code>credentials: include</code>; no se usa localStorage.</li>
          <li>Los errores de API se normalizan con código, mensaje y <code>request_id</code>.</li>
        </ul>
      </section>
    </main>
  );
}
