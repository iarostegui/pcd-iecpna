import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import PersonaCapture from '../components/PersonaCapture';
import { authMe, jsonResponse, mockApi } from '../test/apiMock';

describe('Captura I02 (solo información sintética)', () => {
  beforeEach(() => {
    vi.unstubAllGlobals();
  });

  function setup() {
    return mockApi([
      { method: 'GET' as const, path: '/api/v1/ubigeos?tipo=DEPARTAMENTO', response: () => jsonResponse(200, []) },
      { method: 'GET' as const, path: '/api/v1/personas?pagina=1&tamano=10', response: () => jsonResponse(200, {
        pagina: 1, tamano: 10, total: 0, items: [],
      }) },
      { method: 'POST' as const, path: '/api/v1/personas', response: () => jsonResponse(201, {
        ficha_id: 50, persona_id: 40, persona_reutilizada: false, advertencias: [],
      }) },
    ]);
  }

  it('I02-S01: registra DNI con ceros y clave de idempotencia, sin localStorage', async () => {
    window.localStorage.clear();
    const fetchMock = setup();
    render(<PersonaCapture auth={authMe({ sede_id: 1 })} />);

    const user = userEvent.setup();
    await user.selectOptions(screen.getByLabelText('Tipo de documento'), 'DNI');
    await user.type(screen.getByLabelText(/número de documento \(conservar ceros\)/i), '00123456');
    await user.type(screen.getByLabelText('Nombres'), 'PERSONA SINTETICA');
    await user.type(screen.getByLabelText('Apellido paterno'), 'APELLIDO');
    await user.type(screen.getByLabelText('Apellido materno'), 'MATERNO');
    await user.type(screen.getByLabelText('Teléfono'), '999888777');
    await user.click(screen.getByRole('button', { name: /registrar ficha/i }));

    await screen.findByText(/ficha #50 registrada/i);
    const call = fetchMock.mock.calls.find(args => String(args[0]) === '/api/v1/personas');
    const body = JSON.parse(String(call?.[1]?.body)) as { numero_documento: string };
    expect(body.numero_documento).toBe('00123456');
    expect(new Headers(call?.[1]?.headers).has('Idempotency-Key')).toBe(true);
    expect(window.localStorage.length).toBe(0);
  });

  it('I02-H24: el formulario bloquea confirmación si faltan apellidos', async () => {
    const fetchMock = setup();
    render(<PersonaCapture auth={authMe({ sede_id: 1 })} />);
    await userEvent.click(screen.getByRole('button', { name: /registrar ficha/i }));
    expect(fetchMock.mock.calls.some(args => String(args[0]) === '/api/v1/personas')).toBe(false);
  });

  it('I02-S05: REPORTES no puede confirmar ficha', async () => {
    setup();
    render(<PersonaCapture auth={authMe({ perfil: 'REPORTES', sede_id: null })} />);
    expect(screen.getByRole('button', { name: /registrar ficha/i })).toBeDisabled();
  });

  it('I02-S10: muestra catálogo vacío sin inventar ubigeos', async () => {
    setup();
    render(<PersonaCapture auth={authMe({ sede_id: 1 })} />);
    await waitFor(() => expect(screen.getByText(/catálogo INEI aún no importado/i)).toBeInTheDocument());
  });
});
