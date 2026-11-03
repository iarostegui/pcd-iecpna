import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import App from '../App';
import { authMe, emptyResponse, errorEnvelope, jsonResponse, mockApi, sede } from '../test/apiMock';

const presbiterios = [
  { id: 1, codigo: 'LIM', nombre: 'Lima' },
  { id: 2, codigo: 'TRU', nombre: 'La Libertad – Trujillo' },
  { id: 3, codigo: 'CHI', nombre: 'Chimbote' },
  { id: 4, codigo: 'IQU', nombre: 'Iquitos' },
];

function bootUnauthenticated() {
  return [
    { method: 'GET' as const, path: '/api/v1/presbiterios', response: () => jsonResponse(200, presbiterios) },
    { method: 'GET' as const, path: '/api/v1/auth/me', response: () => jsonResponse(401, errorEnvelope('unauthenticated', 'Se requiere una sesión autenticada.')) },
  ];
}

async function loginFromScreen(): Promise<void> {
  const user = userEvent.setup();

  await user.selectOptions(screen.getByRole('combobox'), '1');
  await user.type(screen.getByLabelText(/código de usuario/i), 'LIM-DIG-01');
  await user.type(screen.getByLabelText(/contraseña/i), 'clave-sintetica');
  await user.click(screen.getByRole('button', { name: /ingresar/i }));
}

