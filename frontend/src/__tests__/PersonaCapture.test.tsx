import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import PersonaCapture from '../components/PersonaCapture';
import { authMe, jsonResponse, mockApi } from '../test/apiMock';

describe('Captura I02 (solo información sintética)', () => {
  beforeEach(() => {
    vi.unstubAllGlobals();
  });

  function setup(loaded = false) {
    return mockApi([
      { method: 'GET' as const, path: '/api/v1/ubigeos?tipo=DEPARTAMENTO', response: () => jsonResponse(200, loaded ? [{ codigo: '15', nombre: 'Lima', tipo: 'DEPARTAMENTO', padre_codigo: null }] : []) },
      { method: 'GET' as const, path: '/api/v1/ubigeos?tipo=PROVINCIA&padre_codigo=15', response: () => jsonResponse(200, [{ codigo: '1501', nombre: 'Lima', tipo: 'PROVINCIA', padre_codigo: '15' }]) },
      { method: 'GET' as const, path: '/api/v1/ubigeos?tipo=DISTRITO&padre_codigo=1501', response: () => jsonResponse(200, [{ codigo: '150101', nombre: 'Lima', tipo: 'DISTRITO', padre_codigo: '1501' }]) },
      { method: 'GET' as const, path: '/api/v1/catalogos', response: () => jsonResponse(200, [
        { categoria: 'SEXO', codigo: 'M', etiqueta: 'Masculino', orden: 20 },
        { categoria: 'ESTADO_CIVIL', codigo: 'CASADO', etiqueta: 'Casado(a)', orden: 20 },
        { categoria: 'CONDICION', codigo: 'MIEMBRO', etiqueta: 'Miembro', orden: 30 },
      ]) },
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
    await user.click(screen.getByLabelText('Ficha completa'));
    await user.click(screen.getByLabelText('Persona validada en papel'));
    await user.click(screen.getByRole('button', { name: /registrar ficha/i }));

    await screen.findByText(/ficha #50 registrada/i);
    const call = fetchMock.mock.calls.find(args => String(args[0]) === '/api/v1/personas');
    const body = JSON.parse(String(call?.[1]?.body)) as { numero_documento: string; control_papel: string };
    expect(body.numero_documento).toBe('00123456');
    expect(JSON.parse(body.control_papel)).toEqual({ ficha_completa: true, falta_informacion: false, persona_validada: true });
    expect(screen.getByRole('button', { name: 'Ficha registrada' })).toBeDisabled();
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

  it('I02-UAT: elige jerarquía geográfica real y catálogos de iglesia desde API', async () => {
    setup(true);
    render(<PersonaCapture auth={authMe({ sede_id: 1, sede_nombre: 'Sede Lima Centro' })} />);
    const user = userEvent.setup();
    expect(screen.getByText(/Sede Lima Centro/)).toBeInTheDocument();
    await user.selectOptions(screen.getByLabelText('Departamento'), '15');
    await screen.findByRole('option', { name: 'Lima', hidden: false });
    await waitFor(() => expect(screen.getByLabelText('Provincia')).toHaveValue(''));
    await user.selectOptions(screen.getByLabelText('Provincia'), '1501');
    await waitFor(() => expect(screen.getByRole('option', { name: 'Lima', hidden: false })).toBeInTheDocument());
    await user.selectOptions(screen.getByLabelText('Distrito'), '150101');
    await waitFor(() => expect(screen.getByRole('option', { name: 'Masculino' })).toBeInTheDocument());
    await user.selectOptions(screen.getByLabelText('Sexo (código de ficha)'), 'M');
    expect(screen.getByLabelText('Distrito')).toHaveValue('150101');
    expect(screen.getByLabelText('Sexo (código de ficha)')).toHaveValue('M');
  });

  it('I02-S10: muestra catálogo vacío sin inventar ubigeos', async () => {
    setup();
    render(<PersonaCapture auth={authMe({ sede_id: 1 })} />);
    await waitFor(() => expect(screen.getByText(/catálogo geográfico aún no cargado/i)).toBeInTheDocument());
  });
});
