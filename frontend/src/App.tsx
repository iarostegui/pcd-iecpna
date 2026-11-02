import { useCallback, useEffect, useRef, useState } from 'react';
import { ApiError, api, setCsrfToken, setUnauthorizedHandler } from './api/client';
import type {
  AuthMe,
  BusquedaSedes,
  GeoEstado,
  LocationResponse,
  Presbiterio,
  Sede,
} from './api/types';
import ContextScreen from './components/ContextScreen';
import LoginScreen from './components/LoginScreen';
import Workspace from './components/Workspace';
import { requestLocationOnce } from './lib/geolocation';

type Phase = 'booting' | 'anonymous' | 'authenticated';

const GEO_MARKER = 'pcd_geo_solicitada';

function readGeoMarker(): boolean {
  try {
    return window.sessionStorage.getItem(GEO_MARKER) === '1';
  } catch {
    return false;
  }
}

function writeGeoMarker(value: boolean): void {
  try {
    if (value) {
      window.sessionStorage.setItem(GEO_MARKER, '1');
    } else {
      window.sessionStorage.removeItem(GEO_MARKER);
    }
  } catch {
    // sessionStorage puede estar bloqueado; el servidor sigue limitando a una captura.
  }
}

function messageFor(error: unknown): string {
  if (!(error instanceof ApiError)) {
    return 'Ocurrió un error inesperado.';
  }

  if (error.status === 401) {
    return 'Presbiterio, usuario o clave incorrectos.';
  }

  if (error.status === 429) {
    return 'Demasiados intentos de acceso. Intenta nuevamente más tarde.';
  }

  if (error.status === 0) {
    return 'No se pudo conectar con la API. Verifica el servicio e intenta de nuevo.';
  }

  return error.message;
}

