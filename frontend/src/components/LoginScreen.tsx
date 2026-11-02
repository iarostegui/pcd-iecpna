import { useState, type FormEvent } from 'react';
import type { Presbiterio } from '../api/types';

interface Props {
  presbiterios: Presbiterio[];
  submitting: boolean;
  error: string | null;
  notice: string | null;
  onLogin: (presbiterioId: number, usuario: string, password: string) => Promise<void>;
}

export default function LoginScreen({ presbiterios, submitting, error, notice, onLogin }: Props) {
  const [presbiterioId, setPresbiterioId] = useState('');
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');

  async function handleSubmit(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    if (presbiterioId === '' || usuario.trim() === '' || password === '') {
      return;
    }

    await onLogin(Number(presbiterioId), usuario.trim(), password);
  }

  return (
    <main className="login-shell">
      <section className="login-brand" aria-label="Identidad PCD">
        <p className="eyebrow">PCD · IECPNA</p>
        <h1>
          Plataforma de
          <br />
          <strong>Captura de Datos</strong>
        </h1>
        <p className="login-tagline">Un registro ordenado. Una iglesia mejor conectada.</p>
        <ul className="login-features">
          <li>Digitación guiada de fichas de Persona y Líder de Grupo Crecer.</li>
          <li>Cada acción queda asociada al digitador que la realizó.</li>
          <li>Ubicación opcional, una sola vez por sesión y no probatoria.</li>
        </ul>
        <p className="login-warning">
          Entorno de desarrollo con datos sintéticos. No ingrese información personal real.
        </p>
      </section>

      <section className="login-panel" aria-labelledby="login-title">
        <p className="eyebrow">Acceso operativo</p>
        <h2 id="login-title">Ingresar al sistema</h2>
        <p className="muted">
          Selecciona el presbiterio y escribe tu código de usuario. Las cuentas no se enumeran.
        </p>

        {notice !== null && (
          <p className="notice info" role="status">
            {notice}
          </p>
        )}
        {error !== null && (
          <p className="notice error" role="alert">
            {error}
          </p>
        )}

        <form className="stack" onSubmit={(event) => void handleSubmit(event)} noValidate>
          <label className="field" htmlFor="login-presbiterio">
            <span>
              Presbiterio <span aria-hidden="true">*</span>
            </span>
            <select
              id="login-presbiterio"
              value={presbiterioId}
              onChange={(event) => setPresbiterioId(event.target.value)}
              disabled={submitting || presbiterios.length === 0}
              required
            >
              <option value="">Seleccionar presbiterio…</option>
              {presbiterios.map((presbiterio) => (
                <option key={presbiterio.id} value={presbiterio.id}>
                  {presbiterio.nombre} ({presbiterio.codigo})
                </option>
              ))}
            </select>
          </label>

          <label className="field" htmlFor="login-usuario">
            <span>
              Código de usuario <span aria-hidden="true">*</span>
            </span>
            <input
              id="login-usuario"
              type="text"
              value={usuario}
              onChange={(event) => setUsuario(event.target.value.toUpperCase())}
              placeholder="Escribe tu código de usuario"
              autoComplete="username"
              autoCapitalize="characters"
              spellCheck={false}
              maxLength={80}
              disabled={submitting}
              required
            />
            <small>El código se escribe; no existe un listado público de cuentas.</small>
          </label>

          <label className="field" htmlFor="login-password">
            <span>
              Contraseña <span aria-hidden="true">*</span>
            </span>
            <input
              id="login-password"
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Ingresa tu clave"
              autoComplete="current-password"
              maxLength={200}
              disabled={submitting}
              required
            />
          </label>

          <button className="btn primary" type="submit" disabled={submitting || presbiterios.length === 0}>
            {submitting ? 'Ingresando…' : 'Ingresar'}
          </button>
        </form>

        <p className="login-note">
          La sesión usa una cookie HttpOnly y un token CSRF en memoria. No se guardan
          credenciales ni sesión en localStorage.
        </p>
      </section>
    </main>
  );
}