describe('App I01', () => {
  beforeEach(() => {
    window.localStorage.clear();
    window.sessionStorage.clear();
    vi.unstubAllGlobals();
  });

  afterEach(() => {
    window.localStorage.clear();
    window.sessionStorage.clear();
  });

  it('CA-10/CA-14: login, selección de sede, contexto y logout sin localStorage', async () => {
    const sedes = { items: [sede()], similares: [], advertencia: null };
    const fetchMock = mockApi([
      ...bootUnauthenticated(),
      { method: 'POST', path: '/api/v1/auth/login', response: () => jsonResponse(200, authMe({ geo_estado: 'DENEGADA' })) },
      { method: 'GET', path: '/api/v1/sedes', response: () => jsonResponse(200, sedes) },
      {
        method: 'PUT',
        path: '/api/v1/contexto/sede',
        response: () => jsonResponse(200, authMe({ sede_id: 1, sede_nombre: 'Sede QA Lima' })),
      },
      { method: 'POST', path: '/api/v1/auth/logout', response: () => emptyResponse(204) },
    ]);

    render(<App />);

    await screen.findByRole('heading', { name: /ingresar al sistema/i });
    expect(screen.queryByText('LIM-DIG-01')).not.toBeInTheDocument();

    await loginFromScreen();
    await screen.findByRole('heading', { name: /selecciona la sede/i });

    await userEvent.click(screen.getByRole('button', { name: /sede qa lima/i }));
    await screen.findByRole('heading', { name: /digitador lim 01/i });

    expect(screen.getByText('Lima')).toBeInTheDocument();
    expect(screen.getAllByText('Sede QA Lima').length).toBeGreaterThan(0);
    expect(window.localStorage.length).toBe(0);

    await userEvent.click(screen.getByRole('button', { name: /cerrar sesión/i }));
    await screen.findByRole('heading', { name: /ingresar al sistema/i });

    expect(fetchMock).toHaveBeenCalled();
    const urls = fetchMock.mock.calls.map((call) => String(call[0]));
    expect(urls).toContain('/api/v1/auth/logout');
  });

  it('CA-61/CA-85: un 401 en una llamada autenticada devuelve al login con aviso', async () => {
    mockApi([
      { method: 'GET', path: '/api/v1/presbiterios', response: () => jsonResponse(200, presbiterios) },
      {
        method: 'GET',
        path: '/api/v1/auth/me',
        response: () => jsonResponse(200, authMe({ sede_id: 1, sede_nombre: 'Sede QA Lima' })),
      },
      { method: 'GET', path: '/api/v1/sedes', response: () => jsonResponse(200, { items: [], similares: [], advertencia: null }) },
      {
        method: 'GET',
        path: '/api/v1/sedes?q=x',
        response: () => jsonResponse(401, errorEnvelope('session_expired', 'La sesión expiró. Inicie sesión nuevamente.')),
      },
    ]);

    render(<App />);

    await screen.findByRole('heading', { name: /digitador lim 01/i });
    await userEvent.click(screen.getByRole('button', { name: /cambiar sede/i }));
    await screen.findByRole('heading', { name: /selecciona la sede/i });

    await userEvent.type(screen.getByLabelText(/buscar sede/i), 'x');
    await screen.findByRole('heading', { name: /ingresar al sistema/i });

    expect(screen.getByRole('status')).toHaveTextContent('La sesión expiró o fue revocada');
  });

  it('CA-94/CA-109: GPS denegado se registra una vez y no se repite tras recargar la app', async () => {
    const getCurrentPosition = vi.fn((_success: PositionCallback, error?: PositionErrorCallback | null) => {
      error?.({ code: 1, message: 'Permiso denegado' } as GeolocationPositionError);
    });

    Object.defineProperty(window.navigator, 'geolocation', {
      configurable: true,
      value: { getCurrentPosition },
    });

    const firstFetch = mockApi([
      ...bootUnauthenticated(),
      { method: 'POST', path: '/api/v1/auth/login', response: () => jsonResponse(200, authMe({ geo_estado: null })) },
      {
        method: 'POST',
        path: '/api/v1/auth/ubicacion',
        response: () =>
          jsonResponse(200, {
            estado: 'DENEGADA',
            latitud: null,
            longitud: null,
            precision_m: null,
            capturada_en: new Date().toISOString(),
            recibida_en: new Date().toISOString(),
          }),
      },
      { method: 'GET', path: '/api/v1/sedes', response: () => jsonResponse(200, { items: [], similares: [], advertencia: null }) },
    ]);

    const first = render(<App />);
    await screen.findByRole('heading', { name: /ingresar al sistema/i });
    await loginFromScreen();
    await screen.findByRole('heading', { name: /selecciona la sede/i });

    await waitFor(() => {
      expect(firstFetch.mock.calls.some((call) => String(call[0]) === '/api/v1/auth/ubicacion')).toBe(true);
    });

    const geoCall = firstFetch.mock.calls.find((call) => String(call[0]) === '/api/v1/auth/ubicacion');
    const body = JSON.parse(String(geoCall?.[1]?.body)) as { estado: string };
    expect(body.estado).toBe('DENEGADA');
    expect(window.sessionStorage.getItem('pcd_geo_solicitada')).toBe('1');

    first.unmount();

    mockApi([
      { method: 'GET', path: '/api/v1/presbiterios', response: () => jsonResponse(200, presbiterios) },
      {
        method: 'GET',
        path: '/api/v1/auth/me',
        response: () => jsonResponse(200, authMe({ sede_id: 1, sede_nombre: 'Sede QA Lima', geo_estado: null })),
      },
      { method: 'GET', path: '/api/v1/sedes', response: () => jsonResponse(200, { items: [], similares: [], advertencia: null }) },
    ]);

    render(<App />);
    await screen.findByRole('heading', { name: /digitador lim 01/i });

    expect(getCurrentPosition).toHaveBeenCalledTimes(1);
  });

  it('CA-11/CA-12: advierte similitud y muestra el 409 de sede duplicada', async () => {
    mockApi([
      ...bootUnauthenticated(),
      { method: 'POST', path: '/api/v1/auth/login', response: () => jsonResponse(200, authMe({ geo_estado: 'DENEGADA' })) },
      {
        method: 'GET',
        path: '/api/v1/sedes',
        response: () =>
          jsonResponse(200, {
            items: [],
            similares: [sede({ id: 9, nombre_oficial: 'Sede Central Anexo' })],
            advertencia: 'Existen sedes con nombres parecidos.',
          }),
      },
      {
        method: 'POST',
        path: '/api/v1/sedes',
        response: () =>
          jsonResponse(
            409,
            errorEnvelope('sede_duplicada', 'Ya existe una sede con el mismo nombre normalizado en este presbiterio.'),
          ),
      },
    ]);

    render(<App />);
    await screen.findByRole('heading', { name: /ingresar al sistema/i });
    await loginFromScreen();
    await screen.findByRole('heading', { name: /selecciona la sede/i });

    expect(screen.getByText(/sedes parecidas/i)).toBeInTheDocument();

    await userEvent.click(screen.getByRole('button', { name: /nueva sede/i }));
    await userEvent.type(screen.getByLabelText(/nombre oficial de la nueva sede/i), 'Sede Central');
    await userEvent.click(screen.getByRole('button', { name: /crear y seleccionar/i }));

    await waitFor(() => {
      expect(screen.getByRole('alert')).toHaveTextContent('Ya existe una sede con ese nombre normalizado');
    });
  });

  it('CA-67/CA-111: REPORTES accede sin sede y sin acciones de escritura', async () => {
    mockApi([
      { method: 'GET', path: '/api/v1/presbiterios', response: () => jsonResponse(200, presbiterios) },
      {
        method: 'GET',
        path: '/api/v1/auth/me',
        response: () =>
          jsonResponse(
            200,
            authMe({
              perfil: 'REPORTES',
              usuario_codigo: 'REPORTES-NAC',
              usuario_nombre: 'Reportes nacional',
              sede_id: null,
              sede_nombre: null,
            }),
          ),
      },
      { method: 'GET', path: '/api/v1/sedes', response: () => jsonResponse(200, { items: [], similares: [], advertencia: null }) },
    ]);

    render(<App />);

    await screen.findByRole('heading', { name: /reportes nacional/i });
    expect(screen.queryByRole('button', { name: /cambiar sede/i })).not.toBeInTheDocument();
    expect(screen.getByText(/sin sede \(consulta nacional\)/i)).toBeInTheDocument();
    expect(screen.getByText(/perfil REPORTES accede en modo de solo lectura/i)).toBeInTheDocument();
  });
});