function idempotencyKey(): string {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }

  return `sede-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export default function App() {
  const [phase, setPhase] = useState<Phase>('booting');
  const [auth, setAuth] = useState<AuthMe | null>(null);
  const [presbiterios, setPresbiterios] = useState<Presbiterio[]>([]);
  const [sedes, setSedes] = useState<BusquedaSedes>({ items: [], similares: [], advertencia: null });
  const [sedeQuery, setSedeQuery] = useState('');
  const [loadingSedes, setLoadingSedes] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [loginError, setLoginError] = useState<string | null>(null);
  const [bootError, setBootError] = useState<string | null>(null);
  const [contextError, setContextError] = useState<string | null>(null);
  const [contextNotice, setContextNotice] = useState<string | null>(null);
  const [sessionNotice, setSessionNotice] = useState<string | null>(null);
  const [forceContext, setForceContext] = useState(false);
  const [gpsState, setGpsState] = useState<GeoEstado | null>(null);

  const phaseRef = useRef<Phase>('booting');
  const geoAttemptedRef = useRef(false);

  useEffect(() => {
    phaseRef.current = phase;
  }, [phase]);

  const applyAuth = useCallback((session: AuthMe): void => {
    setAuth(session);
    setCsrfToken(session.csrf_token);
    if (session.geo_estado !== undefined && session.geo_estado !== null) {
      setGpsState(session.geo_estado);
    }
  }, []);

  const resetSession = useCallback((message: string | null): void => {
    setAuth(null);
    setCsrfToken(null);
    setPhase('anonymous');
    setForceContext(false);
    setGpsState(null);
    setSedes({ items: [], similares: [], advertencia: null });
    setSedeQuery('');
    setContextError(null);
    setContextNotice(null);
    setSessionNotice(message);
    writeGeoMarker(false);
  }, []);

  const handleUnauthorized = useCallback((): void => {
    if (phaseRef.current === 'authenticated') {
      resetSession('La sesión expiró o fue revocada. Inicia sesión nuevamente.');
    }
  }, [resetSession]);

  useEffect(() => {
    setUnauthorizedHandler(handleUnauthorized);

    return () => setUnauthorizedHandler(null);
  }, [handleUnauthorized]);

  const loadSedes = useCallback(async (query: string): Promise<void> => {
    setLoadingSedes(true);
    setContextError(null);

    try {
      const suffix = query.trim() === '' ? '' : `?q=${encodeURIComponent(query.trim())}`;
      const result = await api.get<BusquedaSedes>(`/sedes${suffix}`);
      setSedes(result);
    } catch (error) {
      if (!(error instanceof ApiError && error.status === 401)) {
        setContextError(messageFor(error));
      }
    } finally {
      setLoadingSedes(false);
    }
  }, []);

  useEffect(() => {
    let active = true;

    async function boot(): Promise<void> {
      try {
        const list = await api.get<Presbiterio[]>('/presbiterios');
        if (active) {
          setPresbiterios(list);
        }
      } catch (error) {
        if (active) {
          setBootError(messageFor(error));
        }
      }

      try {
        const me = await api.get<AuthMe>('/auth/me');
        if (!active) {
          return;
        }
        applyAuth(me);
        setPhase('authenticated');
      } catch (error) {
        if (!active) {
          return;
        }
        if (error instanceof ApiError && error.status === 401) {
          setPhase('anonymous');
        } else {
          setBootError(messageFor(error));
          setPhase('anonymous');
        }
      }
    }

    void boot();

    return () => {
      active = false;
    };
  }, [applyAuth]);

  useEffect(() => {
    if (phase !== 'authenticated' || auth === null) {
      return;
    }

    void loadSedes(sedeQuery);
  }, [phase, auth?.sede_id, loadSedes]); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    if (phase !== 'authenticated' || auth === null || auth.perfil === 'REPORTES') {
      return;
    }
    if (auth.geo_estado !== undefined && auth.geo_estado !== null) {
      setGpsState(auth.geo_estado);
      return;
    }
    if (geoAttemptedRef.current || readGeoMarker()) {
      return;
    }

    geoAttemptedRef.current = true;
    writeGeoMarker(true);

    void (async () => {
      const payload = await requestLocationOnce();

      try {
        const stored = await api.post<LocationResponse>('/auth/ubicacion', payload);
        setGpsState(stored.estado);
        setAuth((current) => (current === null ? null : { ...current, geo_estado: stored.estado }));
      } catch (error) {
        if (error instanceof ApiError && error.status === 409) {
          try {
            const me = await api.get<AuthMe>('/auth/me');
            applyAuth(me);
            setGpsState(me.geo_estado ?? null);
          } catch {
            setGpsState(payload.estado);
          }
        } else {
          setGpsState(payload.estado);
        }
      }
    })();
  }, [phase, auth, applyAuth]);

  useEffect(() => {
    if (phase !== 'authenticated' || auth === null) {
      return;
    }

    let lastRefresh = Date.now();

    const refreshOnActivity = (): void => {
      if (Date.now() - lastRefresh < 60_000) {
        return;
      }
      lastRefresh = Date.now();
      void api
        .get<AuthMe>('/auth/me')
        .then((session) => applyAuth(session))
        .catch(() => {
          // El manejador global de 401 ya devuelve al login cuando corresponde.
        });
    };

    window.addEventListener('pointerdown', refreshOnActivity, { passive: true });
    window.addEventListener('keydown', refreshOnActivity);

    return () => {
      window.removeEventListener('pointerdown', refreshOnActivity);
      window.removeEventListener('keydown', refreshOnActivity);
    };
  }, [phase, auth?.usuario_id, applyAuth]);

  useEffect(() => {
    if (phase !== 'authenticated' || auth === null) {
      return;
    }

    const expirations = [auth.vence_inactividad_en, auth.vence_absoluta_en]
      .map((value) => Date.parse(value))
      .filter((value) => Number.isFinite(value));

    if (expirations.length === 0) {
      return;
    }

    const delay = Math.min(...expirations) - Date.now();

    if (delay <= 0) {
      handleUnauthorized();
      return;
    }

    const timer = window.setTimeout(handleUnauthorized, Math.min(delay + 250, 2_147_483_647));

    return () => window.clearTimeout(timer);
  }, [phase, auth, handleUnauthorized]);

  async function handleLogin(presbiterioId: number, usuario: string, password: string): Promise<void> {
    setSubmitting(true);
    setLoginError(null);
    setBootError(null);
    setSessionNotice(null);

    try {
      const session = await api.post<AuthMe>('/auth/login', {
        presbiterio_id: presbiterioId,
        usuario,
        password,
      });
      applyAuth(session);
      setPhase('authenticated');
      setSedeQuery('');
      setForceContext(false);
      setContextNotice(null);
      setGpsState(session.geo_estado ?? null);
    } catch (error) {
      setLoginError(messageFor(error));
    } finally {
      setSubmitting(false);
    }
  }

  async function handleLogout(): Promise<void> {
    try {
      await api.post<null>('/auth/logout', {});
    } catch {
      // La sesión puede haber expirado ya; se limpia el cliente de todos modos.
    } finally {
      resetSession('Sesión cerrada correctamente.');
    }
  }

  async function handleSelectSede(sedeId: number): Promise<void> {
    setSubmitting(true);
    setContextError(null);

    try {
      const session = await api.put<AuthMe>('/contexto/sede', { sede_id: sedeId });
      applyAuth(session);
      setForceContext(false);
      setContextNotice(null);
    } catch (error) {
      if (!(error instanceof ApiError && error.status === 401)) {
        setContextError(messageFor(error));
      }
    } finally {
      setSubmitting(false);
    }
  }

  async function handleCreateSede(nombre: string, referencia: string | null): Promise<void> {
    setSubmitting(true);
    setContextError(null);
    setContextNotice(null);

    try {
      const created = await api.post<Sede>(
        '/sedes',
        { nombre_oficial: nombre, ubicacion_referencia: referencia },
        { 'Idempotency-Key': idempotencyKey() },
      );

      if (created.advertencia !== undefined) {
        setContextNotice(created.advertencia);
      }

      await loadSedes(sedeQuery);
      await handleSelectSede(created.id);
    } catch (error) {
      if (error instanceof ApiError && error.code === 'sede_duplicada') {
        setContextError('Ya existe una sede con ese nombre normalizado en este presbiterio.');
      } else if (!(error instanceof ApiError && error.status === 401)) {
        setContextError(messageFor(error));
      }
    } finally {
      setSubmitting(false);
    }
  }

  async function handleChangePresbiterio(presbiterioId: number): Promise<void> {
    setSubmitting(true);
    setContextError(null);

    try {
      const session = await api.put<AuthMe>('/contexto/presbiterio', { presbiterio_id: presbiterioId });
      applyAuth(session);
      setSedeQuery('');
      setSedes({ items: [], similares: [], advertencia: null });
      setForceContext(true);
      await loadSedes('');
    } catch (error) {
      if (!(error instanceof ApiError && error.status === 401)) {
        setContextError(messageFor(error));
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (phase === 'booting') {
    return (
      <main className="work-shell">
        <p className="notice info" role="status">
          Restaurando sesión…
        </p>
      </main>
    );
  }

  if (phase === 'anonymous' || auth === null) {
    return (
      <LoginScreen
        presbiterios={presbiterios}
        submitting={submitting}
        error={loginError ?? bootError}
        notice={sessionNotice}
        onLogin={handleLogin}
      />
    );
  }

  if (auth.perfil !== 'REPORTES' && (auth.sede_id === null || forceContext)) {
    return (
      <ContextScreen
        auth={auth}
        presbiterios={presbiterios}
        sedes={sedes}
        query={sedeQuery}
        loadingSedes={loadingSedes}
        submitting={submitting}
        error={contextError}
        notice={contextNotice}
        onSearch={(query) => {
          setSedeQuery(query);
          void loadSedes(query);
        }}
        onSelectSede={handleSelectSede}
        onCreateSede={handleCreateSede}
        onChangePresbiterio={handleChangePresbiterio}
        onLogout={handleLogout}
      />
    );
  }

  return (
    <Workspace
      auth={auth}
      gpsState={gpsState}
      notice={contextNotice}
      onOpenContext={() => setForceContext(true)}
      onLogout={handleLogout}
    />
  );
}
