import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import LoginScreen from '../components/LoginScreen';

const presbiterios = [
  { id: 1, codigo: 'LIM', nombre: 'Lima' },
  { id: 2, codigo: 'TRU', nombre: 'La Libertad – Trujillo' },
];

describe('LoginScreen', () => {
  it('CA-101: no enumera cuentas, el usuario es texto y la contraseña inicia vacía', () => {
    render(
      <LoginScreen
        presbiterios={presbiterios}
        submitting={false}
        error={null}
        notice={null}
        onLogin={vi.fn()}
      />,
    );

    expect(screen.queryByText('LIM-DIG-01')).not.toBeInTheDocument();
    expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    expect(screen.getByLabelText(/código de usuario/i)).toHaveAttribute('type', 'text');
    expect(screen.getByLabelText(/contraseña/i)).toHaveValue('');
    expect(screen.getByRole('combobox')).toBeInTheDocument();
    expect(screen.getByText(/las cuentas no se enumeran/i)).toBeInTheDocument();
  });

  it('envía presbiterio, código tecleado y contraseña', async () => {
    const onLogin = vi.fn().mockResolvedValue(undefined);
    const user = userEvent.setup();

    render(
      <LoginScreen
        presbiterios={presbiterios}
        submitting={false}
        error={null}
        notice={null}
        onLogin={onLogin}
      />,
    );

    await user.selectOptions(screen.getByRole('combobox'), '1');
    await user.type(screen.getByLabelText(/código de usuario/i), 'lim-dig-01');
    await user.type(screen.getByLabelText(/contraseña/i), 'clave-sintetica');
    await user.click(screen.getByRole('button', { name: /ingresar/i }));

    expect(onLogin).toHaveBeenCalledWith(1, 'LIM-DIG-01', 'clave-sintetica');
  });

  it('muestra error genérico y aviso de sesión', () => {
    render(
      <LoginScreen
        presbiterios={presbiterios}
        submitting={false}
        error="Presbiterio, usuario o clave incorrectos."
        notice="La sesión expiró o fue revocada. Inicia sesión nuevamente."
        onLogin={vi.fn()}
      />,
    );

    expect(screen.getByRole('alert')).toHaveTextContent('Presbiterio, usuario o clave incorrectos.');
    expect(screen.getByRole('status')).toHaveTextContent('La sesión expiró');
  });
});
